<?php

namespace App\Controller;

use Cdf\BiCoreBundle\Controller\FiController;
use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Cliente controller.
 */
class ClienteController extends FiController
{
    /**
     * Lists all tables entities.
     */
    public function index(Request $request, \Symfony\Component\Asset\Packages $assetsmanager)
    {
        /* $dateimm = new \DateTimeImmutable($datestrchk);
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita = :data")
          ->setParameter("data", $dateimm);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // 0
         */

        /* $datestrchk = "1980-02-05";

          $datedtchk = \DateTime::createFromFormat("Y-m-d H:i:s", $datestrchk . " 00:00:00");
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita = :data")
          ->setParameter("data", $datedtchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // NOT FOUND

          $datedtchk = \DateTime::createFromFormat("Y-m-d", $datestrchk);
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita = :data")
          ->setParameter("data", $datedtchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // NOT FOUND

          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita = :data")
          ->setParameter("data", $datestrchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // OK

          $date1start = \DateTime::createFromFormat("Y-m-d H:i:s", "1980-02-04 23:59:59");
          $date1end = \DateTime::createFromFormat("Y-m-d H:i:s", "1980-02-05 00:00:00");

          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita >= :data1start AND Cliente.datanascita <= :data1end")
          ->setParameter("data1start", $date1start)
          ->setParameter("data1end", $date1end);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // OK

          $date2start = \DateTime::createFromFormat("Y-m-d H:i:s", "1980-02-05 00:00:00");
          $date2end = \DateTime::createFromFormat("Y-m-d H:i:s", "1980-02-05 00:00:01");

          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita >= :data2start AND Cliente.datanascita <= :data2end")
          ->setParameter("data2start", $date2start)
          ->setParameter("data2end", $date2end);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // NOT FOUND

          /* $datestrftimechk = strftime("%Y-%m-%d", strtotime($datestrchk));
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.datanascita = :data")
          ->setParameter("data", $datestrchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); // OK
         */

        /* $datestrchk = '1999-01-01 13:15:00';
          $datestrftimechk = strftime("%Y-%m-%d %H:%i:%s", strtotime($datestrchk));
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.iscrittoil = :data")
          ->setParameter("data", $datestrchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret);

          $datestrftimechk = strftime("%Y-%m-%d", strtotime($datestrchk));
          $qb = $this->get("doctrine")->getManager()->createQueryBuilder()
          ->select(array("Cliente"))
          ->from("App:Cliente", "Cliente")
          ->where("Cliente.iscrittoil = :data")
          ->setParameter("data", $datestrchk);
          $ret = $qb->getQuery()->getResult();
          echo count($ret); */

        $bundle = $this->getBundle();
        $controller = $this->getController();
        $idpassato = $request->get('id');
        if (!$this->getPermessi()->canRead($controller)) {
            throw new AccessDeniedException('Non si hanno i permessi per visualizzare questo contenuto');
        }
        $template = $bundle.':'.$controller.':'.$this->getThisFunctionName().'.html.twig';
        if (!$this->get('twig')->getLoader()->exists($template)) {
            $template = $controller.'/Crud/'.$this->getThisFunctionName().'.html.twig';
        }

        $entityclassnotation = $this->getEntityClassNotation();
        $entityclass = $this->getEntityClassName();
        $formclass = str_replace('Entity', 'Form', $entityclass);

        $modellocolonne = array(
            array('nometabella' => $controller, 'nomecampo' => "$controller.nominativo", 'etichetta' => 'Nominativo', 'ordine' => 10, 'larghezza' => 20, 'escluso' => false),
            array('nometabella' => $controller, 'nomecampo' => "$controller.datanascita", 'etichetta' => 'Data di nascita', 'ordine' => 20, 'larghezza' => 12, 'escluso' => false),
            array('nometabella' => $controller, 'nomecampo' => "$controller.saluto", 'etichetta' => 'Salutami', 'ordine' => 30, 'tipocampo' => 'string', 'campoextra' => true),
                //, "escluso" => false, "larghezza" => 15, "association" => false, "tipocampo"=>"string", "editabile"=>false
        );

        $colonneordinamento = array($controller.'.id' => 'ASC');

        $filtri = array(
                /* array("nomecampo" => "Cliente.nominativo",
                  "operatore" => Comparison::CONTAINS,
                  "valore" => "PipPà"
                  ),
                  array("nomecampo" => "Cliente.nominativo",
                  "operatore" => Comparison::CONTAINS,
                  "valore" => "DegL'"
                  ), */
                /* array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::EQ,
                  "valore" => array("date" => new DatetimeTabella("1980-02-05T00:00:00+00:00"))
                  ), */
                /* array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::EQ,
                  "valore" => "1980-02-05")
                  , */

                /* array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::GTE,
                  "valore" => "1980-02-04")
                  ,
                  array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::LTE,
                  "valore" => "1980-02-06")
                  , */

                /* array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::GTE,
                  "valore" => array("date" => new DatetimeTabella("1978-08-19 23:59:59"))
                  ),
                  array("nomecampo" => "Cliente.datanascita",
                  "operatore" => Comparison::LTE,
                  "valore" => array("date" => new DatetimeTabella("1978-08-20 00:00:00"))
                  ), */
                /* array("nomecampo" => "Cliente.iscrittoil",
                  "operatore" => Comparison::GTE,
                  "valore" => array("date" => new DatetimeTabella("1999-01-01 13:16:00"))
                  ), */
        );
        $prefiltri = array();
        //dump(json_encode($filtri));exit;
        $parametritabella = array('em' => ParametriTabella::setParameter('default'),
            'tablename' => ParametriTabella::setParameter($controller),
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
            'estraituttirecords' => ParametriTabella::setParameter(0),
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
