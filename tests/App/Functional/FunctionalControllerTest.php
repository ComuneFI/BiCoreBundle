<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerTest extends BiTestAuthorizedClient
{
    public function testFunctionalClienteIndex()
    {
        $clientiregistrati = 15;
        $htmltableid = 'tableCliente';
        $client = $this->getClient();
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
        $this->logout();
    }
    public function testFunctionalFornitoreIndex()
    {
        $fornitoriregistrati = 3;
        $htmltableid = 'tableFornitore';
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
        $this->logout();
    }
    public function testSecuredFunctionalMagazzinoIndex()
    {
        $url = $this->getRoute('Magazzino_container');
        $client = $this->getClient();

        $client->request('GET', $url);
        $client->waitFor('.tabellasearch');

        $this->pressButton('.tabellasearch');
        $fieldhtml = 'html body div.tabella-container div#tabellaMagazzino div#TabMagazzinoContent.tab-content div#tabMagazzino1a.tab-pane.p-3.fade.active.show div div.panel.panel-primary.filterable table#tableMagazzino.table.table-sm.table-responsive-sm.table-striped.bitable.table-hover thead tr.filters.d-flex th.biw-19 input.form-control.colonnatabellafiltro';
        $this->fillField($fieldhtml, 'ed');

        $this->findField($fieldhtml)->sendKeys("\n");
        sleep(1);
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->getCurrentPageContent());
        $numrowstabella = $crawler->filterXPath('//table[@id="tableMagazzino"]')->filter('tbody')->filter('tr')->count();
        $this->assertEquals(5, $numrowstabella);

        $this->pressButton('birimuovifiltriMagazzino');
        sleep(1);
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->getCurrentPageContent());
        $numrowstabella = $crawler->filterXPath('//table[@id="tableMagazzino"]')->filter('tbody')->filter('tr')->count();
        $this->assertEquals(11, $numrowstabella);

        $this->logout();
    }
    public function testFunctionalOrdineIndex()
    {
        $ordiniregistrati = 14;
        $htmltableid = 'tableOrdine';
        $client = $this->getClient();
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
    }
    public function testFunctionalOrdineEditinline()
    {
        $clientiregistrati = 15;
        $htmltableid = 'tableOrdine';
        $client = $this->getClient();
        $testUrl = '/Ordine/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaCliente to appear
        ;
        //$this->executeScript('$("#ParametriOrdine").attr("data-editinline","Ma==");');
        $this->executeScript("document.getElementById('ParametriOrdine').dataset.editinline= 'Ma=='");
        $this->pressButton('.tabellarefresh');
        sleep(1);
        $this->clickElement('.bibottonimodificatabellaOrdine[data-biid="9"]');
        $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
        $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
        //$this->dblClickElement(".bibottonimodificatabellaOrdine[data-biid=\"9\"]");
        //$this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        sleep(2);
        $selectorinputqta = 'tr[data-bitableid=\"9\"] > td[data-nomecampo="Ordine.quantita"] :input';
        $selectorconfirm = 'a.bibottonieditinline[data-biid="9"]';

        $qta1ex = 21;

        //$this->executeScript("$('".$selectorinputqta."').val(".$qta1ex.')');
        sleep(1);
        $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=" . $qta1ex);

        //$this->executeScript("document.getElementById('" . $selectorinputqta . "').value = " . $qta1ex . '');
        sleep(1);
        //$this->executeScript("$('".$selectorconfirm."').click()");
        $this->clickElement($selectorconfirm);
        sleep(1);

        /* qui */
        $ordinerow = $this->em->getRepository('App:Ordine')->find(9);
        $this->assertEquals($qta1ex, $ordinerow->getQuantita());
        $this->clickElement('.tabellarefresh');
        //$this->executeScript('$(".tabellarefresh").click();');
        sleep(1);

        $this->pressButton('.tabellarefresh');
        sleep(1);

        $qta2ex = 22;

        $this->rightClickElement('.context-menu-crud[data-bitableid="9"]');
        $client->waitFor('.context-menu-item.context-menu-icon.context-menu-icon-edit');
        $this->clickElement('.context-menu-item.context-menu-icon.context-menu-icon-edit');
        
        $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=" . $qta2ex);

        sleep(1);
        $this->clickElement($selectorconfirm);
        sleep(1);


        /* qui */
        $this->em->clear();
        $ordinerow2 = $this->em->getRepository('App:Ordine')->find(9);
        $this->assertEquals($qta2ex, $ordinerow2->getQuantita());

        $this->logout();
    }
    public function testFunctionalProdottofornitoreIndex()
    {
        $prodottifornitoreregistrati = 11;
        $htmltableid = 'tableProdottofornitore';
        $client = $this->getClient();
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
        $this->logout();
    }
}
