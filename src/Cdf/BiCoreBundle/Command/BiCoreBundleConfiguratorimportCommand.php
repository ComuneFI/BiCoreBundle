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

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'bicorebundle:configuratorimport', description: 'Importa configurazione per BiCore')]
class BiCoreBundleConfiguratorimportCommand extends Command
{
    use ConfiguratorimportInsertTrait;
    use ConfiguratorimportUpdateTrait;

    private $forceupdate = false;
    private $verboso = false;
    private $truncatetables;
    private ?\Symfony\Component\Console\Output\OutputInterface $output = null;

    protected function configure(): void
    {
        $this
                ->setHelp('Importa la configurazione di bi da file fixtures.yml')
                ->addOption('forceupdate', null, InputOption::VALUE_NONE, 'Forza update di record con id già presente')
                ->addOption('truncatetables', null, InputOption::VALUE_NONE, 'Esegue una truncate della tabelle')
                ->addOption('verboso', null, InputOption::VALUE_NONE, 'Visualizza tutti i messaggi di importazione');
    }

    public function __construct(private EntityManagerInterface $em, private DatabaseUtils $dbutility, private EntityUtils $entityutility, private BiCoreSystemTablesUtils $systementity)
    {
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->forceupdate = $input->getOption('forceupdate');
        $this->verboso = $input->getOption('verboso');
        $this->truncatetables = $input->getOption('truncatetables');

        $this->checkSchemaStatus();

        $fixturefile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fixtures.yml';

        return (int) $this->import($fixturefile);
    }

    protected function import($fixturefile): int
    {
        $fs = new Filesystem();
        if ($fs->exists($fixturefile)) {
            $fixtures = Yaml::parse(file_get_contents($fixturefile));
            $msg = '<info>Trovate ' . count($fixtures) . ' entities nel file ' . $fixturefile . '</info>';
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
        }
        $msgerr = '<error>Non trovato file ' . $fixturefile . '</error>';
        $this->output->writeln($msgerr);
        return 1;
    }

    private function checkSchemaStatus(): void
    {
        $schemachanged = $this->dbutility->isSchemaChanged();

        if ($schemachanged) {
            $msgerr = "<error>Attenzione, lo schema database non è aggiornato, verrà comunque tentata l'importazione</error>";
            $this->output->writeln($msgerr);
            //sleep(3);
        }
    }

    private function truncateTable($entityclass): void
    {
        $tablename = $this->entityutility->getTableFromEntity($entityclass);
        $msg = '<info>TRUNCATE della tabella ' . $tablename . ' (' . $entityclass . ')</info>';
        $this->output->writeln($msg);
        $this->dbutility->truncatetable($entityclass, true);
    }

    /**
     * @return mixed[]
     */
    private function getSortedEntities(array $fixtures): array
    {
        $entities = [];
        $sortedEntities = $this->systementity->getSystemEntities();
        foreach ($sortedEntities as $fixture => $details) {
            if (isset($fixtures[$fixture])) {
                $entities[$fixture] = $fixtures[$fixture];
            }
        }

        return $entities;
    }

    private function executeImport($entityclass, $fixture): int
    {
        $msg = '<info>Trovati ' . count($fixture) . " record per l'entity " . $entityclass . '</info>';
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

    private function switchInsertUpdate($entityclass, array $record, $objrecord)
    {
        if (!$objrecord) {
            return $this->executeInsert($entityclass, $record);
        }

        if ($this->forceupdate) {
            return $this->executeUpdate($entityclass, $record, $objrecord);
        }
        $msgerr = '<error>' . $entityclass . ' con id ' . $record['id']
                . " non modificata, specificare l'opzione --forceupdate "
                . 'per sovrascrivere record presenti</error>';
        $this->output->writeln($msgerr);
    }
}
