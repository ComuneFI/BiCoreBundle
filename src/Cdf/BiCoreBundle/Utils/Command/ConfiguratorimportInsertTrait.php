<?php

namespace Cdf\BiCoreBundle\Utils\Command;

use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

trait ConfiguratorimportInsertTrait
{
    private function executeInsert($entityclass, $record)
    {
        $objrecord = new $entityclass();
        foreach ($record as $key => $value) {
            if ('id' !== $key) {
                $propertyEntity = $this->entityutility->getEntityProperties($key, $objrecord);
                $getfieldname = $propertyEntity['get'];
                $setfieldname = $propertyEntity['set'];
                if ('discr' == $key) {
                    continue;
                }
                $fieldtype = $this->dbutility->getFieldType($objrecord, $key);
                if ('boolean' === $fieldtype) {
                    $newval = FieldTypeUtils::getBooleanValue($value);
                    $msgok = '<info>Inserimento '.$entityclass.' con id '.$record['id']
                            .' per campo '.$key.' con valore '
                            .var_export($newval, true).' in formato Boolean</info>';
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
                    $msgok = '<info>Inserimento '.$entityclass.' con id '.$record['id']
                            .' per campo '.$key.' cambio valore da '
                            .($objrecord->$getfieldname() ? $objrecord->$getfieldname()->format('Y-m-d H:i:s') : 'NULL')
                            .' a '.$date->format('Y-m-d H:i:s').' in formato DateTime</info>';
                    $this->output->writeln($msgok);
                    $objrecord->$setfieldname($date);
                    continue;
                }
                if (is_array($value)) {
                    $msgarray = '<info>Inserimento '.$entityclass.' con id '.$record['id']
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
                    $msgok = '<info>Inserimento '.$entityclass.' con id '.$record['id']
                            .' per campo '.$key
                            .' con valore '.print_r($value, true).' tramite entity find</info>';
                    $this->output->writeln($msgok);
                    $joinobj = $this->entityutility->getEntityProperties($joincolumnproperty, new $entityclass());
                    $setfieldname = $joinobj['set'];
                    $objrecord->$setfieldname($joincolumnobj);
                    continue;
                }
                $objrecord->$setfieldname($value);
            }
        }
        $this->em->persist($objrecord);
        $this->em->flush();

        $infomsg = '<info>'.$entityclass.' con id '.$objrecord->getId().' aggiunta</info>';
        $this->output->writeln($infomsg);
        $checkid = $this->changeRecordId($entityclass, $record, $objrecord);

        return $checkid;
    }

    private function changeRecordId($entityclass, $record, $objrecord)
    {
        if ($record['id'] !== $objrecord->getId()) {
            try {
                $qb = $this->em->createQueryBuilder();
                $q = $qb->update($entityclass, 'u')
                        ->set('u.id', ':newid')
                        ->where('u.id = :oldid')
                        ->setParameter('newid', $record['id'])
                        ->setParameter('oldid', $objrecord->getId())
                        ->getQuery();
                $q->execute();
                $msgok = '<info>'.$entityclass.' con id '.$objrecord->getId().' sistemata</info>';
                $this->output->writeln($msgok);
            } catch (\Exception $exc) {
                echo $exc->getMessage();

                return 1;
            }
            $this->em->flush();
        }

        return 0;
    }
}
