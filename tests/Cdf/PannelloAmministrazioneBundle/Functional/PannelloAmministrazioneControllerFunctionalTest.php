<?php

namespace Cdf\BiCoreBundle\Tests\Controller;

use Cdf\BiCoreBundle\Tests\Utils\BiTestAuthorizedClient;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Cdf\BiCoreBundle\Tests\Utils\BiTest;

class PannelloAmministrazioneControllerFunctionalTest extends BiTestAuthorizedClient {

    protected static $client;

    /*
     * @test
     */

    public function test20AdminpanelGenerateBundle() {
        self::$client = static::createPantherClient();
        $container = static::createClient()->getContainer();
        //url da testare
        $apppath = $container->get('pannelloamministrazione.projectpath');
        try {

            /**
             * @var ProjectPath $apppath
             */
            $checkentityprova = $apppath->getSrcPath() .
                    DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR . 'Prova.php';
            $checktypeprova = $apppath->getSrcPath() .
                    DIRECTORY_SEPARATOR . 'Form' . DIRECTORY_SEPARATOR . 'ProvaType.php';
            $checkviewsprova = $apppath->getSrcPath() . DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Prova';
            $checkindexprova = $checkviewsprova .
                    DIRECTORY_SEPARATOR . 'Crud' . DIRECTORY_SEPARATOR . 'index.html.twig';

            $url = $this->getRoute('fi_pannello_amministrazione_homepage', [], false);

            self::$client->request('GET', $url);
            self::$client->waitFor('#adminpanelgenerateentity');
            $this->executeScript('document.getElementById("entityfile").value = "wbadmintest.mwb"');
            $this->pressButton('adminpanelgenerateentity');

            self::$client->waitFor('.biconfirmyes');
            $this->pressButton('biconfirmyes');

            self::$client->waitFor('#corebundlemodalinfo');
            $this->pressButton('biconfirmok');

            $this->logout();
            BiTest::clearcache();

            $this->visit($url);
            $this->login('admin', 'admin');

            $this->assertTrue(file_exists($checkentityprova));

            $this->pressButton('adminpanelaggiornadatabase');
            self::$client->waitFor('.biconfirmyes');

            $this->pressButton('biconfirmyes');

            self::$client->waitFor('.biconfirmok');
            $this->pressButton('biconfirmok');

            $this->logout();
            BiTest::clearcache();

            $this->visit($url);
            $this->login('admin', 'admin');
            $this->executeScript('document.getElementById("entityform").value = "Prova"');

            $this->pressButton('adminpanelgenerateformcrud');
            sleep(1);
            self::$client->waitFor('.biconfirmyes');
            $this->pressButton('biconfirmyes');
            sleep(1);

            self::$client->waitFor('.biconfirmok');
            $this->pressButton('biconfirmok');

            $this->assertTrue(file_exists($checktypeprova));
            $this->assertTrue(file_exists($checkviewsprova));
            $this->assertTrue(file_exists($checkindexprova));

            $this->logout();
            //BiTest::clearcache();
            BiTest::removecache();

            self::$client->reload();

            try {
                $urlRouting = $this->router->generate('Prova_container');
            } catch (\Exception $exc) {
                $urlRouting = '/Prova';
            }

            $url = $urlRouting;

            $this->visit($url);
            $this->login('admin', 'admin');

            $this->crudoperation(self::$client);
            self::$client->quit();
        } catch (\Exception $exc) {
            $container = static::createClient()->getContainer();
            //url da testare
            $apppath = $container->get('pannelloamministrazione.projectpath');
            $screenshotpath = $apppath->getVarPath() . DIRECTORY_SEPARATOR . 'errorAdmin.png';
            self::$client->takeScreenshot($screenshotpath);
            throw new \Exception($exc);
        }
    }

    private function crudoperation() {
        $this->clickElement('tabellaadd');

        /* Inserimento */
        $descrizionetest1 = 'Test inserimento descrizione automatico';
        $fieldhtml = 'prova_descrizione';

        self::$client->waitFor('#' . $fieldhtml);

        $this->fillField($fieldhtml, $descrizionetest1);

        self::$client->waitFor('#prova_submit');
        $this->clickElement('prova_submit');
        sleep(1);
        $em = static::createClient()->getContainer()->get('doctrine')->getManager();

        $qb1 = $em->createQueryBuilder()
                        ->select(['Prova'])
                        ->from('\\App\\Entity\\Prova', 'Prova')
                        ->where('Prova.descrizione = :descrizione')
                        ->setParameter('descrizione', $descrizionetest1)
                        ->getQuery()->getResult();

        $provaobj1 = $qb1[0];
        $rowid = $provaobj1->getId();
        $this->clickElement('.bibottonimodificatabellaProva[data-biid="' . $rowid . '"]');
        $contextmenuedit = 'a.h-100.d-flex.align-items-center.btn.btn-xs.btn-primary';
        self::$client->waitFor($contextmenuedit);
        $this->clickElement($contextmenuedit);

        $this->assertEquals($provaobj1->getDescrizione(), $descrizionetest1);

        //Modifica
        $descrizionetest2 = 'Test inserimento descrizione automatico 2';

        sleep(1);
        self::$client->waitFor('#' . $fieldhtml);

        $this->fillField($fieldhtml, $descrizionetest2);

        $this->clickElement('prova_submit');
        sleep(1);
        $em->clear();

        $em = static::createClient()->getContainer()->get('doctrine')->getManager();
        $qb2 = $em->createQueryBuilder()
                        ->select(['Prova'])
                        ->from('\\App\\Entity\\Prova', 'Prova')
                        ->where('Prova.id = :id')
                        ->setParameter('id', $rowid)
                        ->getQuery()->getResult();

        $this->assertEquals($qb2[0]->getDescrizione(), $descrizionetest2);

        $this->clickElement('.bibottonimodificatabellaProva[data-biid="' . $rowid . '"]');

        $contextmenuedit = 'a.h-100.d-flex.align-items-center.btn.btn-xs.btn-danger';
        self::$client->waitFor($contextmenuedit);
        $this->clickElement($contextmenuedit);

        //$this->rightClickElement('.context-menu-crud[data-bitableid="' . $rowid . '"]');
        //$client->waitFor('.context-menu-item.context-menu-icon.context-menu-icon-delete');
        //sleep(2);
        //$this->clickElement('.context-menu-item.context-menu-icon.context-menu-icon-delete');
        self::$client->waitFor('.biconfirmyes');
        $this->pressButton('biconfirmyes');
        sleep(1);

        $qb3 = $em->createQueryBuilder()
                        ->select(['Prova'])
                        ->from('\\App\\Entity\\Prova', 'Prova')
                        ->where('Prova.descrizione = :descrizione')
                        ->setParameter('descrizione', $descrizionetest2)
                        ->getQuery()->getResult();

        $this->assertEquals(count($qb3), 0);

        $qb = $em->createQueryBuilder();
        $qb->delete();
        $qb->from('\\Cdf\\BiCoreBundle\\Entity\\Colonnetabelle', 'o');
        $qb->where('o.nometabella= :tabella');
        $qb->setParameter('tabella', 'Prova');
        $qb->getQuery()->execute();
        $em->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void {
        self::$client->quit();
        parent::tearDown();
        BiTest::cleanFilesystem();
        BiTest::removecache();
        BiTest::clearcache();
    }

}
