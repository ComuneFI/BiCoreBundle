<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class RuoliControllerTest extends BiWebtestcaseAuthorizedClient
{
    public function testSecuredRuoliIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Ruoli';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Ruoli/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 1 (Righe estratte: 3)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Ruoli/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaruolo = 'Provaruolo';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => $provaruolo));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                'Provaruolo', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Ruoli')->findByRuolo($provaruolo);
        $ruoloinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => 'Provaruolo2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provaruolo2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Ruoli');
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }

}
