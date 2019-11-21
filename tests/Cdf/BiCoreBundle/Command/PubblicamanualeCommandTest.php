<?php

namespace Cdf\BiCoreBundle\Tests\Commands;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PubblicamanualeCommandTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }
    public function testPubblicamanuale()
    {
        $kernel = static::$kernel;
        $application = new Application($kernel);

        //Test per fallimento import se non è stato fatto un export
        $commandpubblicamanuale = $application->find('bicorebundle:pubblicamanuale');
        $commandTesterPubblicamanuale = new CommandTester($commandpubblicamanuale);
        $commandTesterPubblicamanuale->execute(array());
        $outputpubblicamanuale = $commandTesterPubblicamanuale->getDisplay();
        $this->assertRegExp('/.../', $outputpubblicamanuale);

        $apppath = $kernel->getContainer()->get('pannelloamministrazione.projectpath');
        $filename = $apppath->getPublicPath() . DIRECTORY_SEPARATOR . "manuale.pdf";
        $this->assertFileExists($filename);
        $this->assertFileExists($apppath->getVarPath());
        unlink($filename);
    }
}
