<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class OpzionitabelleControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function testSecuredOpzionitabelleIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Opzionitabelle';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Opzionitabelle/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 1 (Righe estratte: 3)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Opzionitabelle/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaopzionitabelle = 'testopzionitabelle';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('opzionitabelle_item');
        $camporuolo = 'opzionitabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => $provaopzionitabelle));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                $provaopzionitabelle, $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Opzionitabelle')->findByNometabella($provaopzionitabelle);
        $opzionitabelleinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('opzionitabelle_item');
        $camporuolo = 'opzionitabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => 'Provaopzionitabella2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provaopzionitabella2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Opzionitabelle');
        $crawler = $client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }

}
