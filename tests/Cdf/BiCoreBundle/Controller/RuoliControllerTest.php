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
            'Pagina 1 di 1 (Righe estratte: 3)',
            $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Ruoli/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaruolo = 'Provaruolo';
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => $provaruolo));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
            'Provaruolo',
            $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->em = self::bootKernel()->getContainer()
                ->get('doctrine')
                ->getManager();
        $entity = $this->em->getRepository('\\Cdf\\BiCoreBundle\\Entity\\Ruoli')->findByRuolo($provaruolo);
        $ruoloinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => 'Provaruolo2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
            'Provaruolo2',
            $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $crawler->filter('input[name="ruoli[_token]"]')->attr('value');
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        //dump($client->getResponse());
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        //$client = static::createClient();
        $client->restart();
    }
}
