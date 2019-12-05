<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Cdf\BiCoreBundle\Utils\Tabella\Tabella;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cdf\BiCoreBundle\Utils\Export\TabellaXls;

trait FiCoreTabellaControllerTrait
{

    private $tabellaxls;

    public function tabella(Request $request)
    {
        if (!$this->permessi->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }

        $parametripassati = array_merge($request->get('parametri'), array('user' => $this->getUser()));
        $parametriform = isset($parametripassati['parametriform']) ?
                json_decode(ParametriTabella::getParameter($parametripassati['parametriform']), true) : array();
        $classbundle = ParametriTabella::getParameter($parametripassati['entityclass']);
        $formbundle = ParametriTabella::getParameter($parametripassati['formclass']);
        $formType = $formbundle . 'Type';

        $entity = new $classbundle();
        $controller = ParametriTabella::getParameter($parametripassati['nomecontroller']);
        $form = $this->createForm(
            $formType,
            $entity,
            array('attr' => array(
                        'id' => 'formdati' . $controller,
                    ),
                    'action' => $this->generateUrl($controller . '_new'),
                    'parametriform' => $parametriform,
                )
        );

        $parametri = array_merge($parametripassati, $this->getParametriTabella($parametripassati));
        $parametri['form'] = $form->createView();
        $templateobj = $this->getTabellaTemplateInformations($controller);
        $parametri['templatelocation'] = $templateobj["path"];

        return $this->render(
            $templateobj["template"],
            array('parametri' => $parametri)
        );
    }
    public function setTabellaxls(TabellaXls $tabellaxls)
    {
        $this->tabellaxls = $tabellaxls;
    }
    public function exportXls(Request $request)
    {
        try {
            $parametripassati = array_merge($request->get('parametri'), array('user' => $this->getUser()));
            $parametripassati['estraituttirecords'] = ParametriTabella::setParameter('1');

            $filexls = $this->tabellaxls->esportaexcel($this->getParametriTabellaXls($parametripassati));
            if (file_exists($filexls)) {
                $response = array(
                    'status' => '200',
                    'file' => 'data:application/vnd.ms-excel;base64,' . base64_encode(file_get_contents($filexls)),
                );
                @unlink($filexls);
            } else {
                $response = array(
                    'status' => '501',
                    'file' => 'Impossibile generare il file excel',
                );
            }
        } catch (\Exception $exc) {
            $response = array(
                'status' => '500',
                'file' => $exc->getFile() . ' -> Riga: ' . $exc->getLine() . ' -> ' . $exc->getMessage(),
            );
        }

        return new JsonResponse($response);
    }
    protected function getParametriTabella(array $parametripassati)
    {
        $doctrine = $this->get('doctrine');
        $configurazionetabella = new Tabella($doctrine, $parametripassati);
        $parametritabella = array(
            'parametritabella' => $configurazionetabella->getConfigurazionecolonnetabella(),
            'recordstabella' => $configurazionetabella->getRecordstabella(),
            'paginacorrente' => $configurazionetabella->getPaginacorrente(),
            'paginetotali' => $configurazionetabella->getPaginetotali(),
            'righetotali' => $configurazionetabella->getRighetotali(),
            'traduzionefiltri' => $configurazionetabella->getTraduzionefiltri()
        );
        return $parametritabella;
    }
    protected function getParametriTabellaXls(array $parametripassati)
    {
        $doctrine = $this->get('doctrine');
        $configurazionetabella = new Tabella($doctrine, $parametripassati);
        $parametritabella = array(
            'parametritabella' => $configurazionetabella->getConfigurazionecolonnetabella(),
            'recordstabella' => $configurazionetabella->getRecordstabella(),
            'paginacorrente' => $configurazionetabella->getPaginacorrente(),
            'paginetotali' => $configurazionetabella->getPaginetotali(),
            'righetotali' => $configurazionetabella->getRighetotali(),
            'traduzionefiltri' => $configurazionetabella->getTraduzionefiltri(),
            'nomecontroller' => ParametriTabella::getParameter($parametripassati['nomecontroller']),
        );
        return $parametritabella;
    }
    protected function getTabellaTemplateInformations($controller)
    {
        $template = $controller . '/Tabella/tabellacontainer.html.twig';
        $path = $controller . '/';
        if (!$this->get("twig")->getLoader()->exists($template)) {
            $template = '@BiCore/' . $controller . '/Tabella/tabellacontainer.html.twig';
            $path = '@BiCore/' . $controller . '/';
            if (!$this->get("twig")->getLoader()->exists($template)) {
                $path = '@BiCore/Standard/';
                $template = $path . 'Tabella/tabellacontainer.html.twig';
            }
        }
        return array("path" => $path, "template" => $template);
    }
}
