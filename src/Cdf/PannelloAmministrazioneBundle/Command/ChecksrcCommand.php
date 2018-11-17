<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ChecksrcCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('pannelloamministrazione:checksrc')
                ->setDescription('Controlla i sorgenti')
                ->setHelp('Usa phpcs, phpmd, ecc per controllare il codice in src');
    }

    // @codeCoverageIgnoreStart
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $prjpath = $container->get("pannelloamministrazione.projectpath");
        $vendorBin = $prjpath->getVendorBinPath() . "/";
        $srcPath = $prjpath->getSrcPath();
        $rootPath = $prjpath->getRootPath();

        /* phpcpd */
        $phpcpdcmd = $vendorBin . "phpcpd " . $srcPath;
        $phpcpdoutput = $this->runcmd($phpcpdcmd);
        if (!$phpcpdoutput) {
            $output->writeln("phpmd: OK");
        } else {
            if (strpos($phpcpdoutput, "0.00%")) {
                $output->writeln("phpmd: OK");
            } else {
                $output->writeln($phpcpdoutput);
            }
        }
        /* phpcpd */

        /* phpcs */
        $phpcscmd = $vendorBin . "phpcs --standard=" . $rootPath . "/tools/phpcs/ruleset.xml  --extensions=php " . $srcPath;
        $phpcsoutput = $this->runcmd($phpcscmd);
        if (!$phpcsoutput) {
            $output->writeln("phpcs: OK");
        } else {
            $output->writeln($phpcsoutput);
            $output->writeln("Per correggere automaticamente un file eseguire:");
            $output->writeln($vendorBin . "phpcbf --standard=PSR2 nomefile.php");
        }
        /* phpcs */

        /* phpmd */
        $phpmdcmd = $vendorBin . "phpmd " . $srcPath . " text " . $rootPath . "/tools/phpmd/ruleset.xml";
        $phpmdoutput = $this->runcmd($phpmdcmd);
        if (!$phpmdoutput) {
            $output->writeln("phpmd: OK");
        } else {
            $output->writeln($phpmdoutput);
        }
        /* phpmd */
    }

    private function runcmd($cmd)
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
    // @codeCoverageIgnoreEnd
}
