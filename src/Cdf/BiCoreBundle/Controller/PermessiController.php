<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Permessi controller.
 */
class PermessiController extends FiController
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
            array(
                'nomecampo' => '__bicorebundle_Permessi.modulo',
                'nometabella' => '__bicorebundle_Permessi',
                'etichetta' => 'Funzionalità',
            ),
            array(
                'nomecampo' => '__bicorebundle_Permessi.__bicorebundle_Operatori.username',
                'escluso' => false,
            ), /*
                  $controller . ".operatori" => array(
                  "nometabella" => "__bicorebundle_Permessi",
                  "nomecampo" => "operatori",
                  "etichetta" => "Operatore",
                  ), */
        );

        $colonneordinamento = array('__bicorebundle_Permessi.id' => 'DESC');
        /* $filtri = array(array("nomecampo" => "__bicorebundle_Permessi.ruoli.superadmin",
          "operatore" => "=",
          "valore" => true
          )); */
        $filtri = array(
            array('nomecampo' => '__bicorebundle_Permessi.__bicorebundle_Operatori.__bicorebundle_Ruoli.user',
                'operatore' => '=',
                'valore' => true,
            ),
        );
        $prefiltri = array();
        $entityutils = new EntityUtils($this->get('doctrine')->getManager());
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
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
            'multiselezione' => ParametriTabella::setParameter('1'),
            'editinline' => ParametriTabella::setParameter('0'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righetotali' => ParametriTabella::setParameter('0'),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            'colonneordinamento' => ParametriTabella::setParameter(json_encode($colonneordinamento)),
            'filtri' => ParametriTabella::setParameter(json_encode($filtri)),
            'prefiltri' => ParametriTabella::setParameter(json_encode($prefiltri)),
            'traduzionefiltri' => ParametriTabella::setParameter(''),
        );

        return $this->render($crudtemplate, array('parametritabella' => $parametritabella));
    }
}
