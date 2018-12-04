<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\FifreeTestAuthorizedClient;

class FunctionalMagazzinoControllerTest extends FifreeTestAuthorizedClient
{
    /*public function testFunctionalMagazzinoIndex()
    {
        $magazzinoregistrati = 11;
        $htmltableid = "tableMagazzino";
        $client = $this->getClient();
        $testUrl = '/Magazzino/';
        $crawler = $client->request('GET', $testUrl);
        $client->waitFor('#' . $htmltableid); // Wait for the tabellaMagazzino to appear
        $this->assertSame(self::$baseUri . $testUrl, $client->getCurrentURL()); // Assert we're still on the same page
        $magazzino = $crawler->filterXPath('//table[@id="' . $htmltableid . '"]')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
            return $tr->filter('td')->each(function ($td, $i) {
                        return trim($td->text());
                    });
        });
        $this->assertSame($magazzinoregistrati, count($magazzino));
    }*/
    public function testSecuredFunctionalMagazzinoIndex()
    {

        $url = $this->getRoute('Magazzino_container');
        $client = $this->getClient();

        $client->request('GET', $url);
        $client->waitFor(".tabellasearch");

        $this->pressButton('.tabellasearch');
        $fieldhtml = "html body div.tabella-container div#tabellaMagazzino div#TabMagazzinoContent.tab-content div#tabMagazzino1a.tab-pane.p-3.fade.active.show div div.panel.panel-primary.filterable table#tableMagazzino.table.table-sm.table-responsive-sm.table-striped.bitable.table-hover thead tr.filters.d-flex th.biw-19 input.form-control.colonnatabellafiltro";
        $this->fillField($fieldhtml, "ed");

        $crawler = $this->findField($fieldhtml)->sendKeys("\n");
        $this->ajaxWait(6000);
        $numrowstabella = $this->evaluateScript("return $('#tableMagazzino > tbody > tr').length;");
        $this->assertEquals(5, $numrowstabella);

        //$this->assertContains('My Title', $crawler->filter('title')->html());
    }
}
