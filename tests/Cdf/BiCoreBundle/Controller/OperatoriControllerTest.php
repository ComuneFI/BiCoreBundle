<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class OperatoriControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredOperatoriIndex()
    {
        $nomecontroller = 'Operatori';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Operatori/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $this->client->getResponse()->getContent()
        );

        //Export xls
        $crawler = $this->client->request('POST', '/Operatori/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData["status"] == "200");

        //New
        $crawler = $this->client->request('GET', '/Operatori/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaooperatori = "testoperatore";
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("operatori_item");
        $username = "operatori[username]";
        $password1 = "operatori[password][first]";
        $password2 = "operatori[password][second]";
        $email = "operatori[email]";
        $form = $crawler->filter('form[id=formdatiOperatori]')->form(
                array(
                    $username => $provaooperatori,
                    $password1 => $provaooperatori,
                    $password2 => $provaooperatori,
                    $email => $provaooperatori . "@" . $provaooperatori,
                )
        );
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                $provaooperatori, $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository("BiCoreBundle:Operatori")->findByUsername($provaooperatori);
        $operatoriinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Operatori/' . $operatoriinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken("operatori_item");
        $username = "operatori[username]";
        $form = $crawler->filter('form[id=formdatiOperatori]')->form(array("$username" => "Provaoperatori2"));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Operatori/' . $operatoriinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provaoperatori2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $qb = $this->em->createQueryBuilder();
        $qb->delete();
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where("o.username= :username");
        $qb->setParameter('username', "Provaoperatori2");
        $qb->getQuery()->execute();
        $this->em->clear();
    }
}
