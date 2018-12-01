<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalClienteControllerTest extends FifreeTestAuthorizedClient
{
    public function testFunctionalClienteIndex()
    {
        $clientiregistrati = 15;
        $htmltableid = "tableCliente";
        $client = $this->getClient();
        $testUrl = '/Cliente/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaCliente to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $clienti = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
            });
        });
        $this->assertSame($clientiregistrati, count($clienti));
    }
}
