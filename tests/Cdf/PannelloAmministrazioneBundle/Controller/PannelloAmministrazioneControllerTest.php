<?php

use Cdf\BiCoreBundle\Tests\Utils\BiWebtestcaseAuthorizedClient;

class PannelloAmministrazioneControllerTest extends BiWebtestcaseAuthorizedClient
{
    /*
     * @test
     */

    public function testSecuredAdminpanelIndex()
    {
        $client = $this->logInAdmin();
        $url = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_homepage');
        //$this->assertStringContainsString('DoctrineORMEntityManager', get_class($em));
        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $urlsc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_symfonycommand');
        $client->request('GET', $urlsc, array('symfonycommand' => 'list'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $urlsc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_symfonycommand');
        $client->request('GET', $urlsc, array('symfonycommand' => 'list --env=test'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $urlsc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_symfonycommand');
        $client->request('GET', $urlsc, array('symfonycommand' => 'lista --env=test'));
        $this->assertEquals(500, $client->getResponse()->getStatusCode());

        $urluc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_unixcommand');
        $client->request('GET', $urluc, array('unixcommand' => 'ls', 'arguments' => '-all'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $urluc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_unixcommand');
        $client->request('GET', $urluc, array('unixcommand' => 'lsssss -all'));
        $this->assertEquals(500, $client->getResponse()->getStatusCode());

        $urluc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_unixcommand');
        $client->request('GET', $urluc, array('unixcommand' => 'lsss', 'arguments' => '-all'));
        $this->assertEquals(500, $client->getResponse()->getStatusCode());

        $client->reload();
        $urlge = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_generateentity');

        $client->request('GET', $urlge, array('file' => 'tabellaminuscola.mwb'));
        $this->assertEquals(500, $client->getResponse()->getStatusCode());

        $client->reload();
        //Restart client per caricare il nuovo bundle
        $client->request('GET', $urlge, array('file' => 'wbadmintest.mwb'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->reload();
        $urlas = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_aggiornaschemadatabase');
        $client->request('GET', $urlas);
        $this->assertTrue($client->getResponse()->isSuccessful());

        $client->reload();
        $urlgf = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_generateformcrud');
        $client->request('GET', $urlgf, array('entityform' => 'Prova'));
        $this->assertTrue($client->getResponse()->isSuccessful());
        $apppath = $client->getContainer()->get('pannelloamministrazione.projectpath');
        $appbundlepath = $apppath->getSrcPath() . DIRECTORY_SEPARATOR;
        $checkentitybaseprova = $appbundlepath . 'Entity' . DIRECTORY_SEPARATOR . 'BaseProva.php';
        $checkentityprova = $appbundlepath . 'Entity' . DIRECTORY_SEPARATOR . 'Prova.php';
        $checktypeprova = $appbundlepath . 'Form' . DIRECTORY_SEPARATOR . 'ProvaType.php';
        $checkviewsprova = $apppath->getSrcPath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Prova';
        $checkindexprova = $apppath->getSrcPath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'Prova' . DIRECTORY_SEPARATOR . 'Crud' . DIRECTORY_SEPARATOR . 'index.html.twig';

        $this->assertTrue(file_exists($checkentitybaseprova));
        $this->assertTrue(file_exists($checkentityprova));
        $this->assertTrue(file_exists($checktypeprova));
        $this->assertTrue(file_exists($checkviewsprova));
        $this->assertTrue(file_exists($checkindexprova));

        /*
          $client->reload();
          $urlcc = $client->getContainer()->get('router')->generate('fi_pannello_amministrazione_clearcache');
          $client->request('GET', $urlcc);
          $client->reload();
          $this->setUp();
          $client = $this->client;
          $client->request('GET', $url);

          $this->assertEquals(200, $client->getResponse()->getStatusCode());
         */

        cleanFilesystem();
        //dump($client->getResponse());
    }

}
