<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Cliente;
use App\Entity\Prodottofornitore;
use App\Entity\Ordine;

/**
 * Report controller.
 */
class ReportController extends AbstractController
{

    /**
     * @Route( "/Report/{_anno}", name="Report_container", defaults={"_anno"=null}, methods={"GET","HEAD"})
     */
    public function indexAction(Request $request): Response
    {
        $anno = ($request->get('_anno') ? $request->get('_anno') : \date('Y'));
        //$this->addFlash("success", "Anno " . $anno);

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();

        /* @var $qb \Doctrine\ORM\QueryBuilder */
        /* @var $ordini \App\Entity\Ordine[] */
        $qb = $em->createQueryBuilder();
        $qb->select(array('a'));
        $qb->from(Ordine::class, 'a');
        $ordini = $qb->getQuery()->getResult();

        /* @var $clientirepository \App\Repository\ClienteRepository */
        $clientirepository = $em->getRepository(Cliente::class);
        $clientiattivi = $clientirepository->findAttivi();
        $clientidisattivati = $clientirepository->findDisattivati();

        /* @var $prodottofornitorerepository \App\Repository\ProdottofornitoreRepository */
        $prodottofornitorerepository = $em->getRepository(Prodottofornitore::class);
        $prodottidisponibili = $prodottofornitorerepository->findDisponibili();
        $prodottinondisponibili = $prodottofornitorerepository->findNonDisponibili();

        /* @var $ordinerepository \App\Repository\OrdineRepository */
        $ordinerepository = $em->getRepository(Ordine::class);
        $ordinidelmese = $ordinerepository->findOrdiniAnnoMese(\date('Y'), \date('m'));

        $twigparms = array(
            'anno' => $anno,
            'ordini' => $ordini,
            'ordinidelmese' => $ordinidelmese,
            'clientiattivi' => $clientiattivi,
            'clientidisattivati' => $clientidisattivati,
            'prodottidisponibili' => $prodottidisponibili,
            'prodottinondisponibili' => $prodottinondisponibili,
        );

        return $this->render('Report\index.html.twig', $twigparms);
    }
}
