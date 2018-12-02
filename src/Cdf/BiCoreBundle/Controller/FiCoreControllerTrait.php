<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Entity\Finder;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Cdf\BiCoreBundle\Utils\Export\TabellaXls;
use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Asset\Packages;

trait FiCoreControllerTrait
{
    /**
     * Lists all tables entities.
     */
    public function index(Request $request, Packages $assetsmanager)
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');

        if (!$this->getPermessi()->canRead()) {
            throw new AccessDeniedException("Non si hanno i permessi per visualizzare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace("Entity", "Form", $entityclass);

        $modellocolonne = array(
                /*
                  $controller . ".nominativo" => array(
                  "nometabella" => $controller,
                  "nomecampo" => "nominativo",
                  "etichetta" => "Nominativo",
                  "ordine" => 10,
                  "larghezza" => 200,
                  "escluso" => false
                  ),
                  $controller . ".datanascita" => array(
                  "nometabella" => $controller,
                  "nomecampo" => "datanascita",
                  "etichetta" => "Data di nascita",
                  "ordine" => 20,
                  "larghezza" => 100,
                  "escluso" => false
                  ),

                 */
        );

        $filtri = array();
        $prefiltri = array();
        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->get("doctrine")->getManager());
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $colonneordinamento = array($tablenamefromentity . '.id' => "DESC");
        $parametritabella = array("em" => ParametriTabella::setParameter("default"),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi())),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter("Elenco " . $controller),
            'multiselezione' => ParametriTabella::setParameter("0"),
            'editinline' => ParametriTabella::setParameter("0"),
            'paginacorrente' => ParametriTabella::setParameter("1"),
            'paginetotali' => ParametriTabella::setParameter(""),
            'righetotali' => ParametriTabella::setParameter("0"),
            'righeperpagina' => ParametriTabella::setParameter("15"),
            'estraituttirecords' => ParametriTabella::setParameter("0"),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(""),
        );

        return $this->render($crudtemplate, array('parametritabella' => $parametritabella,));
    }
    /**
     * Lists all tables entities.
     */
    public function indexDettaglio(Request $request, Packages $assetsmanager)
    {
        if (!$this->getPermessi()->canRead()) {
            throw new AccessDeniedException("Non si hanno i permessi per visualizzare questo contenuto");
        }

        $bundle = $this->getBundle();
        $controller = $this->getController();
        $parametripassati = json_decode($request->get('parametripassati'), true);

        $filtri = $this->getParametroIndexDettaglio($parametripassati, "filtri", array());
        $prefiltri = $this->getParametroIndexDettaglio($parametripassati, "prefiltri", array());
        $titolotabella = $this->getParametroIndexDettaglio($parametripassati, "titolotabella", "Elenco " . $controller);
        $modellocolonne = $this->getParametroIndexDettaglio($parametripassati, "modellocolonne", array());
        $colonneordinamento = $this->getParametroIndexDettaglio($parametripassati, "colonneordinamento", array());
        $multiselezione = $this->getParametroIndexDettaglio($parametripassati, "multiselezione", 0);
        $parametriform = $this->getParametroIndexDettaglio($parametripassati, "parametriform", array());

        $template = $bundle . ':' . $controller . ':' . $this->getThisFunctionName() . '.html.twig';
        if (!$this->get('templating')->exists($template)) {
            $template = $controller . '/Crud/' . $this->getThisFunctionName() . '.html.twig';
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace("Entity", "Form", $entityclass);

        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->get("doctrine")->getManager());

        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $parametritabella = array("em" => ParametriTabella::setParameter("default"),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'parametriform' => ParametriTabella::setParameter(json_encode($parametriform)),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi())),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter(0),
            'titolotabella' => ParametriTabella::setParameter($titolotabella),
            'multiselezione' => ParametriTabella::setParameter($multiselezione),
            'editinline' => ParametriTabella::setParameter("1"),
            'paginacorrente' => ParametriTabella::setParameter("1"),
            'paginetotali' => ParametriTabella::setParameter(""),
            'righeperpagina' => ParametriTabella::setParameter("15"),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(""),
        );

        return $this->render(
            $template,
            array(
                            'parametritabella' => $parametritabella,
                        )
        );
    }
    private function getParametroIndexDettaglio($parametripassati, $keyparametro, $defaultvalue)
    {
        if (isset($parametripassati[$keyparametro])) {
            $parametro = $parametripassati[$keyparametro];
        } else {
            $parametro = $defaultvalue;
        }
        return $parametro;
    }
    /**
     * Displays a form to create a new table entity.
     */
    public function new(Request $request)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canCreate()) {
            throw new AccessDeniedException("Non si hanno i permessi per creare questo contenuto");
        }

        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $parametriform = $request->get("parametriform") ? json_decode($request->get("parametriform"), true) : array();

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);

        $entity = new $entityclass();
        $formType = $formclass . 'Type';
        $form = $this->createForm($formType, $entity, array('attr' => array(
                'id' => 'formdati' . $controller,
            ),
            'action' => $this->generateUrl($controller . '_new'), "parametriform" => $parametriform
        ));

        $form->handleRequest($request);

        $twigparms = array(
            'form' => $form->createView(),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'tabellatemplate' => $tabellatemplate
        );

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entity = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($entity);
                $entityManager->flush();
                return new Response(
                    $this->renderView($crudtemplate, $twigparms),
                    200
                );
            } else {
                //Quando non passa la validazione
                return new Response(
                    $this->renderView($crudtemplate, $twigparms),
                    400
                );
            }
        } else {
            //Quando viene richiesta una "nuova" new
            return new Response(
                $this->renderView($crudtemplate, $twigparms),
                200
            );
        }
    }
    /**
     * Displays a form to edit an existing table entity.
     */
    public function edit(Request $request, $id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();

        if (!$this->getPermessi()->canUpdate()) {
            throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());
        $tabellatemplate = $this->getTabellaTemplate($controller);

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);

        $formType = $formclass . 'Type';

        $elencomodifiche = $this->elencoModifiche($controller, $id);

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' del record con id ' . $id . '.');
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                        'id' => 'formdati' . $controller,
                    ),
                    'action' => $this->generateUrl($controller . '_update', array('id' => $entity->getId())),
                )
        );

        return $this->render(
            $crudtemplate,
            array(
                            'entity' => $entity,
                            'nomecontroller' => ParametriTabella::setParameter($controller),
                            'tabellatemplate' => $tabellatemplate,
                            'edit_form' => $editForm->createView(),
                            'elencomodifiche' => $elencomodifiche,
                        )
        );
    }
    /**
     * Edits an existing table entity.
     */
    public function update(Request $request, $id)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $bundle = $this->getBundle();
        $controller = $this->getController();
        if (!$this->getPermessi()->canUpdate()) {
            throw new AccessDeniedException("Non si hanno i permessi per modificare questo contenuto");
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, "edit");

        $entityclass = $this->getEntityClassName();
        $formclass = str_replace("Entity", "Form", $entityclass);
        $formType = $formclass . 'Type';

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($entityclass)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossibile trovare l\'entità ' . $controller . ' per il record con id ' . $id);
        }

        $editForm = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                        'id' => 'formdati' . $controller,
                    ),
                    'action' => $this->generateUrl($controller . '_update', array('id' => $entity->getId())),
                )
        );

        $editForm->submit($request->request->get($editForm->getName()));

        if ($editForm->isValid()) {
            $originalData = $em->getUnitOfWork()->getOriginalEntityData($entity);

            $em->persist($entity);
            $em->flush();

            $newData = $em->getUnitOfWork()->getOriginalEntityData($entity);
            $repoStorico = $em->getRepository("BiCoreBundle:Storicomodifiche");
            $changes = $repoStorico->isRecordChanged($controller, $originalData, $newData);

            if ($changes) {
                $repoStorico->saveHistory($controller, $changes, $id, $this->getUser());
            }

            $continua = (int) $request->get('continua');
            if ($continua === 0) {
                return new Response('OK');
            } else {
                return $this->redirect($this->generateUrl($controller . '_edit', array('id' => $id)));
            }
        }

        return $this->render(
            $crudtemplate,
            array(
                            'entity' => $entity,
                            'edit_form' => $editForm->createView(),
                            'nomecontroller' => ParametriTabella::setParameter($controller),
                        )
        );
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
    public function aggiorna(Request $request, $id, $token)
    {
        $this->checkAggiornaRight($id);

        /* @var $em \Doctrine\ORM\EntityManager */
        $controller = $this->getController();
        $entityclass = $this->getEntityClassName();

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
        $isValidToken = $this->isCsrfTokenValid($id, $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

        $querydaeseguire = false;

        foreach ($values as $value) {
            $fieldpieces = explode(".", $value["fieldname"]);
            $table = $fieldpieces[0];
            //Si prende in considerazione solo i campi strettamente legati a questa entity
            if ($table == $controller && count($fieldpieces) == 2 && $value["fieldtype"] != 'join') {
                $field = $fieldpieces[1];
                if ($insert) {
                    $queryBuilder->setValue($field, ':' . $field);
                    $queryBuilder->setParameter($field, $value["fieldvalue"]);
                    $querydaeseguire = true;
                } else {
                    $entityutils = new EntityUtils($em);
                    $property = $entityutils->getEntityProperties($field, $entity);
                    $nomefunzioneget = $property["get"];
                    if ($nomefunzioneget != $value["fieldvalue"]) {
                        $querydaeseguire = true;
                        $queryBuilder->set("u." . $field, ':' . $field);
                        $queryBuilder->setParameter($field, $value["fieldvalue"]);
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
    /**
     * Deletes a table entity.
     */
    public function delete(Request $request, $token)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        if (!$this->getPermessi()->canDelete()) {
            throw new AccessDeniedException("Non si hanno i permessi per eliminare questo contenuto");
        }
        $entityclass = $this->getEntityClassName();

        $isValidToken = $this->isCsrfTokenValid($this->getController(), $token);

        if (!$isValidToken) {
            throw $this->createNotFoundException('Token non valido');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $ids = explode(',', $request->get('id'));
            $qb->delete($entityclass, 'u')
                    ->andWhere('u.id IN (:ids)')
                    ->setParameter('ids', $ids);

            $query = $qb->getQuery();
            $query->execute();
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode('501');
            return $response;
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->setStatusCode('200');
            return $response;
        }

        return new Response('Operazione eseguita con successo');
    }
    private function elencoModifiche($controller, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $risultato = $em->getRepository('BiCoreBundle:Storicomodifiche')->findBy(
            array(
                    'nometabella' => $controller,
                    'idtabella' => $id,
                ),
            array('giorno' => 'DESC')
        );

        return $risultato;
    }
    protected function getTabellaTemplate($controller)
    {
        $tabellatemplate = $controller . '/Tabella/tabellaform.html.twig';
        if (!$this->get('templating')->exists($tabellatemplate)) {
            $tabellatemplate = 'BiCoreBundle:' . $controller . ':Tabella/tabellaform.html.twig';
            if (!$this->get('templating')->exists($tabellatemplate)) {
                $tabellatemplate = 'BiCoreBundle:Standard:Tabella/tabellaform.html.twig';
            }
        }

        return $tabellatemplate;
    }
    protected function getCrudTemplate($bundle, $controller, $operation)
    {
        $crudtemplate = $bundle . ':' . $controller . ':Crud/' . $operation . '.html.twig';
        if (!$this->get('templating')->exists($crudtemplate)) {
            $crudtemplate = $controller . '/Crud/' . $operation . '.html.twig';
            if (!$this->get('templating')->exists($crudtemplate)) {
                $crudtemplate = 'BiCoreBundle:Standard:Crud/' . $operation . '.html.twig';
            }
        }
        return $crudtemplate;
    }
    /**
     * Returns the calling function through a backtrace
     */
    protected function getThisFunctionName()
    {
        // a funciton x has called a function y which called this
        // see stackoverflow.com/questions/190421
        $caller = debug_backtrace();
        $caller = $caller[1];
        return $caller['function'];
    }
    protected function getEntityClassNotation()
    {
        $em = $this->get("doctrine")->getManager();
        $entityutils = new EntityUtils($em);
        return $entityutils->getClassNameToShortcutNotations($this->getEntityClassName());
    }
    protected function getEntityClassName()
    {
        $em = $this->get("doctrine")->getManager();
        $entityfinder = new Finder($em, $this->controller);
        return $entityfinder->getClassNameFromEntityName();
    }
    
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
}
