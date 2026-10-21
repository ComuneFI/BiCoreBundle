<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'bicorebundle:dropdatabase', description: 'Cancellazione database bicorebundle')]
class BiCoreBundleDropdatabaseCommand extends Command
{
    protected function configure(): void
    {
        $this
                ->setHelp('Cancella il database e tutti i dati di bicorebundle')
                ->addOption('force', null, InputOption::VALUE_NONE, 'Se non impostato, il comando non avrà effetto');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');

        if (!$force) {
            echo "Specificare l'opzione --force per eseguire il comando";

            return \Symfony\Component\Console\Command\Command::FAILURE;
        }

        /*$command = $this->getApplication()->find('doctrine:database:drop');
        $arguments = array('command' => 'doctrine:database:drop', '--force' => true, '--if-exists' => true);
        $inputcmd = new ArrayInput($arguments);
        $command->run($inputcmd, $output);*/

        $command = $this->getApplication()->find('doctrine:schema:drop');
        $arguments = ['command' => 'doctrine:schema:drop', '--force' => true, '--full-database' => true];
        $inputcmd = new ArrayInput($arguments);
        $command->run($inputcmd, $output);
        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
