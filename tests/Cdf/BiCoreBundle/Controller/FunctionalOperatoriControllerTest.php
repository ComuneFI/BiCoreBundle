<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalOperatoriControllerTest extends FifreeTestAuthorizedClient
{
    public function testOperatoriIndex()
    {
        $operatoriregistrati = 3;
        $htmltableid = "tabellaOperatori";
        $client = $this->getClient();
        $testUrl = '/Operatori/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaOperatori to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $operatori = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
            });
        });
        $this->assertSame($operatoriregistrati, count($operatori));
    }
}
