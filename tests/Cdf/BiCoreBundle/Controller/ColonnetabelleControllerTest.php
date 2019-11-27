<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class ColonnetabelleControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function testSecuredColonnetabelleIndex()
    {
        $nomecontroller = 'Colonnetabelle';
        $client = $this->logInAdmin();
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Colonnetabelle/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 1 (Righe estratte: 6)',
                $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Colonnetabelle/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provacolonnatabella = 'Provacolonnatabella';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => $provacolonnatabella));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                'Provacolonnatabella',
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Colonnetabelle')->findByNometabella($provacolonnatabella);
        $colonnatabellainserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => 'Provacolonnatabella2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provacolonnatabella2',
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Colonnetabelle');
        $crawler = $client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }

}
