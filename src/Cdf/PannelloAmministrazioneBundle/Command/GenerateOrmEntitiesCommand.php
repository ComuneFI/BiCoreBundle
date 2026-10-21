<?php

namespace Cdf\PannelloAmministrazioneBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Fi\OsBundle\DependencyInjection\OsFunctions;
use Cdf\PannelloAmministrazioneBundle\Utils\ProjectPath;
use Cdf\PannelloAmministrazioneBundle\Utils\GeneratorHelper;
use Cdf\PannelloAmministrazioneBundle\Utils\Utility;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pannelloamministrazione:generateormentities', description: 'Genera le entities partendo da un modello workbeanch mwb')]
class GenerateOrmEntitiesCommand extends Command
{
    protected function configure() : void
    {
        $this
                ->setHelp('Genera i file orm per le entities partendo da un modello workbeanch mwb, <br/>bi.mwb Fi/BiCoreBundle default<br/>')
                ->addArgument('mwbfile', InputArgument::REQUIRED, 'Nome file mwb, bi.mwb')
        ;
    }

    public function __construct(protected ProjectPath $apppaths, protected GeneratorHelper $genhelper, protected Utility $pammutils)
    {
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        set_time_limit(0);

        $mwbfile = $input->getArgument('mwbfile');

        $wbFile = $this->apppaths->getDocPath().DIRECTORY_SEPARATOR.$mwbfile;
        $checkprerequisiti = $this->genhelper->checkprerequisiti($mwbfile, $output);

        if ($checkprerequisiti < 0) {
            return -1;
        }

        $destinationPath = $this->genhelper->getDestinationEntityOrmPath();

        $exportJson = $this->genhelper->getExportJsonFile();
        $scriptGenerator = $this->genhelper->getScriptGenerator();
        $destinationPathEscaped = $this->genhelper->getDestinationEntityOrmPath();
        $exportjsonfile = $this->genhelper->getJsonMwbGenerator();

        $exportjsonreplaced = str_replace('[dir]', $destinationPathEscaped, $exportjsonfile);

        file_put_contents($exportJson, $exportjsonreplaced);
        $workingdir = $this->apppaths->getRootPath();
        if (OsFunctions::isWindows()) {
            $command = $scriptGenerator.'.bat'.' --config='.$exportJson.' '.$wbFile.' '.$destinationPathEscaped;
        } else {
            $command = $scriptGenerator.' --config='.$exportJson.' '.$wbFile.' '.$destinationPathEscaped;
        }

        $schemaupdateresult = $this->pammutils->runCommand($command, $workingdir);
        if ($schemaupdateresult['errcode'] < 0) {
            $output->writeln($schemaupdateresult['message']);

            return \Symfony\Component\Console\Command\Command::FAILURE;
        }
        $output->writeln($schemaupdateresult['message']);

        $this->genhelper->removeExportJsonFile();
        $tablecheck = $this->genhelper->checktables($destinationPath, $wbFile, $output);

        if ($tablecheck < 0) {
            return \Symfony\Component\Console\Command\Command::FAILURE;
        }

        $output->writeln('<info>Entities yml create</info>');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
