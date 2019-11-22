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
        $client = $this->logInUser();
        foreach ($routes as $route) {
            $url = $client->getContainer()->get('router')->generate($route.'_container');
            //$this->assertStringContainsString('DoctrineORMEntityManager', get_class($em));

            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }
    }

    public function testSecuredNotAuthorizedTabella()
    {
        $route = 'Cliente';
        $client = $this->logInAdmin();
        $url = $client->getContainer()->get('router')->generate($route.'_container');
        $crawler = $client->request('GET', $url);
        $parametri = $this->getParametriTabella($route, $crawler);

        $crawler = $client->request('GET', '/logout');
        $client = $this->logInUsernoreoles();

        $url = $client->getContainer()->get('router')->generate($route.'_tabella');
        //$this->assertStringContainsString('DoctrineORMEntityManager', get_class($em));
        $client->request('POST', '/'.$route.'/tabella', array('parametri' => $parametri));

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
