<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class ColonnetabelleControllerTest extends BiWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSecuredColonnetabelleIndex()
    {
        $nomecontroller = 'Colonnetabelle';
        $this->client->request('GET', '/'.$nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Colonnetabelle/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Pagina 1 di 1 (Righe estratte: 6)',
            $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Colonnetabelle/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provacolonnatabella = 'Provacolonnatabella';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => $provacolonnatabella));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
            'Provacolonnatabella',
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Colonnetabelle')->findByNometabella($provacolonnatabella);
        $colonnatabellainserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Colonnetabelle/'.$colonnatabellainserito->getId().'/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => 'Provacolonnatabella2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Colonnetabelle/'.$colonnatabellainserito->getId().'/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'Provacolonnatabella2',
            $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Colonnetabelle');
        $crawler = $this->client->request('GET', '/Colonnetabelle/'.$colonnatabellainserito->getId().'/'.$csrfDeleteToken.'/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
