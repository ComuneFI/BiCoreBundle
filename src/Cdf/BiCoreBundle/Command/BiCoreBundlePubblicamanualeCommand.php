<?php

namespace Cdf\BiCoreBundle\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
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

    public function __construct($projectdir, Filesystem $fs)
    {
        $this->projectdir = dirname($projectdir);
        $this->fs = $fs;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDir = $this->projectdir;
        $manuale = 'manuale.pdf';
        $originDir = $projectDir.DIRECTORY_SEPARATOR.'doc'.DIRECTORY_SEPARATOR.'manuale';
        $manualepath = $originDir.DIRECTORY_SEPARATOR.$manuale;
        if ($this->fs->exists($manualepath)) {
            $targetDir = $projectDir.DIRECTORY_SEPARATOR.'public';

            $this->fs->mkdir($targetDir, 0777);
            //    // We use a custom iterator to ignore VCS files
            $this->fs->mirror($originDir, $targetDir, Finder::create()->name($manuale)->in($originDir));
        } else {
            throw new Exception('Attenzione, non è presente il file '.$manualepath);
        }
        $output->writeln('Done!');
        return 0;
    }
}
