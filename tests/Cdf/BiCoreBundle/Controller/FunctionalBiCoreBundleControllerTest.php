<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalBiCoreBundleControllerTest extends BiTestAuthorizedClient
{
    public function testBiCoreBundleIndex()
    {
        $colonnetabelleregistrati = 6;
        $htmltableid = 'tableColonnetabelle';
        $client = $this->getClient();
        $testUrl = '/Colonnetabelle/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaColonnetabelle to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $colonnetabelle = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($colonnetabelleregistrati, count($colonnetabelle));

        $menuapplicazioneregistrati = 15;
        $htmltableid = 'tableMenuapplicazione';
        $client = $this->getClient();
        $testUrl = '/Menuapplicazione/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaMenuapplicazione to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $menuapplicazione = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($menuapplicazioneregistrati, count($menuapplicazione));

        $operatoriregistrati = 3;
        $htmltableid = 'tableOperatori';
        $client = $this->getClient();
        $testUrl = '/Operatori/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaOperatori to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $operatori = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($operatoriregistrati, count($operatori));

        $opzionitabelleregistrati = 3;
        $htmltableid = 'tableOpzionitabelle';
        $client = $this->getClient();
        $testUrl = '/Opzionitabelle/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaOpzionitabelle to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $opzionitabelle = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($opzionitabelleregistrati, count($opzionitabelle));

        $permessiregistrati = 1;
        $htmltableid = 'tablePermessi';
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

        $ruoliregistrati = 3;
        $htmltableid = 'tableRuoli';
        $client = $this->getClient();
        $testUrl = '/Ruoli/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaRuoli to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $ruoli = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($ruoliregistrati, count($ruoli));
        $this->logout();
    }
}
