<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerProdottofornitoreIndexTest extends BiTestAuthorizedClient
{
    public function testFunctionalProdottofornitoreIndex()
    {
        $prodottifornitoreregistrati = 11;
        $htmltableid = 'tableProdottofornitore';
        $client = static::createPantherClient();
        $testUrl = '/Prodottofornitore/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaProdottofornitore to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $prodottifornitore = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                return trim($td->text());
            });
        });
        $this->assertSame($prodottifornitoreregistrati, count($prodottifornitore));
        $this->logout();
    }
}
