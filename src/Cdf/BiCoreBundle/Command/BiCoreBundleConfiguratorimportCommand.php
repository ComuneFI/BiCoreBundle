<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManagerInterface;
use Cdf\BiCoreBundle\Utils\Database\DatabaseUtils;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;
use Cdf\BiCoreBundle\Utils\Entity\BiCoreSystemTablesUtils;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\Command\ConfiguratorimportInsertTrait;
use Cdf\BiCoreBundle\Utils\Command\ConfiguratorimportUpdateTrait;

class BiCoreBundleConfiguratorimportCommand extends Command
{
    use ConfiguratorimportInsertTrait,
        ConfiguratorimportUpdateTrait;

    protected static $defaultName = 'bicorebundle:configuratorimport';

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
                ->setDescription('Importa configurazione per BiCore')
                ->setHelp('Importa la configurazione di bi da file fixtures.yml')
                ->addOption('forceupdate', null, InputOption::VALUE_NONE, 'Forza update di record con id già presente')
                ->addOption('truncatetables', null, InputOption::VALUE_NONE, 'Esegue una truncate della tabelle')
                ->addOption('verboso', null, InputOption::VALUE_NONE, 'Visualizza tutti i messaggi di importazione');
    }

    public function __construct(EntityManagerInterface $em, DatabaseUtils $dbutil, EntityUtils $entityutil, BiCoreSystemTablesUtils $systementity)
    {
        $this->em = $em;
        $this->dbutility = $dbutil;
        $this->entityutility = $entityutil;
        $this->systementity = $systementity;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->forceupdate = $input->getOption('forceupdate');
        $this->verboso = $input->getOption('verboso');
        $this->truncatetables = $input->getOption('truncatetables');

        $this->checkSchemaStatus();

        $fixturefile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fixtures.yml';
        $ret = $this->import($fixturefile);

        return $ret;
    }

    protected function import($fixturefile)
    {
        $fs = new Filesystem();
        if ($fs->exists($fixturefile)) {
            $fixtures = Yaml::parse(file_get_contents($fixturefile));
            $msg = '<info>Trovate '.count($fixtures).' entities nel file '.$fixturefile.'</info>';
            $this->output->writeln($msg);

            if ($this->truncatetables) {
                foreach ($fixtures as $entityclass => $fixture) {
                    $this->truncateTable($entityclass);
                }
            }
            $sortedEntities = $this->getSortedEntities($fixtures);
            foreach ($sortedEntities as $entityclass => $fixture) {
                $ret = $this->executeImport($entityclass, $fixture);
                if (1 == $ret) {
                    return 1;
                }
            }

            return 0;
        } else {
            $msgerr = '<error>Non trovato file '.$fixturefile.'</error>';
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
        $msg = '<info>TRUNCATE della tabella '.$tablename.' ('.$entityclass.')</info>';
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
        $msg = '<info>Trovati '.count($fixture)." record per l'entity ".$entityclass.'</info>';
        $this->output->writeln($msg);
        foreach ($fixture as $record) {
            $objrecord = $this->em->getRepository($entityclass)->find($record['id']);
            $ret = $this->switchInsertUpdate($entityclass, $record, $objrecord);
            if (0 !== $ret) {
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
            $msgerr = '<error>'.$entityclass.' con id '.$record['id']
                    ." non modificata, specificare l'opzione --forceupdate "
                    .'per sovrascrivere record presenti</error>';
            $this->output->writeln($msgerr);
        }
    }
}
