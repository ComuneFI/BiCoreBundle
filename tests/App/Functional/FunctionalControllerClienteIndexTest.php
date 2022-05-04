<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerClienteIndexTest extends BiTestAuthorizedClient {

    public function testFunctionalClienteIndex() {
        $client = static::createPantherClient();
        try {
            $clientiregistrati = 15;
            $htmltableid = 'tableCliente';
            $testUrl = '/Cliente/';
            $crawler = $client->request('GET', $testUrl);
            $client->waitFor('#' . $htmltableid); // Wait for the tabellaCliente to appear
            $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
            $clienti = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                return $tr->filter('td')->each(function ($td, $i) {
                    return trim($td->text());
                });
            });
            $this->assertSame($clientiregistrati, count($clienti));

            sleep(1);
            $this->clickElement('.bibottonimodificatabellaCliente[data-biid="1"]');
            $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
            $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');

            $client->waitFor('#ClienteSubTabellaDettagliContainer');
            $this->clickElement('.bibottonimodificatabellaOrdine[data-biid="2"]');
            $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
            $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');

            $selectorinputqta = 'tr[data-bitableid=\"2\"] > td[data-nomecampo="Ordine.quantita"] :input';
            $selectorconfirm = '#tableOrdine > tbody:nth-child(2) > tr:nth-child(2) > td:nth-child(6) > a:nth-child(2)';

            $qta1ex = 11;

            sleep(1);
            $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=" . $qta1ex);

            sleep(1);
            $this->clickElement($selectorconfirm);

            sleep(1);

            $qta2ex = 10;

            $this->clickElement('.bibottonimodificatabellaOrdine[data-biid="2"]');
            $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
            $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');

            $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=" . $qta2ex);

            sleep(1);
            $this->clickElement($selectorconfirm);
            sleep(1);

            //Problema su salvataggio perchè non trovo un pulsante di conferma univoco per la pagina che riceverebbe il click
            $this->clickElement('#card-simple2-tab');
            sleep(1);

            $this->clickElement('.bibottonimodificatabellaMagazzino[data-biid="3"]');
            $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
            $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');

            $selectorconfirm = 'a.bibottonieditinline[data-biid="3"][data-tabella="Magazzino"]';

            $this->selectFieldOption('Magazzino_giornodellasettimana', 3);
            sleep(1);
            $this->clickElement($selectorconfirm);

            sleep(1);

            $this->clickElement('.bibottonimodificatabellaMagazzino[data-biid="3"]');
            $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
            $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');

            $this->selectFieldOption('Magazzino_giornodellasettimana', 2);
            sleep(1);
            sleep(1);
            $this->clickElement($selectorconfirm);
            sleep(1);
            $this->logout();
            $client->quit();
        } catch (\Exception $exc) {
            $client->takeScreenshot('tests/var/error.png');
            throw new \Exception($exc);
        }
    }

}
