<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BiCoreBundlePubblicamanualeCommand extends Command
{

    protected static $defaultName = 'bicorebundle:pubblicamanuale';
    
    protected function configure()
    {
        $this
                ->setDescription('Copia il manuale dalla cartella Doc alla cartella Web')
                ->setHelp('Estende la pubblicazione degli assets al manuale');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->copiaManuale();
    }

    protected function copiaManuale()
    {
        $filesystem = $this->getContainer()->get('filesystem');

        $projectDir = $this->getContainer()->get('kernel')->getProjectDir() .DIRECTORY_SEPARATOR . '..' ;
        $originDir = $projectDir . '/doc/manuale';
        $targetDir = $projectDir . '/public';

        $filesystem->mkdir($targetDir, 0777);
        //    // We use a custom iterator to ignore VCS files
        $filesystem->mirror($originDir, $targetDir, Finder::create()->name('manuale.pdf')->in($originDir));
    }
}
