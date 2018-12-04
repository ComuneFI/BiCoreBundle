<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalOrdineControllerTest extends FifreeTestAuthorizedClient
{
    public function testFunctionalOrdineIndex()
    {
        $ordiniregistrati = 14;
        $htmltableid = "tableOrdine";
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
        $htmltableid = "tableOrdine";
        $client = $this->getClient();
        $testUrl = '/Ordine/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaCliente to appear
        $this->executeScript('$("#ParametriOrdine").attr("data-editinline","Ma==");');
        $this->executeScript('$(".tabellarefresh").click();');
        $this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        $selectorinputqta = "tr.d-flex:nth-child(1) > td:nth-child(4) > div:nth-child(1) > input:nth-child(1)";
        $selectorconfirm = "tr.d-flex:nth-child(1) > td:nth-child(8) > a:nth-child(2)";
        $qta1ex = 21;
        $this->executeScript("$('".$selectorinputqta."').val(".$qta1ex.")");
        $this->executeScript("$('".$selectorconfirm."').click()");
        sleep(1);
        $qta1 = $this->evaluateScript('return $("tr.d-flex:nth-child(1) > td:nth-child(4) > input:nth-child(1)").val();');
        $this->assertEquals($qta1ex, $qta1);

        sleep(1);
        $this->executeScript('$(".tabellarefresh").click();');
        sleep(1);
            
        $qta2ex = 22;
        $this->executeScript("$('.bibottonimodificatabellaOrdine[data-biid=\"9\"]').dblclick();");
        $this->executeScript("$('".$selectorinputqta."').val(".$qta2ex.")");
        $this->executeScript("$('".$selectorconfirm."').click()");
        sleep(1);
        $selectorinputqtadisabled = "tr.d-flex:nth-child(1) > td:nth-child(4) > input:nth-child(1)";
        $qta2 = $this->evaluateScript('return $("'.$selectorinputqtadisabled.'").val();');
        $this->assertEquals($qta2ex, $qta2);
        $this->logout();
    }
}
