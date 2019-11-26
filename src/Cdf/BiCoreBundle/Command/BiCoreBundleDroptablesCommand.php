<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

class BiCoreBundleDroptablesCommand extends Command
{
    protected static $defaultName = 'bicorebundle:droptables';

    protected function configure()
    {
        $this
                ->setDescription('Eliminazione di tutte le tabelle bicorebundle')
                ->setHelp('ATTENZIONE, questo comando cancellerà tutte le informazioni presenti nel database!!')
                ->addOption('force', null, InputOption::VALUE_NONE, 'Se non impostato, il comando non avrà effetto');
    }

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;
        $driver = $em->getConnection()->getDatabasePlatform()->getName();

        $force = $input->getOption('force');

        if (!$force) {
            $output->writeln("Specificare l'opzione --force per eseguire il comando");

            return 1;
        }

        //Truncate tabelle
        $tables = $em->getConnection()->getSchemaManager()->listTables();
        //Cancellazione tabelle
        foreach ($tables as $table) {
            $tableName = $table->getName();

            switch ($driver) {
                case 'postgresql':
                    $em->getConnection()->executeQuery(sprintf('DROP TABLE %s CASCADE', $tableName));
                    $sequences = $em->getConnection()->getSchemaManager()->listSequences();
                    foreach ($sequences as $sequence) {
                        $sequenceName = $sequence->getName();
                        $em->getConnection()->executeQuery(sprintf('DROP SEQUENCE %s', $sequenceName));
                    }
                    break;
                case 'mysql':
                    $em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=0');
                    $em->getConnection()->executeQuery(sprintf('DROP TABLE %s', $tableName));
                    $em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS=1');
                    break;
                default:
                    //$em->getConnection()->executeQuery(sprintf('DELETE FROM %s', $tableName));
                    $em->getConnection()->executeQuery(sprintf('DROP TABLE %s', $tableName));
                    break;
            }
        }

        $output->writeln('Done!');
        return 0;
    }
}
