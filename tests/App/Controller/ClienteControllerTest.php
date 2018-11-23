<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;
use Symfony\Component\HttpFoundation\Response;

class ClienteControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function testSecuredClienteIndex()
    {
        $this->logInAdmin();
        $nomecontroller = 'Cliente';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        //Export xls
        $crawler = $this->client->request('POST', '/' . $nomecontroller . '/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData["status"] == "200");

        $this->client->request('POST', '/' . $nomecontroller . '/tabella', array('parametri' => $parametri));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 14 (Righe estratte: 210)', $this->client->getResponse()->getContent()
        );

        $nominativo = "Andrea Manzi";
        $entity = $this->em->getRepository("App:" . $nomecontroller)->findByNominativo($nominativo);
        $nominativonserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Cliente/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("cliente_item");
        $camponominativo = "cliente[nominativo]";
        $form = $crawler->filter('form[id=formdati'.$nomecontroller.']')->form(array("$camponominativo" => "Andrea Manzi 2"));

        // submit that form
        $crawler = $this->client->submit($form);

        $crawler = $this->client->request('GET', '/'.$nomecontroller.'/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Andrea Manzi 2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("cliente_item");
        $camponominativo = "cliente[nominativo]";
        $form = $crawler->filter('form[id=formdati'.$nomecontroller.']')->form(array("$camponominativo" => "Andrea Manzi"));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/'.$nomecontroller.'/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Andrea Manzi', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Controllo storico modifiche
        $entity = $this->em->getRepository("BiCoreBundle:Storicomodifiche")->findByNometabella($nomecontroller);
        $this->assertSame(2, count($entity));
        foreach ($entity as $clientemodificato) {
            $this->assertSame("admin", $clientemodificato->getOperatori()->getUsername());
        }
    }
}
