<?php

use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseReadrolesAuthorizedClient;

class TestTablesReadonlyAuthorizedControllerTest extends BiWebtestcaseReadrolesAuthorizedClient
{
    /*
     * @test
     */
    public function testSecuredReadonlyAuthorizedIndex()
    {
        $client = $this->logInUser();
        $url = $client->getContainer()->get('router')->generate('Magazzino_container');
        $client->request('GET', $url);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $url = $client->getContainer()->get('router')->generate('Cliente_container');
        $client->request('GET', $url);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $url = $client->getContainer()->get('router')->generate('Cliente_new');
        $client->request('GET', $url);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $url = $client->getContainer()->get('router')->generate('Cliente_edit', array('id' => 1));
        $client->request('GET', $url);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $url = $client->getContainer()->get('router')->generate('Cliente_update', array('id' => 1));
        $client->request('GET', $url);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());

        $csrfDeleteToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('Cliente');
        $url = $client->getContainer()->get('router')->generate('Cliente_delete', array('id' => 1, 'token' => $csrfDeleteToken));
        $client->request('GET', $url);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testSecuredReadonlyAuthorizedAggiorna()
    {
        //aggiorna ajax
        $client = $this->logInUser();
        $csrfTokenAggiorna = $client->getContainer()->get('security.csrf.token_manager')->getToken('1');
        $parametriagiorna = array('values' => array(array('fieldname' => 'Cliente.nominativo', 'fieldtype' => 'string', 'fieldvalue' => 'Andrea Manzo')));
        $client->request('POST', '/Cliente/1/'.$csrfTokenAggiorna.'/aggiorna', $parametriagiorna);
        $this->assertSame(403, $client->getResponse()->getStatusCode());
    }
}
