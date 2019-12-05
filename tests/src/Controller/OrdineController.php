<?php

namespace App\Controller;

use Cdf\BiCoreBundle\Controller\FiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Ordine controller.
 */
class OrdineController extends FiController
{
    /**
     * Lists all tables entities.
     */
    public function index(Request $request, \Symfony\Component\Asset\Packages $assetsmanager)
    {
        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');
        if (!$this->getPermessi()->canRead($controller)) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }
        $template = $bundle.'/'.$controller.'/'.$this->getThisFunctionName().'.html.twig';
        if (!$this->get('twig')->getLoader()->exists($template)) {
            $template = $controller.'/Crud/'.$this->getThisFunctionName().'.html.twig';
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();

        $formclass = str_replace('Entity', 'Form', $entityclass);

        //La larghezza deve essere in percentuale
        $modellocolonne = array(
            array('nometabella' => $controller, 'nomecampo' => 'Ordine.quantita', 'larghezza' => 10),
            array('nometabella' => $controller, 'nomecampo' => 'Ordine.data', 'larghezza' => 15, 'editabile' => false),
            array('nometabella' => $controller, 'nomecampo' => 'Ordine.Cliente', 'larghezza' => 30),
            array('nometabella' => $controller, 'nomecampo' => 'Ordine.Prodottofornitore', 'larghezza' => 30, 'etichetta' => 'Prodotto', 'escluso' => false),
            array('nometabella' => $controller, 'nomecampo' => 'Ordine.Prodottofornitore.Fornitore.ragionesociale', 'larghezza' => 20, 'etichetta' => 'Ragione Sociale fornitore', 'escluso' => false),
                //array("nometabella" => $controller, "nomecampo" => "datanascita", "etichetta" => "Data di nascita", "ordine" => 20, "larghezza" => 100, "escluso" => false),
        );

        $colonneordinamento = array('Ordine.Cliente.nominativo' => 'DESC', 'Ordine.quantita' => 'ASC');
        //$wheremanuale = "(Ordine.quantita > 10 or (Cliente.nominativo = 'Emidio Picariello' AND Ordine.quantita < 10)) and (Prodottofornitore.descrizione='Quinoa') ";

        /* @var $em \Doctrine\ORM\EntityManager */
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        //$qb->expr()->
        //\Doctrine\Common\Collections\Criteria::expr()->

        $filtri = array(
                /* array(
                  "nomecampo" => "Ordine.quantita",
                  "operatore" => Comparison::GT,
                  "valore" => -1,
                  ),
                  array(
                  "nomecampo" => "Ordine.quantita",
                  "operatore" => Comparison::GTE,
                  "valore" => 0,
                  ) */
        );

//        $output = [
//            'date' => $datetime,
//        ];

        $prefiltri = array(
//            array(
//                "nomecampo" => "Cliente.attivo",
//                "operatore" => '=',
//                "valore" => false,
//            ),
//            array(
//                "nomecampo" => "Prodottofornitore.descrizione",
//                "operatore" => Comparison::NIN,
//                "valore" => array('A', 'COCCA'),
//            ),
//            array(
//                "nomecampo" => "Prodottofornitore.descrizione",
//                "operatore" => Comparison::IN,
//                "valore" => array('penne', 'spaghetti', 'fusilli', 'Succo di frutta albicocca', 'Quinoa'),
//            ),
//            array(
//                "nomecampo" => "Cliente.note",
//                "operatore" => Comparison::EQ,
//                "valore" => null,
//            ),
//            array(
//                "nomecampo" => "Cliente.punti",
//                "operatore" => Comparison::NEQ,
//                "valore" => null,
//            ),
//            array(
//                "nomecampo" => "Prodottofornitore.descrizione",
//                "operatore" => Comparison::CONTAINS,
//                "valore" => 'ruttA',
//            ),
//            array(
//                "nomecampo" => "Prodottofornitore.descrizione",
//                "operatore" => Comparison::STARTS_WITH,
//                "valore" => 'SuCcO',
//            ),
//            array(
//                "nomecampo" => "Prodottofornitore.descrizione",
//                "operatore" => Comparison::ENDS_WITH,
//                "valore" => 'biCocCa',
//            ),
//            array("nomecampo" => "Cliente.datanascita",
//                "operatore" => Comparison::GTE,
//                "valore" => array("date" => new DatetimeTabella("1960-01-01 23:59:59"))
//            ),
//            array("nomecampo" => "Cliente.datanascita",
//                "operatore" => Comparison::LTE,
//                "valore" => array("date" => new DatetimeTabella("1980-12-31"))
//            ),
                /* array("nomecampo" => "Ordine.Prodottofornitore.Fornitore.ragionesociale",
                  "operatore" => Comparison::EQ,
                  "valore" => "Alce Nero"
                  ),
                  array("nomecampo" => "Ordine.Cliente.nominativo",
                  "operatore" => Comparison::EQ,
                  "valore" => "Andrea Manzi"
                  ), */
                /* array(
                  "nomecampo" => "Cliente.attivo",
                  "operatore" => Comparison::EQ,
                  //"valore" => '01/09/2018 09:44',
                  "valore" => true,
                  ), */
                /* array(
                  "nomecampo" => "Prodottofornitore.descrizione",
                  "operatore" => "=",
                  "valore" => 'Spaghetti',
                  ) */
        );

        $entityutils = new \Cdf\BiCoreBundle\Utils\Entity\EntityUtils($this->get('doctrine')->getManager());

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
            'multiselezione' => ParametriTabella::setParameter('0'),
            'editinline' => ParametriTabella::setParameter('0'),
            'paginacorrente' => ParametriTabella::setParameter('1'),
            'paginetotali' => ParametriTabella::setParameter(''),
            'righeperpagina' => ParametriTabella::setParameter('15'),
            //'wheremanuale' => ParametriTabella::setParameter($wheremanuale),
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
}
