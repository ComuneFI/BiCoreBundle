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

    }
    public function testSecuredOrdineUpdate()
    {
        $this->logInAdmin();
        $nomecontroller = 'Ordine';
        $this->client->request('GET', '/' . $nomecontroller . '/100/update');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        
    }
    public function testSecuredOrdineDelete()
    {
        $this->logInAdmin();
        $nomecontroller = 'Ordine';
        $csrfDeleteToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken($nomecontroller);
        $url = $this->client->getContainer()->get('router')->generate($nomecontroller . "_delete", array("id" => 1, "token" => $csrfDeleteToken));
        $this->client->request('GET', $url);
        //dump($this->client->getResponse());
        $this->assertSame(501, $this->client->getResponse()->getStatusCode());
    }
}
