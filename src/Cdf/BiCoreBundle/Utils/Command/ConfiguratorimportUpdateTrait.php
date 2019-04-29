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
            $cambiato = $this->dbutility->isRecordChanged($entityclass, $key, $objrecord->$getfieldname(), $value);
            if (!$cambiato) {
                if ($this->verboso) {
                    $msginfo = '<info>' . $entityclass . ' con id ' . $objrecord->getId()
                            . ' per campo ' . $key . ' non modificato perchè già '
                            . var_export($value, true) . '</info>';
                    $this->output->writeln($msginfo);
                }
            } else {
                try {
                    $this->update($objrecord, $key, $value, $entityclass);
                } catch (\Exception $exc) {
                    $msgerr = '<error>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                            . ' per campo ' . $key . ', ERRORE: ' . $exc->getMessage()
                            . ' alla riga ' . $exc->getLine() . '</error>';
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

    private function update(&$objrecord, $key, $value, $entityclass)
    {
        $fieldtype = $this->dbutility->getFieldType($objrecord, $key);
        $propertyEntity = $this->entityutility->getEntityProperties($key, $objrecord);
        $getfieldname = $propertyEntity['get'];
        $setfieldname = $propertyEntity['set'];

        if ('boolean' === $fieldtype) {
            $newval = FieldTypeUtils::getBooleanValue($value);

            $msgok = '<info>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                    . ' per campo ' . $key . ' cambio valore da '
                    . var_export($objrecord->$getfieldname(), true)
                    . ' a ' . var_export($newval, true) . ' in formato Boolean</info>';
            $this->output->writeln($msgok);
            $objrecord->$setfieldname($newval);
            return true;
        }
        if ('datetime' === $fieldtype || 'date' === $fieldtype) {
            $date = FieldTypeUtils::getDateTimeValueFromTimestamp($value);
            $msgok = '<info>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                    . ' per campo ' . $key . ' cambio valore da '
                    //. (!is_null($objrecord->$getfieldname())) ? $objrecord->$getfieldname()->format("Y-m-d H:i:s") : "(null)"
                    . ($objrecord->$getfieldname() ? $objrecord->$getfieldname()->format('Y-m-d H:i:s') : var_export(null, true))
                    . ' a ' . ($value ? $date->format('Y-m-d H:i:s') : var_export(null, true)) . ' in formato DateTime</info>';
            $this->output->writeln($msgok);
            $objrecord->$setfieldname($date);
            return true;
        }
        if (is_array($value) || is_array($objrecord->$getfieldname())) {
            $newval = FieldTypeUtils::getArrayValue($value);
            $msgarray = '<info>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                    . ' per campo ' . $key . ' cambio valore da '
                    . json_encode($objrecord->$getfieldname()) . ' a '
                    . json_encode($newval) . ' in formato array' . '</info>';
            $this->output->writeln($msgarray);
            $objrecord->$setfieldname($newval);
            return true;
        }

        $joincolumn = $this->entityutility->getJoinTableField($entityclass, $key);
        $joincolumnproperty = $this->entityutility->getJoinTableFieldProperty($entityclass, $key);
        if ($joincolumn && $joincolumnproperty) {
            $joincolumnobj = $this->em->getRepository($joincolumn)->find($value);
            $msgok = '<info>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                    . ' per campo ' . $key . ' cambio valore da ' . var_export($objrecord->$getfieldname(), true)
                    . ' a ' . var_export($value, true) . ' tramite entity find</info>';
            $this->output->writeln($msgok);
            $joinobj = $this->entityutility->getEntityProperties($joincolumnproperty, new $entityclass());
            $setfieldname = $joinobj['set'];
            $objrecord->$setfieldname($joincolumnobj);
            return true;
        }

//        //Si prende in considerazione solo il null del boolean, gli altri non si toccano
//        if (!is_null($value)) {
//            return true;
//        }

        $msgok = '<info>Modifica ' . $entityclass . ' con id ' . $objrecord->getId()
                . ' per campo ' . $key . ' cambio valore da ' . var_export($objrecord->$getfieldname(), true)
                . ' a ' . var_export($value, true) . '</info>';
        $this->output->writeln($msgok);
        $objrecord->$setfieldname($value);
        return false;
    }
}
