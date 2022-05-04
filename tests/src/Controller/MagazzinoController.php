<?php

namespace App\Controller;

use Cdf\BiCoreBundle\Controller\FiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Asset\Packages;

/*
  use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpFoundation\Response;
  use App\Entity\Magazzino;
  use App\Form\MagazzinoType;
 */

/**
 * Magazzino controller.
 */
class MagazzinoController extends FiController
{

    /**
     * Lists all tables entities.
     */
    public function index(Request $request, Packages $assetsmanager): Response
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');

        if (!$this->getPermessi()->canRead($controller)) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }
        $crudtemplate = $this->getCrudTemplate($bundle, $controller, $this->getThisFunctionName());

        $entityclassnotation = $this->getEntityClassName();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

        $modellocolonne = array(
            array('nometabella' => $controller, 'nomecampo' => "$controller.giornodellasettimana", 'etichetta' => 'Giorno della settimana',
                'ordine' => 10, 'larghezza' => 10, 'escluso' => false,
                'decodifiche' => array('Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'),
            ),
        );

        $filtri = array();
        $prefiltri = array();
        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->em);
        $tablenamefromentity = $entityutils->getTableFromEntity($entityclass);
        $colonneordinamento = array($tablenamefromentity . '.id' => 'DESC');
        $parametritabella = array('em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($tablenamefromentity),
            'nomecontroller' => ParametriTabella::setParameter($controller),
            'bundle' => ParametriTabella::setParameter($bundle),
            'entityname' => ParametriTabella::setParameter($entityclassnotation),
            'entityclass' => ParametriTabella::setParameter($entityclass),
            'formclass' => ParametriTabella::setParameter($formclass),
            'modellocolonne' => ParametriTabella::setParameter(json_encode($modellocolonne)),
            'permessi' => ParametriTabella::setParameter(json_encode($this->getPermessi()->toJson($controller))),
            'urltabella' => ParametriTabella::setParameter($assetsmanager->getUrl('') . $controller . '/' . 'tabella'),
            'baseurl' => ParametriTabella::setParameter($assetsmanager->getUrl('')),
            'idpassato' => ParametriTabella::setParameter($idpassato),
            'titolotabella' => ParametriTabella::setParameter('Elenco ' . $controller),
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
}
