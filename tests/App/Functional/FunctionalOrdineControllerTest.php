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
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaOrdine to appear
        $this->assertSame(self::$baseUri.$testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $ordini = $crawler->filterXPath('//table[@id="'.$htmltableid.'"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
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
        $client->waitFor('#'.$htmltableid); // Wait for the tabellaCliente to appear
        $this->executeScript('$("#ParametriOrdine").attr("data-editinline","Ma==");');
        $this->executeScript('$(".tabellarefresh").click();');
        sleep(1);
        $this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        sleep(2);
        $selectorinputqta = 'tr[data-bitableid=\"9\"] > td[data-nomecampo="Ordine.quantita"] :input';
        $selectorconfirm = 'a.bibottonieditinline[data-biid="9"]';

        $qta1ex = 21;
        $this->executeScript("$('".$selectorinputqta."').val(".$qta1ex.')');
        sleep(1);
        $this->executeScript("$('".$selectorconfirm."').click()");
        sleep(1);

        /* qui */
        $ordinerow = $this->em->getRepository('App:Ordine')->find(9);
        $this->assertEquals($qta1ex, $ordinerow->getQuantita());

        $this->executeScript('$(".tabellarefresh").click();');
        sleep(1);

        $qta2ex = 22;
        $this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        sleep(2);
        $this->executeScript("$('".$selectorinputqta."').val(".$qta2ex.')');
        sleep(1);
        $this->executeScript("$('".$selectorconfirm."').click()");
        sleep(1);

        /* qui */
        $this->em->clear();
        $ordinerow2 = $this->em->getRepository('App:Ordine')->find(9);
        $this->assertEquals($qta2ex, $ordinerow2->getQuantita());

        $this->logout();
    }
}
