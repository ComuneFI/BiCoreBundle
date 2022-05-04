<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerOrdineIndexTest extends BiTestAuthorizedClient {

    public function testFunctionalOrdineIndex() {
        $client = static::createPantherClient();
        try {
            $ordiniregistrati = 14;
            $htmltableid = 'tableOrdine';
            $testUrl = '/Ordine/';
            $crawler = $client->request('GET', $testUrl);
            $client->waitFor('#' . $htmltableid); // Wait for the tabellaOrdine to appear
            $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
            $ordini = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($ordiniregistrati, count($ordini));
            $this->logout();
            $client->quit();
        } catch (\Exception $exc) {
            $client->takeScreenshot('tests/var/error.png');
            throw new \Exception($exc);
        }
    }

}
