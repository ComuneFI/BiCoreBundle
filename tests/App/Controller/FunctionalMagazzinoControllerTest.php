<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalMagazzinoControllerTest extends FifreeTestAuthorizedClient
{
    public function testFunctionalMagazzinoIndex()
    {
        $magazzinoregistrati = 11;
        $htmltableid = "tabellaMagazzino";
        $client = $this->getClient();
        $testUrl = '/Magazzino/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaMagazzino to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $magazzino = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
            });
        });
        $this->assertSame($magazzinoregistrati, count($magazzino));
    }
}
