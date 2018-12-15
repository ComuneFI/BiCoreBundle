<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class RuoliControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSecuredRuoliIndex()
    {
        $nomecontroller = 'Ruoli';
        $this->client->request('GET', '/'.$nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Ruoli/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Ruoli/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaruolo = 'Provaruolo';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => $provaruolo));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                'Provaruolo', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Ruoli')->findByRuolo($provaruolo);
        $ruoloinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Ruoli/'.$ruoloinserito->getId().'/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => 'Provaruolo2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Ruoli/'.$ruoloinserito->getId().'/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provaruolo2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Ruoli');
        $crawler = $this->client->request('GET', '/Ruoli/'.$ruoloinserito->getId().'/'.$csrfDeleteToken.'/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
