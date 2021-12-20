<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalBiCoreBundleControllerTest extends BiTestAuthorizedClient
{

    protected static $client;

    public function testBiCoreBundleIndex()
    {
        try {
            self::$client = static::createPantherClient();
            $colonnetabelleregistrati = 6;
            $htmltableid = 'tableColonnetabelle';
            $testUrl = '/Colonnetabelle/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaColonnetabelle to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $colonnetabelle = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($colonnetabelleregistrati, count($colonnetabelle));

            $menuapplicazioneregistrati = 15;
            $htmltableid = 'tableMenuapplicazione';
            $testUrl = '/Menuapplicazione/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaMenuapplicazione to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $menuapplicazione = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($menuapplicazioneregistrati, count($menuapplicazione));

            $operatoriregistrati = 3;
            $htmltableid = 'tableOperatori';
            $testUrl = '/Operatori/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaOperatori to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $operatori = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($operatoriregistrati, count($operatori));

            $opzionitabelleregistrati = 3;
            $htmltableid = 'tableOpzionitabelle';
            $testUrl = '/Opzionitabelle/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaOpzionitabelle to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $opzionitabelle = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($opzionitabelleregistrati, count($opzionitabelle));

            $permessiregistrati = 14;
            $htmltableid = 'tablePermessi';
            $testUrl = '/Permessi/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaPermessi to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $permessi = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($permessiregistrati, count($permessi));

            $ruoliregistrati = 3;
            $htmltableid = 'tableRuoli';
            $testUrl = '/Ruoli/';
            $crawler = self::$client->request('GET', $testUrl);
            self::$client->waitFor('#' . $htmltableid); // Wait for the tabellaRuoli to appear
            $this->assertSame(self::$baseUri . $testUrl, self::$client->getCurrentURL()); // Assert we're still on the same page
            $ruoli = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($ruoliregistrati, count($ruoli));
            //$this->logout();
        } catch (\Exception $exc) {
            self::$client->takeScreenshot('tests/var/error.png');
            throw new \Exception($exc);
        }
    }

    public function tearDown(): void
    {
        self::$client->quit();
        parent::tearDown();
    }
}
