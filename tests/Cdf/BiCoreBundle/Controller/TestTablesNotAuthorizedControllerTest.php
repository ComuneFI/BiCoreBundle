<?php

use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseNorolesAuthorizedClient;

class TestTablesNotAuthorizedControllerTest extends FifreeWebtestcaseNorolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredNotAuthorizedIndex()
    {
        $routes = array('Cliente', 'Fornitore', 'Prodottofornitore',
            'Magazzino', 'Ordine');
        foreach ($routes as $route) {
            $client = $this->client;
            $url = $client->getContainer()->get('router')->generate($route . "_container");
            //$this->assertContains('DoctrineORMEntityManager', get_class($em));

            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }
    }
//    public function testSecuredNotAuthorizedTabella()
//    {
//        $routes = array('Cliente', 'Fornitore', 'Prodottofornitore',
//            'Magazzino', 'Ordine');
//        foreach ($routes as $route) {
//            $client = $this->client;
//            $url = $client->getContainer()->get('router')->generate($route . "_tabella");
//            //$this->assertContains('DoctrineORMEntityManager', get_class($em));
//
//            $crawler = $client->request('GET', $url);
//            $parametri = $this->getParametriTabella($route, $crawler);
//
//            $this->assertEquals(403, $client->getResponse()->getStatusCode());
//        }
//    }
}
