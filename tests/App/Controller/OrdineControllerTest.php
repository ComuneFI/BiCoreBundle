<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeWebtestcaseAuthorizedClient;
use Symfony\Component\HttpFoundation\Response;

class OrdineControllerTest extends FifreeWebtestcaseAuthorizedClient
{
    public function testSecuredOrdineIndex()
    {
        $this->logInAdmin();
        $nomecontroller = 'Ordine';
        $this->client->request('GET', '/' . $nomecontroller);
        $crawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/' . $nomecontroller . '/1/delete');
        $this->assertSame(501, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/' . $nomecontroller . '/100/update');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
