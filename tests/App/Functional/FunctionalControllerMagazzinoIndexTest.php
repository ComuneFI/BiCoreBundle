<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerMagazzinoIndexTest extends BiTestAuthorizedClient
{
    public function testSecuredFunctionalMagazzinoIndex()
    {
        $url = $this->getRoute('Magazzino_container');
        $client = static::createPantherClient();

        $client->request('GET', $url);
        $client->waitFor('.tabellasearch');

        $this->pressButton('.tabellasearch');
        $fieldhtml = 'html body div.tabella-container div#tabellaMagazzino div#TabMagazzinoContent.tab-content div#tabMagazzino1a.tab-pane.p-3.fade.active.show div div.panel.panel-primary.filterable table#tableMagazzino.table.table-sm.table-responsive-sm.table-striped.bitable.table-hover thead tr.filters.d-flex th.biw-19 input.form-control.colonnatabellafiltro';
        $this->fillField($fieldhtml, 'ed');

        $this->findField($fieldhtml)->sendKeys("\n");
        sleep(5);
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->getCurrentPageContent());
        $numrowstabella = $crawler->filterXPath('//table[@id="tableMagazzino"]')->filter('tbody')->filter('tr')->count();
        $this->assertEquals(5, $numrowstabella);

        $this->pressButton('birimuovifiltriMagazzino');
        sleep(5);
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->getCurrentPageContent());
        $numrowstabella = $crawler->filterXPath('//table[@id="tableMagazzino"]')->filter('tbody')->filter('tr')->count();
        $this->assertEquals(11, $numrowstabella);

        $this->logout();
        $client->quit();
    }
}
