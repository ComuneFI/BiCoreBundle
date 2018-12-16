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
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testPubblicamanuale()
    {
        $kernel = static::$kernel;
        $application = new Application($kernel);

        $this->expectException(\Exception::class);
        //Test per fallimento import se non è stato fatto un export
        $commandpubblicamanuale = $application->find('bicorebundle:pubblicamanuale');
        $commandTesterImport = new CommandTester($commandpubblicamanuale);
        $commandTesterImport->execute(array());
        $outputimport = $commandTesterImport->getDisplay();

    }
}
