<?php

use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseNorolesAuthorizedClient;

class TestTablesNotAuthorizedControllerTest extends BiWebtestcaseNorolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredNotAuthorizedIndex()
    {
        $routes = array('Cliente', 'Fornitore', 'Prodottofornitore',
            'Magazzino', 'Ordine', );
        foreach ($routes as $route) {
            $client = $this->client;
            $url = $client->getContainer()->get('router')->generate($route.'_container');
            //$this->assertContains('DoctrineORMEntityManager', get_class($em));

            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }
    }

    public function testSecuredNotAuthorizedTabella()
    {
        $route = 'Cliente';
        $this->logInAdmin();
        $client = $this->client;
        $url = $client->getContainer()->get('router')->generate($route.'_container');
        $crawler = $client->request('GET', $url);
        $parametri = $this->getParametriTabella($route, $crawler);

        $crawler = $client->request('GET', '/logout');
        $this->logInUsernoreoles();

        $url = $client->getContainer()->get('router')->generate($route.'_tabella');
        //$this->assertContains('DoctrineORMEntityManager', get_class($em));
        $client->request('POST', '/'.$route.'/tabella', array('parametri' => $parametri));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
