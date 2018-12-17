<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalOpzionitabelleControllerTest extends BiTestAuthorizedClient
{
    public function testOpzionitabelleIndex()
    {
        $opzionitabelleregistrati = 3;
        $htmltableid = 'tableOpzionitabelle';
        $client = $this->getClient();
        $testUrl = '/Opzionitabelle/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaOpzionitabelle to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $opzionitabelle = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });
        $this->assertSame($opzionitabelleregistrati, count($opzionitabelle));
    }
}
