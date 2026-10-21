<?php

declare(strict_types=1);

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;

/**
 * @codeCoverageIgnore
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pannelloamministrazione:checksrc', description: 'Controlla i sorgenti')]
class ChecksrcCommand extends Command
{
    protected function configure() : void
    {
        $this
                ->setHelp('Usa phpcs, phpmd, ecc per controllare il codice in src');
    }

    public function __construct(private readonly ProjectPath $apppaths)
    {
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
        $phpcscmd = [$vendorBin.'phpcs', '--standard='.$rootPath.'/../tools/phpcs/ruleset.xml', '--extensions=php', $srcPath];
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
        $phpmdcmd = [$vendorBin.'phpmd', $srcPath, 'text', $rootPath.'/../tools/phpmd/ruleset.xml'];
        $phpmdoutput = $this->runcmd($phpmdcmd);
        if (!$phpmdoutput) {
            $output->writeln('phpmd: OK');
        } else {
            $output->writeln($phpmdoutput);
        }
        /* phpmd */
        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }

    /**
     *
     * @param array<string> $cmd
     */
    private function runcmd(array $cmd) : string
    {
        $process = new Process($cmd);
        $process->setTimeout(60 * 100);
        $process->run();
        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        return $process->getErrorOutput() ?: $process->getOutput();
    }
}
