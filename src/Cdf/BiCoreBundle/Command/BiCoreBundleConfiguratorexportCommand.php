<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Cdf\BiCoreBundle\Utils\Entity\BiCoreSystemTablesUtils;
use Cdf\BiCoreBundle\Utils\Entity\EntityUtils;

class BiCoreBundleConfiguratorexportCommand extends Command
{
    protected static $defaultName = 'bicorebundle:configuratorexport';

    private $entities = array();
    private $em;
    private $systementity;
    private $output;

    protected function configure()
    {
        $this
                ->setDescription('Esporta configurazione per BiCore')
                ->setHelp('Esporta la configurazione di bi');
    }

    public function __construct(EntityManagerInterface $em, EntityUtils $entityutility, BiCoreSystemTablesUtils $systementity)
    {
        $this->em = $em;
        $this->entityutility = $entityutility;
        $this->systementity = $systementity;

        // you *must* call the parent constructor
        parent::__construct();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $this->output = $output;

        try {
            //$fixturefile = $this->getContainer()->get('kernel')->locateResource('@BiCoreBundle/Resources/config/fixtures.yml');
            $fixturefile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fixtures.yml';
            $fs->remove($fixturefile);
            $systementities = $this->systementity->getSystemEntities();
            foreach ($systementities as $entity => $details) {
                $ret = $this->export($fixturefile, $entity);
                if (1 == $ret) {
                    return 1;
                }
            }

            return 0;
        } catch (\Exception $exc) {
            echo $exc->getMessage().' at line '.$exc->getLine();
        }
        return 0;
    }

    protected function export($fixturefile, $entity)
    {
        $entityclass = $entity;
        $ret = $this->exportEntity($fixturefile, $entityclass);
        if (0 == $ret) {
            foreach ($this->entities as $entity) {
                $this->output->writeln('<info>Esporto '.$entity.' su file</info>');
                $this->exportEntityToFile($fixturefile, $entity);
            }
            $this->exportEntityToFile($fixturefile, $entityclass);

            return 0;
        }

        return 1;
    }

    private function exportEntity($fixturefile, $entityclass)
    {
        $this->output->writeln('<info>Export Entity: '.$entityclass.'</info>');
        if ($this->entityutility->entityExists($entityclass)) {
            /* $hasEntityCollegate = $entityutility->entityHasJoinTables($entityclass);
              if ($hasEntityCollegate) {
              $this->output->writeln("<info>Entity " . $entityclass . " ha tabelle in join</info>");
              $entityCollegata = $entityutility->getEntityJoinTables($entityclass);
              foreach ($entityCollegata as $key => $tabella) {
              $this->entities[] = $key;
              $this->output->writeln("<info>Prima esporto " . $key . " -> " . $tabella["entity"]["fieldName"] . "</info>");
              $this->exportEntity($fixturefile, $key);
              }
              } */
        } else {
            $this->output->writeln('<error>Entity not found: '.$entityclass.' </error>');

            return 1;
        }

        return 0;
    }

    private function exportEntityToFile($fixturefile, $entityclass)
    {
        $entityDump = array();

        $query = $this->em->createQueryBuilder()
                ->select('p')
                ->from($entityclass, 'p')
                ->orderBy('p.id', 'asc')
                ->getQuery()
        ;
        $repo = $query->getArrayResult();

        //$repo = $this->em->getRepository($entityclass)->findAll();
        $this->output->writeln('<info>Trovate '.count($repo)." records per l'entity ".$entityclass.'</info>');
        foreach ($repo as $row) {
            $entityDump[$entityclass][] = $row;
        }
        if (count($entityDump) > 0) {
            $yml = Yaml::dump($entityDump);
            file_put_contents($fixturefile, $yml, FILE_APPEND);
        }
    }
}
