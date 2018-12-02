<?php

namespace Cdf\BiCoreBundle\Tests\Listener;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;

class MaintenanceListenerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function setUp()
    {
        parent::setUp();
    }
    public function testSecuredMaintenanceListener()
    {
        $lockfile = $this->client->getContainer()->getParameter("bi_core.lockfile");
        
        @unlink($lockfile);
        $nomecontroller = 'Ruoli';
        
        $crawler = $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $msg = "Sito momentaneamente sottoposto a manutenzione";
        file_put_contents($lockfile, $msg);

        $this->client->request('GET', '/' . $nomecontroller);
        $this->assertSame(503, $this->client->getResponse()->getStatusCode());
        $this->assertContains($msg, $this->client->getResponse()->getContent());
        
        @unlink($lockfile);
        
        $crawler = $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
    }
}
