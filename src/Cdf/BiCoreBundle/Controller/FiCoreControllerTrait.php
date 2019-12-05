<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Arrays\ArrayUtils;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\Finder;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

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
        $entityutils = new EntityUtils($this->get('doctrine')->getManager());
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $colonneordinamento = array($tablenamefromentity.'.id' => 'DESC');
        $parametritabella = array('em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/').$controller.'/'.'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter('Elenco '.$controller),
            'multiselezione' => ParametriTabella::setParameter('0'),
            'editinline' => ParametriTabella::setParameter('0'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righetotali' => ParametriTabella::setParameter('0'),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            'estraituttirecords' => ParametriTabella::setParameter('0'),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(''),
        );

        return $this->render($crudtemplate, array('parametritabella' => $parametritabella));
    }

    /**
     * Lists all tables entities.
     */
    public function indexDettaglio(Request $request, Packages $assetsmanager)
    {
        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }

        $bundle = $this->getBundle();
        $controller = $this->getController();
        $parametripassati = json_decode($request->get('parametripassati'), true);

        $filtri = $this->getParametroIndexDettaglio($parametripassati, 'filtri', array());
        $prefiltri = $this->getParametroIndexDettaglio($parametripassati, 'prefiltri', array());
        $titolotabella = $this->getParametroIndexDettaglio($parametripassati, 'titolotabella', 'Elenco '.$controller);
        $modellocolonne = $this->getParametroIndexDettaglio($parametripassati, 'modellocolonne', array());
        $colonneordinamento = $this->getParametroIndexDettaglio($parametripassati, 'colonneordinamento', array());
        $multiselezione = $this->getParametroIndexDettaglio($parametripassati, 'multiselezione', 0);
        $parametriform = $this->getParametroIndexDettaglio($parametripassati, 'parametriform', array());

        $template = $bundle.':'.$controller.':'.$this->getThisFunctionName().'.html.twig';
        if (!$this->get("twig")->getLoader()->exists($template)) {
            $template = $controller.'/Crud/'.$this->getThisFunctionName().'.html.twig';
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

        $entityutils = new EntityUtils($this->get('doctrine')->getManager());

        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $parametritabella = array('em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'parametriform' => ParametriTabella::setParameter(json_encode($parametriform)),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('/').$controller.'/'.'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('/')),
            'idpassato' => ParametriTabella::setParameter(0),
            'titolotabella' => ParametriTabella::setParameter($titolotabella),
            'multiselezione' => ParametriTabella::setParameter($multiselezione),
            'editinline' => ParametriTabella::setParameter('1'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(''),
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
     * Lists all tables entities.
     */
    public function lista(Request $request)
    {
        if (!$this->getPermessi()->canRead($this->getController())) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $em = $this->get('doctrine')->getManager();
        $righe = $em->getRepository($entityclassnotation)->findAll();

        $lista = array();
        foreach ($righe as $riga) {
            $lista[] = array('id' => $riga->getId(), 'descrizione' => $riga->__toString());
        }

        return new JsonResponse(ArrayUtils::arrayOrderby($lista, 'descrizione', SORT_ASC));
    }

    protected function getTabellaTemplate($controller)
    {
        $tabellatemplate = $controller.'/Tabella/tabellaform.html.twig';
        if (!$this->get("twig")->getLoader()->exists($tabellatemplate)) {
            $tabellatemplate = '@BiCore/'.$controller.'/Tabella/tabellaform.html.twig';
            if (!$this->get("twig")->getLoader()->exists($tabellatemplate)) {
                $tabellatemplate = '@BiCore/Standard/Tabella/tabellaform.html.twig';
            }
        }

        return $tabellatemplate;
    }

    protected function getCrudTemplate($bundle, $controller, $operation)
    {
        $crudtemplate = $bundle.'/'.$controller.'/Crud/'.$operation.'.html.twig';
        if (!$this->get("twig")->getLoader()->exists($crudtemplate)) {
            $crudtemplate = $controller.'/Crud/'.$operation.'.html.twig';
            if (!$this->get("twig")->getLoader()->exists($crudtemplate)) {
                $crudtemplate = '@BiCore/Standard/Crud/'.$operation.'.html.twig';
            }
        }

        return $crudtemplate;
    }

    /**
     * Returns the calling function through a backtrace.
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
        $em = $this->get('doctrine')->getManager();
        $entityutils = new EntityUtils($em);

        return $entityutils->getClassNameToShortcutNotations($this->getEntityClassName());
    }

    protected function getEntityClassName()
    {
        $em = $this->get('doctrine')->getManager();
        $entityfinder = new Finder($em);

        return $entityfinder->getClassNameFromEntityName($this->controller);
    }
}
