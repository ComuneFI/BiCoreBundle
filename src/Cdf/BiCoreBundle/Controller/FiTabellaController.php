<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Cdf\BiCoreBundle\Utils\Export\TabellaXls;
use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class FiTabellaController extends FiCrudController
{

    public function tabella(Request $request)
    {
        if (!$this->permessi->canRead()) {
            throw new AccessDeniedException("Non si hanno i permessi per visualizzare questo contenuto");
        }
        $doctrine = $this->get("doctrine");
        //$em = $doctrine->getManager();

        $parametripassati = array_merge($request->get("parametri"), array('user' => $this->getUser()));
        $parametriform = isset($parametripassati["parametriform"]) ?
                json_decode(ParametriTabella::getParameter($parametripassati["parametriform"]), true) : array();
        $configurazionetabella = new Tabella($doctrine, $parametripassati);
        $parametritabella = array(
            'parametritabella' => $configurazionetabella->getConfigurazionecolonnetabella(),
            'recordstabella' => $configurazionetabella->getRecordstabella(),
            'paginacorrente' => $configurazionetabella->getPaginacorrente(),
            'paginetotali' => $configurazionetabella->getPaginetotali(),
            'righetotali' => $configurazionetabella->getRighetotali(),
            'traduzionefiltri' => $configurazionetabella->getTraduzionefiltri(),
        );
        $classbundle = ParametriTabella::getParameter($parametripassati["entityclass"]);
        //$formbundle = $namespace . '\\' . $bundle . 'Bundle' . '\\Form\\' . $controller;
        $formbundle = ParametriTabella::getParameter($parametripassati["formclass"]);
        $formType = $formbundle . 'Type';

        $entity = new $classbundle();
        //$tablename = ParametriTabella::getParameter($parametripassati["tablename"]);
        $controller = ParametriTabella::getParameter($parametripassati["nomecontroller"]);
        $form = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                'id' => 'formdati' . $controller,
                ),
                'action' => $this->generateUrl($controller . '_new'),
                "parametriform" => $parametriform
                )
        );

        $parametri = array_merge($parametripassati, $parametritabella);
        $parametri["form"] = $form->createView();

        $template = $controller . '/Tabella/tabellacontainer.html.twig';
        $templatelocation = $controller . "/";
        if (!$this->get('templating')->exists($template)) {
            $template = "BiCoreBundle:" . $controller . ':Tabella/tabellacontainer.html.twig';
            $templatelocation = "BiCoreBundle:" . $controller . ":";
            if (!$this->get('templating')->exists($template)) {
                $templatelocation = 'BiCoreBundle:Standard:';
                $template = $templatelocation . 'Tabella/tabellacontainer.html.twig';
            }
        }
        $parametri["templatelocation"] = $templatelocation;

        return $this->render(
            $template,
            array(
                    'parametri' => $parametri
                        )
        );
    }

    public function exportXls(Request $request)
    {
        $doctrine = $this->get("doctrine");

        try {
            $parametripassati = array_merge($request->get("parametri"), array('user' => $this->getUser()));
            $parametripassati["estraituttirecords"] = ParametriTabella::setParameter("1");
            $configurazionetabella = new Tabella($doctrine, $parametripassati);
            $parametritabella = array(
                'parametritabella' => $configurazionetabella->getConfigurazionecolonnetabella(),
                'recordstabella' => $configurazionetabella->getRecordstabella(),
                'paginacorrente' => $configurazionetabella->getPaginacorrente(),
                'paginetotali' => $configurazionetabella->getPaginetotali(),
                'righetotali' => $configurazionetabella->getRighetotali(),
                'traduzionefiltri' => $configurazionetabella->getTraduzionefiltri(),
                'nomecontroller' => ParametriTabella::getParameter($parametripassati["nomecontroller"]),
            );
            $xls = new TabellaXls($this->container);
            $filexls = $xls->esportaexcel($parametritabella);
            if (file_exists($filexls)) {
                $response = array(
                    'status' => '200',
                    'file' => "data:application/vnd.ms-excel;base64," . base64_encode(file_get_contents($filexls))
                );
                @unlink($filexls);
            } else {
                $response = array(
                    'status' => '501',
                    'file' => "Impossibile generare il file excel"
                );
            }
        } catch (\Exception $exc) {
            $response = array(
                'status' => '500',
                'file' => $exc->getFile() . " -> Riga: " . $exc->getLine() . " -> " . $exc->getMessage()
            );
        }

        return new JsonResponse($response);
    }

    private function checkAggiornaRight($id)
    {
        if ($id === 0) {
            if (!$this->getPermessi()->canCreate()) {
                throw new AccessDeniedException("Non si hanno i permessi per creare questo contenuto");
            }
        } else {
            if (!$this->getPermessi()->canUpdate()) {
                throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
            }
        }
    }

    /**
     * Inline existing table entity.
     */
    public function aggiorna(Request $request, $id)
    {
        $this->checkAggiornaRight($id);

        /* @var $em \Doctrine\ORM\EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();
        $this->checkAggiornaRight($id);
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();
        $insert = ($id === 0);

        if ($insert) {
            //Insert
            $entity = new $entityclass();
            $queryBuilder
                    ->insert($entityclass)
            ;
        } else {
            //Update
            $entity = $em->getRepository($entityclass)->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
            }
            $queryBuilder
                    ->update($entityclass, "u")
                    ->where("u.id = :id")
                    ->setParameter("id", $id);
        }
        $values = $request->get("values");
        $querydaeseguire = false;

        foreach ($values as $value) {
            $fieldpieces = explode(".", $value["fieldname"]);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && count($fieldpieces) == 2) {
                $field = $fieldpieces[1];
                $subfieldpieces = explode("_", $field);
                if ($insert) {
                    $queryBuilder->setValue($field, ':' . $field);
                    $queryBuilder->setParameter($field, $value["fieldvalue"]);
                    $querydaeseguire = true;
                } else {
                    /**/
                    $nomefunzioneget = "get";
                    foreach ($subfieldpieces as $field4get) {
                        $nomefunzioneget .= ucfirst($field4get);
                    }
                    if (method_exists($entity, $nomefunzioneget)) {
                        if ($entity->$nomefunzioneget() != $value["fieldvalue"]) {
                            $querydaeseguire = true;
                            $queryBuilder->set("u." . $field, ':' . $field);
                            $queryBuilder->setParameter($field, $value["fieldvalue"]);
                        }
                    }
                }
            } else {
                continue;
            }
        }
        if ($querydaeseguire) {
            $queryBuilder->getQuery()->execute();
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse(array("errcode" => 0, "message" => "Registrazione eseguita"));
    }
}
