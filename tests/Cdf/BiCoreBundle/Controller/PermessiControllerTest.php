<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class PermessiControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredPermessiIndex()
    {
        $nomecontroller = 'Permessi';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Permessi/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Pagina 1 di 1 (Righe estratte: 1)',
            $this->client->getResponse()->getContent()
        );

        //Export xls
        $crawler = $this->client->request('POST', '/Permessi/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData["status"] == "200");

        //New
        $crawler = $this->client->request('GET', '/Permessi/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaopermessi = "testpermessi";
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("permessi_item");
        $camporuolo = "permessi[modulo]";
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => $provaopermessi));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
            $provaopermessi,
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository("BiCoreBundle:Permessi")->findByModulo($provaopermessi);
        $permessiinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("permessi_item");
        $camporuolo = "permessi[modulo]";
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => "Provapermessi2"));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Provapermessi2',
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
