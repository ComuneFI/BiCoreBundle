<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class BiCoreBundleControllerTest extends BiWebtestcaseAuthorizedClient
{

    public function setUp(): void
    {
        parent::setUp();
    }

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
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 6)',
                $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Colonnetabelle/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provacolonnatabella = 'Provacolonnatabella';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => $provacolonnatabella));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertContains(
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
        $this->assertContains(
                'Provacolonnatabella2',
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Colonnetabelle');
        $crawler = $client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

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
        $this->assertContains(
                'Pagina 1 di 2 (Righe estratte: 16)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Menuapplicazione/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provamenuapplicazione = 'Provamenuapplicazione';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('menuapplicazione_item');
        $camporuolo = 'menuapplicazione[nome]';
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => $provamenuapplicazione));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertContains(
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
        $this->assertContains(
                'Provamenuapplicazione2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Menuapplicazione');
        $crawler = $client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

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
        $this->assertContains(
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
//        $this->assertContains(
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
        $this->assertContains(
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
        $this->assertContains(
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
    }

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
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Opzionitabelle/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaopzionitabelle = 'testopzionitabelle';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('opzionitabelle_item');
        $camporuolo = 'opzionitabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => $provaopzionitabelle));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertContains(
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
        $this->assertContains(
                'Provaopzionitabella2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Opzionitabelle');
        $crawler = $client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

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
        $this->assertContains(
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
//        $this->assertContains(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaopermessi = 'testpermessi';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('permessi_item');
        $camporuolo = 'permessi[modulo]';
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => $provaopermessi));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertContains(
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
        $this->assertContains(
                'Provapermessi2',
                $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Permessi');
        $crawler = $client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

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
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $client->getResponse()->getContent()
        );

        //New
        $crawler = $client->request('GET', '/Ruoli/new');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $client->getResponse()->getContent()
//        );
        $provaruolo = 'Provaruolo';
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => $provaruolo));
        // submit that form
        $crawler = $client->submit($form);
        $this->assertContains(
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
        $this->assertContains(
                'Provaruolo2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Ruoli');
        $crawler = $client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

}
