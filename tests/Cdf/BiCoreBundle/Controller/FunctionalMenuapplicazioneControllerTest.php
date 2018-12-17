<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalMenuapplicazioneControllerTest extends BiTestAuthorizedClient
{
    public function testMenuapplicazioneIndex()
    {
        $menuapplicazioneregistrati = 15;
        $htmltableid = 'tableMenuapplicazione';
        $client = $this->getClient();
        $testUrl = '/Menuapplicazione/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaMenuapplicazione to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $menuapplicazione = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });
        $this->assertSame($menuapplicazioneregistrati, count($menuapplicazione));
    }
}
