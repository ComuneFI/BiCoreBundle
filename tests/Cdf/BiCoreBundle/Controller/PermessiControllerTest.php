<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class PermessiControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function testSecuredPermessiIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Permessi';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Permessi/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 1 (Righe estratte: 1)',
                $client->getResponse()->getContent()
        );

        //Export xls
        $crawler = $client->request('POST', '/Permessi/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue('200' == $responseData['status']);

        //New
        $crawler = $client->request('GET', '/Permessi/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaopermessi = 'testpermessi';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('permessi_item');
        $camporuolo = 'permessi[modulo]';
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => $provaopermessi));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                $provaopermessi,
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Permessi')->findByModulo($provaopermessi);
        $permessiinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('permessi_item');
        $camporuolo = 'permessi[modulo]';
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => 'Provapermessi2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provapermessi2',
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Permessi');
        $crawler = $client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }
}
