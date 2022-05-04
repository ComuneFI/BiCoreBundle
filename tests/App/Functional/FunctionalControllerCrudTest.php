<?php

namespace App\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class FunctionalControllerCrudTest extends BiTestAuthorizedClient {

    public function testFunctionalOrdineEditinline() {
        $client = static::createPantherClient();
        try {
            $clientiregistrati = 15;
            $htmltableid = 'tableOrdine';
            $testUrl = '/Ordine/';
            $crawler = $client->request('GET', $testUrl);
            $client->waitFor('#' . $htmltableid); // Wait for the tabellaCliente to appear
            //$this->executeScript('$("#ParametriOrdine").attr("data-editinline","Ma==");');
            $this->executeScript("document.getElementById('ParametriOrdine').dataset.editinline= 'Ma=='");
            $this->pressButton('.tabellarefresh');
            sleep(2);
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
            sleep(5);

            /* qui */
            $container = static::createClient()->getContainer();
            $em = $container->get('doctrine')->getManager();

            $ordinerow = $em->getRepository('\\App\\Entity\\Ordine')->find(9);
            $this->assertEquals($qta1ex, $ordinerow->getQuantita());
            $this->clickElement('.tabellarefresh');
            //$this->executeScript('$(".tabellarefresh").click();');
            sleep(2);

            $this->pressButton('.tabellarefresh');
            sleep(5);

            $qta2ex = 22;

            $this->rightClickElement('.context-menu-crud[data-bitableid="9"]');
            sleep(2);
            $client->waitFor('.context-menu-item.context-menu-icon.context-menu-icon-edit');
            sleep(2);
            $this->clickElement('.context-menu-item.context-menu-icon.context-menu-icon-edit');
            sleep(2);
            $this->executeScript("document.querySelector('#tableOrdine > tbody > tr:nth-child(2) > td:nth-child(4) > div > input').value=" . $qta2ex);

            sleep(1);
            $this->clickElement($selectorconfirm);
            sleep(5);

            /* qui */
            $em->clear();
            $ordinerow2 = $em->getRepository('\\App\\Entity\\Ordine')->find(9);
            $this->assertEquals($qta2ex, $ordinerow2->getQuantita());
            sleep(1);
            //$this->logout();
            $client->quit();
        } catch (\Exception $exc) {
            $client->takeScreenshot('tests/var/errorFunctionalOrdineEditinline.png');
            throw new \Exception($exc);
        }
    }

}
