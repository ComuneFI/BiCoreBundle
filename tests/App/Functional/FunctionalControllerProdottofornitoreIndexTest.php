<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerProdottofornitoreIndexTest extends BiTestAuthorizedClient {

    public function testFunctionalProdottofornitoreIndex() {
        $client = static::createPantherClient();
        try {

            $prodottifornitoreregistrati = 11;
            $htmltableid = 'tableProdottofornitore';
            $testUrl = '/Prodottofornitore/';
            $crawler = $client->request('GET', $testUrl);
            $client->waitFor('#' . $htmltableid); // Wait for the tabellaProdottofornitore to appear
            $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
            $prodottifornitore = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($prodottifornitoreregistrati, count($prodottifornitore));
            //$this->logout();
            sleep(1);
            $client->quit();
        } catch (\Exception $exc) {
            $client->takeScreenshot('tests/var/errorFunctionalProdottofornitoreIndex.png');
            throw new \Exception($exc);
        }
    }

}
