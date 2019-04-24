<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class BiCoreBundleControllerTest extends BiWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredColonnetabelleIndex()
    {
        $nomecontroller = 'Colonnetabelle';
        $this->client->request('GET', '/' . $nomecontroller);
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
        $crawler = $this->client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('colonnetabelle_item');
        $camporuolo = 'colonnetabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiColonnetabelle]')->form(array("$camporuolo" => 'Provacolonnatabella2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provacolonnatabella2',
                $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Colonnetabelle');
        $crawler = $this->client->request('GET', '/Colonnetabelle/' . $colonnatabellainserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
    public function testSecuredMenuapplicazioneIndex()
    {
        $nomecontroller = 'Menuapplicazione';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Menuapplicazione/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 2 (Righe estratte: 16)', $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Menuapplicazione/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provamenuapplicazione = 'Provamenuapplicazione';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('menuapplicazione_item');
        $camporuolo = 'menuapplicazione[nome]';
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => $provamenuapplicazione));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                $provamenuapplicazione, $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Menuapplicazione')->findByNome($provamenuapplicazione);
        $menuapplicazioneinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('menuapplicazione_item');
        $camporuolo = 'menuapplicazione[nome]';
        $form = $crawler->filter('form[id=formdatiMenuapplicazione]')->form(array("$camporuolo" => 'Provamenuapplicazione2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provamenuapplicazione2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Menuapplicazione');
        $crawler = $this->client->request('GET', '/Menuapplicazione/' . $menuapplicazioneinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
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
        $this->assertTrue('200' == $responseData['status']);

        //New
        $crawler = $this->client->request('GET', '/Operatori/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaooperatori = 'testoperatore';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('operatori_item');
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
        $crawler = $this->client->submit($form);
        $this->assertContains(
                $provaooperatori, $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Operatori')->findByUsername($provaooperatori);
        $operatoriinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Operatori/' . $operatoriinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('operatori_item');
        $username = 'operatori[username]';
        $form = $crawler->filter('form[id=formdatiOperatori]')->form(array("$username" => 'Provaoperatori2'));

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
        $qb->where('o.username= :username');
        $qb->setParameter('username', 'Provaoperatori2');
        $qb->getQuery()->execute();
        $this->em->clear();
    }
    public function testSecuredOpzionitabelleIndex()
    {
        $nomecontroller = 'Opzionitabelle';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Opzionitabelle/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Opzionitabelle/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaopzionitabelle = 'testopzionitabelle';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('opzionitabelle_item');
        $camporuolo = 'opzionitabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => $provaopzionitabelle));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                $provaopzionitabelle, $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Opzionitabelle')->findByNometabella($provaopzionitabelle);
        $opzionitabelleinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('opzionitabelle_item');
        $camporuolo = 'opzionitabelle[nometabella]';
        $form = $crawler->filter('form[id=formdatiOpzionitabelle]')->form(array("$camporuolo" => 'Provaopzionitabella2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provaopzionitabella2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Opzionitabelle');
        $crawler = $this->client->request('GET', '/Opzionitabelle/' . $opzionitabelleinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
    public function testSecuredPermessiIndex()
    {
        $nomecontroller = 'Permessi';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Permessi/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 1)',
                $this->client->getResponse()->getContent()
        );

        //Export xls
        $crawler = $this->client->request('POST', '/Permessi/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue('200' == $responseData['status']);

        //New
        $crawler = $this->client->request('GET', '/Permessi/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaopermessi = 'testpermessi';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('permessi_item');
        $camporuolo = 'permessi[modulo]';
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => $provaopermessi));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                $provaopermessi,
                $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Permessi')->findByModulo($provaopermessi);
        $permessiinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('permessi_item');
        $camporuolo = 'permessi[modulo]';
        $form = $crawler->filter('form[id=formdatiPermessi]')->form(array("$camporuolo" => 'Provapermessi2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provapermessi2',
                $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Permessi');
        $crawler = $this->client->request('GET', '/Permessi/' . $permessiinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
    public function testSecuredRuoliIndex()
    {
        $nomecontroller = 'Ruoli';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        $this->client->request('POST', '/Ruoli/tabella', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Pagina 1 di 1 (Righe estratte: 3)', $this->client->getResponse()->getContent()
        );

        //New
        $crawler = $this->client->request('GET', '/Ruoli/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        $this->assertContains(
//                'Utente', $this->client->getResponse()->getContent()
//        );
        $provaruolo = 'Provaruolo';
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => $provaruolo));
        // submit that form
        $crawler = $this->client->submit($form);
        $this->assertContains(
                'Provaruolo', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $entity = $this->em->getRepository('BiCoreBundle:Ruoli')->findByRuolo($provaruolo);
        $ruoloinserito = $entity[0];

        //Edit
        $crawler = $this->client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ruoli_item');
        $camporuolo = 'ruoli[ruolo]';
        $form = $crawler->filter('form[id=formdatiRuoli]')->form(array("$camporuolo" => 'Provaruolo2'));

        // submit that form
        $crawler = $this->client->submit($form);
        $crawler = $this->client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
                'Provaruolo2', $this->client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        //Delete
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('Ruoli');
        $crawler = $this->client->request('GET', '/Ruoli/' . $ruoloinserito->getId() . '/' . $csrfDeleteToken . '/delete');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
