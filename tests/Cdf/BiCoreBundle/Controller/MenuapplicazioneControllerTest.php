<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class MenuapplicazioneControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredMenuapplicazioneIndex()
    {
        $this->client->request('GET', '/Menuapplicazione');
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($crawler);

        $this->client->request('POST', '/Menuapplicazione/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Pagina 1 di 2 (Righe estratte: 16)',
            $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Menuapplicazione/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provamenuapplicazione = "Provamenuapplicazione";
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("menuapplicazione_item");
        $camporuolo = "menuapplicazione[nome]";
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => $provamenuapplicazione));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
            $provamenuapplicazione,
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository("BiCoreBundle:Menuapplicazione")->findByNome($provamenuapplicazione);
        $menuapplicazioneinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("menuapplicazione_item");
        $camporuolo = "menuapplicazione[nome]";
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => "Provamenuapplicazione2"));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Provamenuapplicazione2',
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
