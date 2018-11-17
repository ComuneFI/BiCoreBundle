<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class OpzionitabelleControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredOpzionitabelleIndex()
    {
        $this->client->request('GET', '/Opzionitabelle');
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($crawler);

        $this->client->request('POST', '/Opzionitabelle/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Pagina 1 di 1 (Righe estratte: 3)',
            $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Opzionitabelle/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaopzionitabelle = "testopzionitabelle";
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("opzionitabelle_item");
        $camporuolo = "opzionitabelle[nometabella]";
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => $provaopzionitabelle));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
            $provaopzionitabelle,
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository("BiCoreBundle:Opzionitabelle")->findByNometabella($provaopzionitabelle);
        $opzionitabelleinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("opzionitabelle_item");
        $camporuolo = "opzionitabelle[nometabella]";
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => "Provaopzionitabella2"));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Provaopzionitabella2',
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
