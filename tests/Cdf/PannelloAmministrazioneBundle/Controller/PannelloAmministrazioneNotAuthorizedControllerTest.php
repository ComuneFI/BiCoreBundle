<?php

use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseNorolesAuthorizedClient;

class PannelloAmministrazioneNotAuthorizedControllerTest extends BiWebtestcaseNorolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredAdminpanelIndex()
    {
        $routes = array('fi_pannello_amministrazione_homepage');
        $client = $this->logInUser();
        foreach ($routes as $route) {
            $url = $client->getContainer()->get('router')->generate($route);
            //$this->assertStringContainsString('DoctrineORMEntityManager', get_class($em));

            $client->request('GET', $url);
            $this->assertEquals(403, $client->getResponse()->getStatusCode());
        }
    }
}
