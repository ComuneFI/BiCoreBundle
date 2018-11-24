<?php

use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseNorolesAuthorizedClient;

class PannelloAmministrazioneNotAuthorizedControllerTest extends FifreeWebtestcaseNorolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredAdminpanelIndex()
    {
        $routes = array('fi_pannello_amministrazione_homepage', 'Permessi',
            'Menuapplicazione', 'Opzionitabelle', 'Colonnetabelle', 'Operatori', 'Ruoli');
        foreach ($routes as $route) {
            $client = $this->client;
            $url = $client->getContainer()->get('router')->generate($route);
            //$this->assertContains('DoctrineORMEntityManager', get_class($em));

            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }
    }
}
