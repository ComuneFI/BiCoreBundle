<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalPermessiControllerTest extends FifreeTestAuthorizedClient
{
    public function testPermessiIndex()
    {
        $permessiregistrati = 1;
        $htmltableid = "tablePermessi";
        $client = $this->getClient();
        $testUrl = '/Permessi/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaPermessi to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $permessi = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
            });
        });
        $this->assertSame($permessiregistrati, count($permessi));
    }
}
