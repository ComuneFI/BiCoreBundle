<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;

class PannelloAmministrazioneControllerFunctionalTest extends BiTestAuthorizedClient
{
    /*
     * @test
     */

    public function test20AdminpanelGenerateBundle()
    {
        //url da testare
        $container = static::createClient()->getContainer();
        $apppath = $container->get('pannelloamministrazione.projectpath');
        $checkentityprova = $apppath->getSrcPath() .
                DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR . 'Prova.php';
        $checktypeprova = $apppath->getSrcPath() .
                DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'ProvaType.php';
        $checkviewsprova = $apppath->getSrcPath() . DIRECTORY_SEPARATOR . '..' .
                DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Prova';
        $checkindexprova = $checkviewsprova .
                DIRECTORY_SEPARATOR . 'Crud' . DIRECTORY_SEPARATOR . 'index.html.twig';

        $url = $this->getRoute('fi_pannello_amministrazione_homepage');

        $client = static::createPantherClient();

        $client->request('GET', $url);
        $client->waitFor('#adminpanelgenerateentity');
        $this->executeScript('document.getElementById("entityfile").value = "wbadmintest.mwb"');
        $this->pressButton('adminpanelgenerateentity');

        $client->waitFor('.biconfirmyes');
        $this->pressButton('biconfirmyes');

        $client->waitFor('#corebundlemodalinfo');
        $this->pressButton('biconfirmok');

        $this->logout();
        clearcache();

        $this->visit($url);
        $this->login('admin', 'admin');

        $this->assertTrue(file_exists($checkentityprova));

        $this->pressButton('adminpanelaggiornadatabase');
        $client->waitFor('.biconfirmyes');

        $this->pressButton('biconfirmyes');

        $client->waitFor('.biconfirmok');
        $this->pressButton('biconfirmok');

        $this->logout();
        clearcache();

        $this->visit($url);
        $this->login('admin', 'admin');
        $this->executeScript('document.getElementById("entityform").value = "Prova"');

        $this->pressButton('adminpanelgenerateformcrud');
        sleep(1);
        $client->waitFor('.biconfirmyes');
        $this->pressButton('biconfirmyes');
        sleep(1);

        $client->waitFor('.biconfirmok');
        $this->pressButton('biconfirmok');

        $this->assertTrue(file_exists($checktypeprova));
        $this->assertTrue(file_exists($checkviewsprova));
        $this->assertTrue(file_exists($checkindexprova));

        $this->logout();
        //clearcache();
        removecache();
        
        $client->reload();

        try {
            $urlRouting = $this->router->generate('Prova_container');
        } catch (\Exception $exc) {
            $urlRouting = '/Prova';
        }

        $url = $urlRouting;

        $this->visit($url);
        $this->login('admin', 'admin');

        $this->crudoperation($client);
    }

    private function crudoperation($client)
    {
        $this->clickElement('tabellaadd');

        /* Inserimento */
        $descrizionetest1 = 'Test inserimento descrizione automatico';
        $fieldhtml = 'prova_descrizione';
        
        $client->waitFor('#' . $fieldhtml);

        $this->fillField($fieldhtml, $descrizionetest1);

        $client->waitFor('#prova_submit');
        $this->clickElement('prova_submit');
        sleep(2);
        $em = static::createClient()->getContainer()->get('doctrine')->getManager();

        $qb1 = $em->createQueryBuilder()
                        ->select(array('Prova'))
                        ->from('App:Prova', 'Prova')
                        ->where('Prova.descrizione = :descrizione')
                        ->setParameter('descrizione', $descrizionetest1)
                        ->getQuery()->getResult();

        $provaobj1 = $qb1[0];
        $rowid = $provaobj1->getId();
        $this->clickElement('.bibottonimodificatabellaProva[data-biid="' . $rowid . '"]');
        $contextmenuedit = 'a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary';
        $client->waitFor($contextmenuedit);
        $this->clickElement($contextmenuedit);

        $this->assertEquals($provaobj1->getDescrizione(), $descrizionetest1);

        //Modifica
        $descrizionetest2 = 'Test inserimento descrizione automatico 2';

        sleep(2);
        $client->waitFor('#' . $fieldhtml);

        $this->fillField($fieldhtml, $descrizionetest2);

        $this->clickElement('prova_submit');
        sleep(2);
        $em->clear();

        $em = static::createClient()->getContainer()->get('doctrine')->getManager();
        $qb2 = $em->createQueryBuilder()
                        ->select(array("Prova"))
                        ->from("App:Prova", "Prova")
                        ->where("Prova.id = :id")
                        ->setParameter("id", $rowid)
                        ->getQuery()->getResult();

        $this->assertEquals($qb2[0]->getDescrizione(), $descrizionetest2);

        $this->clickElement('.bibottonimodificatabellaProva[data-biid="' . $rowid . '"]');

        $this->rightClickElement('.context-menu-crud[data-bitableid="' . $rowid . '"]');
        $client->waitFor('.context-menu-item.context-menu-icon.context-menu-icon-delete');
        sleep(5);
        $this->clickElement('.context-menu-item.context-menu-icon.context-menu-icon-delete');

        $client->waitFor('.biconfirmyes');
        $this->pressButton('biconfirmyes');
        sleep(2);

        $qb3 = $em->createQueryBuilder()
                        ->select(array('Prova'))
                        ->from('App:Prova', 'Prova')
                        ->where('Prova.descrizione = :descrizione')
                        ->setParameter('descrizione', $descrizionetest2)
                        ->getQuery()->getResult();

        $this->assertEquals(count($qb3), 0);

        $qb = $em->createQueryBuilder();
        $qb->delete();
        $qb->from('BiCoreBundle:Colonnetabelle', 'o');
        $qb->where('o.nometabella= :tabella');
        $qb->setParameter('tabella', 'Prova');
        $qb->getQuery()->execute();
        $em->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        static::createPantherClient()->quit();
        parent::tearDown();
        cleanFilesystem();
        removecache();
        clearcache();
    }

}
