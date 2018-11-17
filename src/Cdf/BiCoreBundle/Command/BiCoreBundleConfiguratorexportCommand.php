<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;

class BiCoreBundleConfiguratorexportCommand extends ContainerAwareCommand
{

    private $entities = array();
    private $em;
    private $systementity;
    private $output;

    protected function configure()
    {
        $this
                ->setName('bicorebundle:configuratorexport')
                ->setDescription('Configuratore per Fifree')
                ->setHelp('Esporta la configurazione di bi');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem;
        $this->em = $this->getContainer()->get("doctrine")->getManager();
        $this->systementity = $this->getContainer()->get("cdf.bicorebundle.utility.entity.system");
        $this->output = $output;

        try {
            //$fixturefile = $this->getContainer()->get('kernel')->locateResource('@BiCoreBundle/Resources/config/fixtures.yml');
            $fixturefile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "fixtures.yml";
            $fs->remove($fixturefile);
            $systementities = $this->systementity->getSystemEntities();
            foreach ($systementities as $entity => $details) {
                $ret = $this->export($fixturefile, $entity);
                if ($ret == 1) {
                    return 1;
                }
            }
            return 0;
        } catch (\Exception $exc) {
            echo $exc->getMessage() . " at line " . $exc->getLine();
        }
    }

    protected function export($fixturefile, $entity)
    {
        $entityclass = $entity;
        $ret = $this->exportEntity($fixturefile, $entityclass);
        if ($ret == 0) {
            foreach ($this->entities as $entity) {
                $this->output->writeln("<info>Esporto " . $entity . " su file</info>");
                $this->exportEntityToFile($fixturefile, $entity);
            }
            $this->exportEntityToFile($fixturefile, $entityclass);
            return 0;
        }
        return 1;
    }

    private function exportEntity($fixturefile, $entityclass)
    {
        $entityutility = $this->getContainer()->get("cdf.bicorebundle.utility.entity");
        $this->output->writeln("<info>Export Entity: " . $entityclass . "</info>");
        if ($entityutility->entityExists($entityclass)) {
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
            $this->output->writeln("<error>Entity not found: " . $entityclass . " </error>");
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
                ->orderBy("p.id", "asc")
                ->getQuery()
        ;
        $repo = $query->getArrayResult();


        //$repo = $this->em->getRepository($entityclass)->findAll();
        $this->output->writeln("<info>Trovate " . count($repo) . " records per l'entity " . $entityclass . "</info>");
        foreach ($repo as $row) {
            $entityDump[$entityclass][] = $row;
        }
        if (count($entityDump) > 0) {
            $yml = Yaml::dump($entityDump);
            file_put_contents($fixturefile, $yml, FILE_APPEND);
        }
    }
}
