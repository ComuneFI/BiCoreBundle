<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class MenuapplicazioneControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function testSecuredMenuapplicazioneIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Menuapplicazione';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Menuapplicazione/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 2 (Righe estratte: 16)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Menuapplicazione/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provamenuapplicazione = 'Provamenuapplicazione';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('menuapplicazione_item');
        $camporuolo = 'menuapplicazione[nome]';
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => $provamenuapplicazione));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                $provamenuapplicazione, $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Menuapplicazione')->findByNome($provamenuapplicazione);
        $menuapplicazioneinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('menuapplicazione_item');
        $camporuolo = 'menuapplicazione[nome]';
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => 'Provamenuapplicazione2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provamenuapplicazione2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Menuapplicazione');
        $crawler = $client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }

}
