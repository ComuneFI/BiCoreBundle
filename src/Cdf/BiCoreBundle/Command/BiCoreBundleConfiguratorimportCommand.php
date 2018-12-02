<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\Command\ConfiguratorimportInsertTrait;
use Cdf\BiCoreBundle\Utils\Command\ConfiguratorimportUpdateTrait;

class BiCoreBundleConfiguratorimportCommand extends ContainerAwareCommand
{

    use ConfiguratorimportInsertTrait,
        ConfiguratorimportUpdateTrait;

    private $forceupdate = false;
    private $verboso = false;
    private $dbutility;
    private $systementity;
    private $entityutility;
    private $em;
    private $truncatetables;
    private $output;

    protected function configure()
    {
        $this
                ->setName('bicorebundle:configuratorimport')
                ->setDescription('Configuratore per Fifree')
                ->setHelp('Importa la configurazione di bi da file fixtures.yml')
                ->addOption('forceupdate', null, InputOption::VALUE_NONE, 'Forza update di record con id già presente')
                ->addOption('truncatetables', null, InputOption::VALUE_NONE, 'Esegue una truncate della tabelle')
                ->addOption('verboso', null, InputOption::VALUE_NONE, 'Visualizza tutti i messaggi di importazione');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->forceupdate = $input->getOption('forceupdate');
        $this->verboso = $input->getOption('verboso');
        $this->truncatetables = $input->getOption('truncatetables');
        $this->dbutility = $this->getContainer()->get("cdf.bicorebundle.utility.database");
        $this->entityutility = $this->getContainer()->get("cdf.bicorebundle.utility.entity");
        $this->systementity = $this->getContainer()->get("cdf.bicorebundle.utility.entity.system");
        $this->em = $this->getContainer()->get("doctrine")->getManager();

        $this->checkSchemaStatus();

        $fixturefile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "fixtures.yml";
        $ret = $this->import($fixturefile);
        return $ret;
    }
    protected function import($fixturefile)
    {
        $fs = new Filesystem;
        if ($fs->exists($fixturefile)) {
            $fixtures = Yaml::parse(file_get_contents($fixturefile));
            $msg = "<info>Trovate " . count($fixtures) . " entities nel file " . $fixturefile . "</info>";
            $this->output->writeln($msg);

            if ($this->truncatetables) {
                foreach ($fixtures as $entityclass => $fixture) {
                    $this->truncateTable($entityclass);
                }
            }
            $sortedEntities = $this->getSortedEntities($fixtures);
            foreach ($sortedEntities as $entityclass => $fixture) {
                $ret = $this->executeImport($entityclass, $fixture);
                if ($ret == 1) {
                    return 1;
                }
            }
            return 0;
        } else {
            $msgerr = "<error>Non trovato file " . $fixturefile . "</error>";
            $this->output->writeln($msgerr);
            return 1;
        }
    }
    private function checkSchemaStatus()
    {
        $schemachanged = $this->dbutility->isSchemaChanged();

        if ($schemachanged) {
            $msgerr = "<error>Attenzione, lo schema database non è aggiornato, verrà comunque tentata l'importazione</error>";
            $this->output->writeln($msgerr);
            //sleep(3);
        }
    }
    private function truncateTable($entityclass)
    {
        $tablename = $this->entityutility->getTableFromEntity($entityclass);
        $msg = "<info>TRUNCATE della tabella " . $tablename . " (" . $entityclass . ")</info>";
        $this->output->writeln($msg);
        $this->dbutility->truncatetable($entityclass, true);
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getSortedEntities($fixtures)
    {
        $entities = array();
        $sortedEntities = $this->systementity->getSystemEntities();
        foreach ($sortedEntities as $fixture => $details) {
            if (isset($fixtures[$fixture])) {
                $entities[$fixture] = $fixtures[$fixture];
            }
        }
        return $entities;
    }
    private function executeImport($entityclass, $fixture)
    {
        $msg = "<info>Trovati " . count($fixture) . " record per l'entity " . $entityclass . "</info>";
        $this->output->writeln($msg);
        foreach ($fixture as $record) {
            $objrecord = $this->em->getRepository($entityclass)->find($record["id"]);
            $ret = $this->switchInsertUpdate($entityclass, $record, $objrecord);
            if ($ret !== 0) {
                return 1;
            }
        }
        return 0;
    }
    private function switchInsertUpdate($entityclass, $record, $objrecord)
    {
        if (!$objrecord) {
            return $this->executeInsert($entityclass, $record);
        }

        if ($this->forceupdate) {
            return $this->executeUpdate($entityclass, $record, $objrecord);
        } else {
            $msgerr = "<error>" . $entityclass . " con id " . $record["id"]
                    . " non modificata, specificare l'opzione --forceupdate "
                    . "per sovrascrivere record presenti</error>";
            $this->output->writeln($msgerr);
        }
    }
}
