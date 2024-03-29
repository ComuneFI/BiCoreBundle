<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;

/**
 * @codeCoverageIgnore
 */
class ChecksrcCommand extends Command
{
    protected static $defaultName = 'pannelloamministrazione:checksrc';
    
    private ProjectPath $apppaths;
    
    protected function configure() : void
    {
        $this
                ->setDescription('Controlla i sorgenti')
                ->setHelp('Usa phpcs, phpmd, ecc per controllare il codice in src');
    }

    public function __construct(ProjectPath $projectpath)
    {
        $this->apppaths = $projectpath;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $prjpath = $this->apppaths;
        $vendorBin = $prjpath->getVendorBinPath().'/';
        $srcPath = $prjpath->getSrcPath();
        $rootPath = $prjpath->getRootPath();

        /* phpcs */
        $phpcscmd = array($vendorBin.'phpcs', '--standard='.$rootPath.'/../tools/phpcs/ruleset.xml', '--extensions=php', $srcPath);
        $phpcsoutput = $this->runcmd($phpcscmd);
        if (!$phpcsoutput) {
            $output->writeln('phpcs: OK');
        } else {
            $output->writeln($phpcsoutput);
            $output->writeln('Per correggere automaticamente un file eseguire:');
            $output->writeln($vendorBin.'phpcbf --standard=PSR2 nomefile.php');
        }
        /* phpcs */

        /* phpmd */
        $phpmdcmd = array($vendorBin.'phpmd', $srcPath, 'text', $rootPath.'/../tools/phpmd/ruleset.xml');
        $phpmdoutput = $this->runcmd($phpmdcmd);
        if (!$phpmdoutput) {
            $output->writeln('phpmd: OK');
        } else {
            $output->writeln($phpmdoutput);
        }
        /* phpmd */
        return 0;
    }

    /**
     *
     * @param array<string> $cmd
     * @return string
     */
    private function runcmd(array $cmd) : string
    {
        $process = new Process($cmd);
        $process->setTimeout(60 * 100);
        $process->run();
        if ($process->isSuccessful()) {
            $out = $process->getOutput();
        } else {
            $out = ($process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput());
        }

        return $out;
    }
}
