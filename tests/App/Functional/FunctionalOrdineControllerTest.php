<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalOrdineControllerTest extends BiTestAuthorizedClient
{
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
        $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=".$qta1ex);

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

        $qta2ex = 22;
        //$this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        $this->clickElement('.bibottonimodificatabellaOrdine[data-biid="9"]');
        $client->waitFor('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
        $this->clickElement('a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary');
        
//        sleep(2);
//        $this->executeScript("$('" . $selectorinputqta . "').val(" . $qta2ex . ')');
//        sleep(1);
//        $this->executeScript("$('" . $selectorconfirm . "').click()");
//        sleep(1);
                //$this->executeScript("$('".$selectorinputqta."').val(".$qta1ex.')');
        $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=".$qta2ex);

        //$this->executeScript("document.getElementById('" . $selectorinputqta . "').value = " . $qta1ex . '');
        sleep(1);
        //$this->executeScript("$('".$selectorconfirm."').click()");
        $this->clickElement($selectorconfirm);
        sleep(1);


        /* qui */
        $this->em->clear();
        $ordinerow2 = $this->em->getRepository('App:Ordine')->find(9);
        $this->assertEquals($qta2ex, $ordinerow2->getQuantita());

        $this->logout();
    }
}
