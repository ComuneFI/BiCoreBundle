<?php
namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalMagazzinoControllerTest extends BiTestAuthorizedClient
{
    /* public function testFunctionalMagazzinoIndex()
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
      } */

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
//        $this->ajaxWait(6000);
        sleep(1);
        $crawler = new \Symfony\Component\DomCrawler\Crawler($this->getCurrentPageContent());
        $numrowstabella = $crawler->filterXPath('//table[@id="tableMagazzino"]')->filter('tbody')->filter('tr')->count();
        $this->assertEquals(5, $numrowstabella);
    }
}
