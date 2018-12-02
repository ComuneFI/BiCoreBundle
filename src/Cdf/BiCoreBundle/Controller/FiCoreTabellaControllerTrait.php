<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Cdf\BiCoreBundle\Utils\Export\TabellaXls;
use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Symfony\Component\HttpFoundation\JsonResponse;

trait FiCoreTabellaControllerTrait
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
}
