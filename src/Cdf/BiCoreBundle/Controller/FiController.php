<?php

namespace Cdf\BiCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Cdf\BiCoreBundle\Utils\Permessi\PermessiUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Asset\Packages;

class FiController extends FiTabellaController
{
    public function __construct(ObjectManager $em, TokenStorageInterface $user)
    {
        $matches = array();
        $controllo = new \ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (count($matches) == 0) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = new PermessiUtils($em, $this->getController(), $user->getToken()->getUser());
    }
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

        $colonneordinamento = array($controller . '.id' => "DESC");
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
            'editinline' => ParametriTabella::setParameter("1"),
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
}
