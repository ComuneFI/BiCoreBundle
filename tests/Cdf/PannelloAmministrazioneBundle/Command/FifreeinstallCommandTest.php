<?php

namespace Cdf\PannelloAmministrazioneBundle\Tests\Commands;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FifreeinstallCommandTest extends WebTestCase
{

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testFifreeinstall()
    {
        $kernel = static::$kernel;
        $application = new Application($kernel);
        $cominstall = static::$kernel->getContainer()->get('cdf.bicorebundle.install');
        $comdroptables = static::$kernel->getContainer()->get('cdf.bicorebundle.droptables');

        $application->add($cominstall);
        $application->add(new \Cdf\BiCoreBundle\Command\BiCoreBundleDropdatabaseCommand());
        $application->add($comdroptables);

        $commandimport = $application->find('bicorebundle:droptables');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array('--force' => true));
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        //$this->assertContains('Cosa cercare', $outputimport);
        /**/
        $commandimport = $application->find('bicorebundle:dropdatabase');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array('--force' => true));
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        //$this->assertContains('Cosa cercare', $outputimport);
        /**/
        $commandimport = $application->find('bicorebundle:install');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array('admin' => "admin",'adminpass' => "admin",'adminemail' => "admin@admin.it"));
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        //$this->assertContains('Cosa cercare', $outputimport);
        $commandimport = $application->find('bicoredemo:loaddefauldata');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array());
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        $this->assertContains('Done', $outputimport);
    }
}
