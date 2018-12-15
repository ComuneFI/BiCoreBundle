<?php

namespace Cdf\BiCoreBundle\Utils\Command;

use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

trait ConfiguratorimportUpdateTrait
{
    private function executeUpdate($entityclass, $record, $objrecord)
    {
        unset($record['id']);
        foreach ($record as $key => $value) {
            if ('discr' == $key) {
                continue;
            }
            $propertyEntity = $this->entityutility->getEntityProperties($key, $objrecord);
            $getfieldname = $propertyEntity['get'];
            $setfieldname = $propertyEntity['set'];
            $cambiato = $this->dbutility->isRecordChanged($entityclass, $key, $objrecord->$getfieldname(), $value);
            if (!$cambiato) {
                if ($this->verboso) {
                    $msginfo = '<info>'.$entityclass.' con id '.$objrecord->getId()
                            .' per campo '.$key.' non modificato perchè già '
                            .$value.'</info>';
                    $this->output->writeln($msginfo);
                }
            } else {
                try {
                    $fieldtype = $this->dbutility->getFieldType($objrecord, $key);
                    if ('boolean' === $fieldtype) {
                        $newval = FieldTypeUtils::getBooleanValue($value);

                        $msgok = '<info>Modifica '.$entityclass.' con id '.$objrecord->getId()
                                .' per campo '.$key.' cambio valore da '
                                .var_export($objrecord->$getfieldname(), true)
                                .' a '.var_export($newval, true).' in formato Boolean</info>';
                        $this->output->writeln($msgok);
                        $objrecord->$setfieldname($newval);
                        continue;
                    }
                    //Si prende in considerazione solo il null del boolean, gli altri non si toccano
                    if (!$value) {
                        continue;
                    }

                    if ('datetime' === $fieldtype || 'date' === $fieldtype) {
                        $date = FieldTypeUtils::getDateTimeValueFromTimestamp($value);
                        $msgok = '<info>Modifica '.$entityclass.' con id '.$objrecord->getId()
                                .' per campo '.$key.' cambio valore da '
                                //. (!is_null($objrecord->$getfieldname())) ? $objrecord->$getfieldname()->format("Y-m-d H:i:s") : "(null)"
                                .($objrecord->$getfieldname() ? $objrecord->$getfieldname()->format('Y-m-d H:i:s') : 'NULL')
                                .' a '.$date->format('Y-m-d H:i:s').' in formato DateTime</info>';
                        $this->output->writeln($msgok);
                        $objrecord->$setfieldname($date);
                        continue;
                    }
                    if (is_array($value)) {
                        $msgarray = '<info>Modifica '.$entityclass.' con id '.$objrecord->getId()
                                .' per campo '.$key.' cambio valore da '
                                .json_encode($objrecord->$getfieldname()).' a '
                                .json_encode($value).' in formato array'.'</info>';
                        $this->output->writeln($msgarray);
                        $objrecord->$setfieldname($value);
                        continue;
                    }

                    $joincolumn = $this->entityutility->getJoinTableField($entityclass, $key);
                    $joincolumnproperty = $this->entityutility->getJoinTableFieldProperty($entityclass, $key);
                    if ($joincolumn && $joincolumnproperty) {
                        $joincolumnobj = $this->em->getRepository($joincolumn)->find($value);
                        $msgok = '<info>Modifica '.$entityclass.' con id '.$objrecord->getId()
                                .' per campo '.$key.' cambio valore da '.print_r($objrecord->$getfieldname(), true)
                                .' a '.print_r($value, true).' tramite entity find</info>';
                        $this->output->writeln($msgok);
                        $joinobj = $this->entityutility->getEntityProperties($joincolumnproperty, new $entityclass());
                        $setfieldname = $joinobj['set'];
                        $objrecord->$setfieldname($joincolumnobj);
                        continue;
                    }

                    $msgok = '<info>Modifica '.$entityclass.' con id '.$objrecord->getId()
                            .' per campo '.$key.' cambio valore da '.print_r($objrecord->$getfieldname(), true)
                            .' a '.print_r($value, true).'</info>';
                    $this->output->writeln($msgok);
                    $objrecord->$setfieldname($value);
                } catch (\Exception $exc) {
                    $msgerr = '<error>Modifica '.$entityclass.' con id '.$objrecord->getId()
                            .' per campo '.$key.', ERRORE: '.$exc->getMessage()
                            .' alla riga '.$exc->getLine().'</error>';
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
