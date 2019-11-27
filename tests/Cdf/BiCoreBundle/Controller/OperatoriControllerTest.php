<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class OperatoriControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function testSecuredOperatoriIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Operatori';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $client->request('POST', '/Operatori/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 1 (Righe estratte: 3)', $client->getResponse()->getContent()
        );

        //Export xls
        $crawler = $client->request('POST', '/Operatori/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue('200' == $responseData['status']);

        //New
        $crawler = $client->request('GET', '/Operatori/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertStringContainsString(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaooperatori = 'testoperatore';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('operatori_item');
        $username = 'operatori[username]';
        $password1 = 'operatori[password][first]';
        $password2 = 'operatori[password][second]';
        $email = 'operatori[email]';
        $form = $crawler->filter('form[id=formdatiOperatori]')->form(
                array(
                    $username => $provaooperatori,
                    $password1 => $provaooperatori,
                    $password2 => $provaooperatori,
                    $email => $provaooperatori . '@' . $provaooperatori,
                )
        );
        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString(
                $provaooperatori, $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Operatori')->findByUsername($provaooperatori);
        $operatoriinserito = $entity[0];

        //Edit
        $crawler = $client->request('GET', '/Operatori/' . $operatoriinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('operatori_item');
        $username = 'operatori[username]';
        $form = $crawler->filter('form[id=formdatiOperatori]')->form(array("$username" => 'Provaoperatori2'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/Operatori/' . $operatoriinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Provaoperatori2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $qb = $this->em->createQueryBuilder();
        $qb->delete();
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where('o.username= :username');
        $qb->setParameter('username', 'Provaoperatori2');
        $qb->getQuery()->execute();
        $this->em->clear();
        //$client = static::createClient();
        $client->restart();
    }
}
