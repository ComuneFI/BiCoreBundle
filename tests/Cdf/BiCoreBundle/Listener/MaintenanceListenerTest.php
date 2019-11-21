<?php

namespace Cdf\BiCoreBundle\Tests\Listener;

use Symfony\Component\HttpFoundation\Response;
use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class MaintenanceListenerTest extends BiWebtestcaseAuthorizedClient
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSecuredMaintenanceListener()
    {
        $client = $this->logInAdmin();
        $lockfile = $client->getContainer()->getParameter('bi_core.lockfile');

        @unlink($lockfile);
        $nomecontroller = 'Ruoli';

        $crawler = $client->request('GET', '/'.$nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $msg = 'Sito momentaneamente sottoposto a manutenzione';
        file_put_contents($lockfile, $msg);

        $client->request('GET', '/'.$nomecontroller);
        $this->assertSame(503, $client->getResponse()->getStatusCode());
        $this->assertContains($msg, $client->getResponse()->getContent());

        @unlink($lockfile);

        $crawler = $client->request('GET', '/'.$nomecontroller);
        $crawler = $client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
