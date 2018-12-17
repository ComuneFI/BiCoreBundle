<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalColonnetabelleControllerTest extends BiTestAuthorizedClient
{
    public function testColonnetabelleIndex()
    {
        $colonnetabelleregistrati = 6;
        $htmltableid = 'tableColonnetabelle';
        $client = $this->getClient();
        $testUrl = '/Colonnetabelle/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaColonnetabelle to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $colonnetabelle = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });
        $this->assertSame($colonnetabelleregistrati, count($colonnetabelle));
    }
}
