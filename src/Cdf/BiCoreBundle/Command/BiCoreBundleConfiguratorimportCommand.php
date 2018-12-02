<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

class BiCoreBundleConfiguratorimportCommand extends ContainerAwareCommand
{

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

    private function executeInsert($entityclass, $record)
    {
        $objrecord = new $entityclass();
        foreach ($record as $key => $value) {
            if ($key !== 'id') {
                $propertyEntity = $this->entityutility->getEntityProperties($key, $objrecord);
                $getfieldname = $propertyEntity["get"];
                $setfieldname = $propertyEntity["set"];
                if ($key=="discr") {
                    continue;
                }
                $fieldtype = $this->dbutility->getFieldType($objrecord, $key);
                if ($fieldtype === 'boolean') {
                    $newval = FieldTypeUtils::getBooleanValue($value);
                    $msgok = "<info>Inserimento " . $entityclass . " con id " . $record["id"]
                            . " per campo " . $key . " con valore "
                            . var_export($newval, true) . " in formato Boolean</info>";
                    $this->output->writeln($msgok);
                    $objrecord->$setfieldname($newval);
                    continue;
                }
                //Si prende in considerazione solo il null del boolean, gli altri non si toccano
                if (!$value) {
                    continue;
                }
                if ($fieldtype === 'datetime' || $fieldtype === 'date') {
                    $date = FieldTypeUtils::getDateTimeValueFromTimestamp($value);
                    $msgok = "<info>Inserimento " . $entityclass . " con id " . $record["id"]
                            . " per campo " . $key . " cambio valore da "
                            . ($objrecord->$getfieldname() ? $objrecord->$getfieldname()->format("Y-m-d H:i:s") : "NULL")
                            . " a " . $date->format("Y-m-d H:i:s") . " in formato DateTime</info>";
                    $this->output->writeln($msgok);
                    $objrecord->$setfieldname($date);
                    continue;
                }
                if (is_array($value)) {
                    $msgarray = "<info>Inserimento " . $entityclass . " con id " . $record["id"]
                            . " per campo " . $key . " cambio valore da "
                            . json_encode($objrecord->$getfieldname()) . " a "
                            . json_encode($value) . " in formato array" . "</info>";
                    $this->output->writeln($msgarray);
                    $objrecord->$setfieldname($value);
                    continue;
                }

                $joincolumn = $this->entityutility->getJoinTableField($entityclass, $key);
                $joincolumnproperty = $this->entityutility->getJoinTableFieldProperty($entityclass, $key);
                if ($joincolumn && $joincolumnproperty) {
                    $joincolumnobj = $this->em->getRepository($joincolumn)->find($value);
                    $msgok = "<info>Inserimento " . $entityclass . " con id " . $record["id"]
                            . " per campo " . $key
                            . " con valore " . print_r($value, true) . " tramite entity find</info>";
                    $this->output->writeln($msgok);
                    $joinobj = $this->entityutility->getEntityProperties($joincolumnproperty, new $entityclass());
                    $setfieldname = $joinobj["set"];
                    $objrecord->$setfieldname($joincolumnobj);
                    continue;
                }
                $objrecord->$setfieldname($value);
            }
        }
        $this->em->persist($objrecord);
        $this->em->flush();

        $infomsg = "<info>" . $entityclass . " con id " . $objrecord->getId() . " aggiunta</info>";
        $this->output->writeln($infomsg);
        $checkid = $this->changeRecordId($entityclass, $record, $objrecord);
        return $checkid;
    }

    private function changeRecordId($entityclass, $record, $objrecord)
    {
        if ($record["id"] !== $objrecord->getId()) {
            try {
                $qb = $this->em->createQueryBuilder();
                $q = $qb->update($entityclass, 'u')
                        ->set('u.id', ":newid")
                        ->where('u.id = :oldid')
                        ->setParameter("newid", $record["id"])
                        ->setParameter("oldid", $objrecord->getId())
                        ->getQuery();
                $q->execute();
                $msgok = "<info>" . $entityclass . " con id " . $objrecord->getId() . " sistemata</info>";
                $this->output->writeln($msgok);
            } catch (\Exception $exc) {
                echo $exc->getMessage();
                return 1;
            }
            $this->em->flush();
        }
        return 0;
    }

    private function executeUpdate($entityclass, $record, $objrecord)
    {
        unset($record["id"]);
        foreach ($record as $key => $value) {
            if ($key=="discr") {
                continue;
            }
            $propertyEntity = $this->entityutility->getEntityProperties($key, $objrecord);
            $getfieldname = $propertyEntity["get"];
            $setfieldname = $propertyEntity["set"];
            $cambiato = $this->dbutility->isRecordChanged($entityclass, $key, $objrecord->$getfieldname(), $value);
            if (!$cambiato) {
                if ($this->verboso) {
                    $msginfo = "<info>" . $entityclass . " con id " . $objrecord->getId()
                            . " per campo " . $key . " non modificato perchè già "
                            . $value . "</info>";
                    $this->output->writeln($msginfo);
                }
            } else {
                try {
                    $fieldtype = $this->dbutility->getFieldType($objrecord, $key);
                    if ($fieldtype === 'boolean') {
                        $newval = FieldTypeUtils::getBooleanValue($value);

                        $msgok = "<info>Modifica " . $entityclass . " con id " . $objrecord->getId()
                                . " per campo " . $key . " cambio valore da "
                                . var_export($objrecord->$getfieldname(), true)
                                . " a " . var_export($newval, true) . " in formato Boolean</info>";
                        $this->output->writeln($msgok);
                        $objrecord->$setfieldname($newval);
                        continue;
                    }
                    //Si prende in considerazione solo il null del boolean, gli altri non si toccano
                    if (!$value) {
                        continue;
                    }

                    if ($fieldtype === 'datetime' || $fieldtype === 'date') {
                        $date = FieldTypeUtils::getDateTimeValueFromTimestamp($value);
                        $msgok = "<info>Modifica " . $entityclass . " con id " . $objrecord->getId()
                                . " per campo " . $key . " cambio valore da "
                                //. (!is_null($objrecord->$getfieldname())) ? $objrecord->$getfieldname()->format("Y-m-d H:i:s") : "(null)"
                                . ($objrecord->$getfieldname() ? $objrecord->$getfieldname()->format("Y-m-d H:i:s") : "NULL")
                                . " a " . $date->format("Y-m-d H:i:s") . " in formato DateTime</info>";
                        $this->output->writeln($msgok);
                        $objrecord->$setfieldname($date);
                        continue;
                    }
                    if (is_array($value)) {
                        $msgarray = "<info>Modifica " . $entityclass . " con id " . $objrecord->getId()
                                . " per campo " . $key . " cambio valore da "
                                . json_encode($objrecord->$getfieldname()) . " a "
                                . json_encode($value) . " in formato array" . "</info>";
                        $this->output->writeln($msgarray);
                        $objrecord->$setfieldname($value);
                        continue;
                    }

                    $joincolumn = $this->entityutility->getJoinTableField($entityclass, $key);
                    $joincolumnproperty = $this->entityutility->getJoinTableFieldProperty($entityclass, $key);
                    if ($joincolumn && $joincolumnproperty) {
                        $joincolumnobj = $this->em->getRepository($joincolumn)->find($value);
                        $msgok = "<info>Modifica " . $entityclass . " con id " . $objrecord->getId()
                                . " per campo " . $key . " cambio valore da " . print_r($objrecord->$getfieldname(), true)
                                . " a " . print_r($value, true) . " tramite entity find</info>";
                        $this->output->writeln($msgok);
                        $joinobj = $this->entityutility->getEntityProperties($joincolumnproperty, new $entityclass());
                        $setfieldname = $joinobj["set"];
                        $objrecord->$setfieldname($joincolumnobj);
                        continue;
                    }

                    $msgok = "<info>Modifica " . $entityclass . " con id " . $objrecord->getId()
                            . " per campo " . $key . " cambio valore da " . print_r($objrecord->$getfieldname(), true)
                            . " a " . print_r($value, true) . "</info>";
                    $this->output->writeln($msgok);
                    $objrecord->$setfieldname($value);
                } catch (\Exception $exc) {
                    $msgerr = "<error>Modifica " . $entityclass . " con id " . $objrecord->getId()
                            . " per campo " . $key . ", ERRORE: " . $exc->getMessage()
                            . " alla riga " . $exc->getLine() . "</error>";
                    $this->output->writeln($msgerr);
                    //dump($exc);
                    return 1;
                }
            }
        }
        $this->em->persist($objrecord);
        $this->em->flush();

        return 0;
    }
}
