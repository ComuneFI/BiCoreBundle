<?php

use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseReadrolesAuthorizedClient;

class TestTablesReadonlyAuthorizedControllerTest extends Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseReadrolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredReadonlyAuthorizedIndex()
    {
        
            $client = $this->client;
            $url = $client->getContainer()->get('router')->generate("Magazzino_container");
            $client->request('GET', $url);
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
            $url = $client->getContainer()->get('router')->generate("Cliente_container");
            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        
    }
}
