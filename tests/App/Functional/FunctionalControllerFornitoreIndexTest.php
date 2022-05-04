<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerFornitoreIndexTest extends BiTestAuthorizedClient {

    public function testFunctionalFornitoreIndex() {
        $client = static::createPantherClient();
        try {
            $fornitoriregistrati = 3;
            $htmltableid = 'tableFornitore';
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
            //$this->logout();
            sleep(1);
            $client->quit();
        } catch (\Exception $exc) {
            $client->takeScreenshot('tests/var/errorFunctionalFornitoreIndex.png');
            throw new \Exception($exc);
        }
    }

}
