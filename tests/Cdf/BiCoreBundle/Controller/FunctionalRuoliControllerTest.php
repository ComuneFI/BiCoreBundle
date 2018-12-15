<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalRuoliControllerTest extends FifreeTestAuthorizedClient
{
    public function testRuoliIndex()
    {
        $ruoliregistrati = 3;
        $htmltableid = 'tableRuoli';
        $client = $this->getClient();
        $testUrl = '/Ruoli/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaRuoli to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $ruoli = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });
        $this->assertSame($ruoliregistrati, count($ruoli));
    }
}
