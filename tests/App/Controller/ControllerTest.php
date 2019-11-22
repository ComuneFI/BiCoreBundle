<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;
use Symfony\Component\HttpFoundation\Response;

class ControllerTest extends BiWebtestcaseAuthorizedClient
{
    public function testSecuredClienteIndex()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Cliente';
        $client->request('GET', '/' . $nomecontroller);
        $crawler =$client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('GET', '/' . $nomecontroller . '/1000/edit');
        $this->assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $parametri = $this->getParametriTabella($nomecontroller, $crawler);

        //Elenco valori entity
        $crawler = $client->request('GET', '/' . $nomecontroller . '/lista');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $ec = count($this->em->getRepository('App:' . $nomecontroller)->findAll());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(count($responseData), $ec);

        //Export xls
        $crawler = $client->request('POST', '/' . $nomecontroller . '/exportxls', array('parametri' => $parametri));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue('200' == $responseData['status']);

        $client->request('POST', '/' . $nomecontroller . '/tabella', array('parametri' => $parametri));

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Pagina 1 di 14 (Righe estratte: 210)', $client->getResponse()->getContent()
        );
        //Sub tables
        $client->request('POST', '/Ordine/indexDettaglio', array('parametripassati' => json_encode('{"prefiltri":[{"nomecampo":"Ordine.Cliente.id","operatore":"=","valore":1}],"titolotabella":"Ordini+del+cliente+Andrea+Manzi","modellocolonne":[{"nomecampo":"Ordine.Cliente","escluso":true}],"colonneordinamento":{"Ordine.data":"DESC","Ordine.quantita":"DESC"},"multiselezione":true}')));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('POST', '/Magazzino/indexDettaglio', array('parametripassati' => json_encode('{"prefiltri":[{"nomecampo":"Magazzino.Ordine.Cliente.id","operatore":"=","valore":1}],"modellocolonne":[{"nomecampo":"Magazzino.giornodellasettimana","escluso":false,"decodifiche":["Domenica","Lunedì","Martedì","Mercoledì","Giovedì","Venerdì","Sabato"]}],"titolotabella":"Roba+in+magazzino+del+cliente+Andrea+Manzi"}')));
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //New
        $crawler = $client->request('GET', '/Cliente/new');
        //Incomplete submit
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('cliente_item');
        $camponominativo = 'cliente[nominativo]';
        $form = $crawler->filter('form[id=formdati' . $nomecontroller . ']')->form(array("$camponominativo" => ''));

        // submit that form
        $crawler = $client->submit($form);
        $this->assertStringContainsString('Questo valore non dovrebbe essere vuoto', $client->getResponse()->getContent());

        $nominativo = 'Andrea Manzi';
        $entity = $this->em->getRepository('App:' . $nomecontroller)->findByNominativo($nominativo);
        $nominativonserito = $entity[0];

        //Update
        $crawler = $client->request('GET', '/Cliente/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Submit
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('cliente_item');
        $camponominativo = 'cliente[nominativo]';
        $form = $crawler->filter('form[id=formdati' . $nomecontroller . ']')->form(array("$camponominativo" => ''));

        // submit that form
        $crawler = $client->submit($form);

        //Edit
        $crawler = $client->request('GET', '/Cliente/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Submit
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('cliente_item');
        $camponominativo = 'cliente[nominativo]';
        $form = $crawler->filter('form[id=formdati' . $nomecontroller . ']')->form(array("$camponominativo" => 'Andrea Manzi 2'));

        // submit that form
        $crawler = $client->submit($form);

        $crawler = $client->request('GET', '/' . $nomecontroller . '/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Andrea Manzi 2', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Cliente');
        $camponominativo = 'cliente[nominativo]';
        $form = $crawler->filter('form[id=formdati' . $nomecontroller . ']')->form(array("$camponominativo" => 'Andrea Manzi'));

        // submit that form
        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/' . $nomecontroller . '/' . $nominativonserito->getId() . '/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString(
                'Andrea Manzi', $client->getResponse()->getContent()
        );
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        //Controllo storico modifiche
        $entity = $this->em->getRepository('BiCoreBundle:Storicomodifiche')->findByNometabella($nomecontroller);
        $this->assertSame(2, count($entity));
        foreach ($entity as $clientemodificato) {
            $this->assertSame('admin', $clientemodificato->getOperatori()->getUsername());
            $this->em->remove($clientemodificato);
            $this->em->flush();
        }
        $crawler = $client->request('GET', '/' . $nomecontroller . '/' . $nominativonserito->getId() . '/' . $csrfToken . '/delete');
        $this->assertSame(501, $client->getResponse()->getStatusCode());

        //Parametri per errori
        $parametripererrore = $parametri;
        $parametripererrore["modellocolonne"] = \Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella::setParameter('[{"nometabella":"Cliente","nomecampo":"Cliente.errore","etichetta":"Errore"}]');
        $client->request('POST', '/' . $nomecontroller . '/tabella', array('parametri' => $parametripererrore));

        $this->assertStringContainsString(
                'Cliente.errore field table option not found', $client->getResponse()->getContent()
        );
        $this->assertSame(500, $client->getResponse()->getStatusCode());

        $parametripererrore["modellocolonne"] = \Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella::setParameter('[]');
        $parametripererrore["filtri"] = \Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella::setParameter('[{"nomecampo":"Cliente.nominativa","operatore":"=","valore":"Andrea Manzi"}]');
        $client->request('POST', '/' . $nomecontroller . '/tabella', array('parametri' => $parametripererrore));

        $this->assertStringContainsString(
                'field or association Cliente.nominativa', $client->getResponse()->getContent()
        );
        $this->assertSame(500, $client->getResponse()->getStatusCode());

        $parametripererrore["filtri"] = \Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella::setParameter('[{"nomecampo":"Cliento.nominativo","operatore":"=","valore":"Andrea Manzi"}]');
        $client->request('POST', '/' . $nomecontroller . '/tabella', array('parametri' => $parametripererrore));

        $this->assertStringContainsString(
                'table or association Cliento not found', $client->getResponse()->getContent()
        );
        $this->assertSame(500, $client->getResponse()->getStatusCode());
    }
    public function testSecuredClienteAggiorna()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Cliente';

        //aggiorna ajax
        $csrfTokenAggiorna = $client->getContainer()->get('security.csrf.token_manager')->getToken('1');
        $parametriagiorna = array('values' => array(array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => 'Andrea Manzo')));
        $client->request('POST', '/Cliente/1/' . $csrfTokenAggiorna . '/aggiorna', $parametriagiorna);
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $parametriagiorna = array('values' => array(array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => 'Andrea Manzi')));
        $client->request('POST', '/Cliente/1/' . $csrfTokenAggiorna . '/aggiorna', $parametriagiorna);
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $parametriagiorna = array('values' => array(array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => 'Andrea Manzi')));
        $client->request('POST', '/Cliente/1000/' . $csrfTokenAggiorna . '/aggiorna', $parametriagiorna);
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $parametriagiorna = array('values' => array(array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => 'Andrea Manzi')));
        $client->request('POST', '/Cliente/1/TokenNonValido/aggiorna', $parametriagiorna);
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
    public function testSecuredClienteInsertInline()
    {
        $client = $this->logInAdmin();
        $nomecontroller = 'Cliente';
        $nominativo = 'Manzi Andrea';
        //aggiorna ajax
        $csrfTokenInserisci = $client->getContainer()->get('security.csrf.token_manager')->getToken('0');
        $parametriinsert = array('values' => array(
                array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => $nominativo),
                array('fieldname' => 'Cliente.datanascita', 'fieldtype' => 'date', 'fieldvalue' => '07/01/1990'),
                array('fieldname' => 'Cliente.attivo', 'fieldtype' => 'boolean', 'fieldvalue' => '1'),
                array('fieldname' => 'Cliente.punti', 'fieldtype' => 'integer', 'fieldvalue' => '1'),
        ));
        $client->request('POST', '/Cliente/0/' . $csrfTokenInserisci . '/aggiorna', $parametriinsert);

        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue('0' == $responseData['errcode']);
        $this->assertTrue('Registrazione eseguita' == $responseData['message']);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $entity = $this->em->getRepository('App:' . $nomecontroller)->findByNominativo($nominativo);
        $this->assertSame(1, count($entity));

        foreach ($entity as $clientemodificato) {
            $this->em->remove($clientemodificato);
            $this->em->flush();
            $this->em->clear();
        }
        $entitybis = $this->em->getRepository('App:' . $nomecontroller)->findByNominativo($nominativo);
        $this->assertSame(0, count($entitybis));
    }
    public function testSecuredOrdineIndex()
    {
        $client=$this->logInAdmin();
        $nomecontroller = 'Ordine';
        $client->request('GET', '/' . $nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
    public function testSecuredOrdineUpdate()
    {
        $client=$this->logInAdmin();
        $nomecontroller = 'Ordine';
        $client->request('GET', '/' . $nomecontroller . '/100/update');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
    public function testSecuredOrdineDelete()
    {
        $client=$this->logInAdmin();
        $nomecontroller = 'Ordine';
        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken($nomecontroller);
        $url = $client->getContainer()->get('router')->generate($nomecontroller . '_delete', array('id' => 1, 'token' => $csrfDeleteToken));
        $client->request('GET', $url);
        //dump($client->getResponse());
        $this->assertSame(501, $client->getResponse()->getStatusCode());
    }
}
