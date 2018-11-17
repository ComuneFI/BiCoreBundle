<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalFornitoreControllerTest extends FifreeTestAuthorizedClient
{
    public function testFunctionalFornitoreIndex()
    {
        $fornitoriregistrati = 3;
        $htmltableid = "tabellaFornitore";
        $client = $this->getClient();
        $testUrl = '/Fornitore/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaFornitore to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $fornitori = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
            });
        });
        $this->assertSame($fornitoriregistrati, count($fornitori));
    }
}
