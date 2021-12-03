<?php

namespace Cdf\BiCoreBundle\Repository;

use Cdf\BiCoreBundle\Entity\Storicomodifiche;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;

class StoricomodificheRepository extends EntityRepository
{
    /**
     * save field modification in history table.
     *
     * @string $nometabella
     * @string $controller
     * @array $changes
     */
    public function saveHistory($nometabella, $changes, $id, $user)
    {
        $em = $this->getEntityManager();

        $adesso = new DateTime();
        foreach ($changes as $fieldName => $change) {
            $nuovamodifica = new Storicomodifiche();
            $nuovamodifica->setNometabella($nometabella);
            $nuovamodifica->setNomecampo($fieldName);
            $nuovamodifica->setIdtabella($id);
            $nuovamodifica->setGiorno($adesso);
            $nuovamodifica->setValoreprecedente($this->getValoreprecedenteImpostare($change));
            $nuovamodifica->setOperatori($user);
            $em->persist($nuovamodifica);
        }
        $em->flush();
        $em->clear();
    }

    private function getValoreprecedenteImpostare($change)
    {
        if (is_object($change)) {
            if ($change instanceof DateTime) {
                $risposta = $change->format('d/m/Y H:i:s');
            } else {
                $risposta = $change->__toString().' ('.$change->getId().')';
            }
        } else {
            $risposta = $change;
        }

        return $risposta;
    }

    /**
     * check if field is historicized.
     *
     * @string $entitclass
     * @string $indicedato fieldname
     *
     * return @boolean
     */
    private function isHistoricized($nometabella, $indiceDato)
    {
        $risposta = false;

        $em = $this->getEntityManager();
        $entity = $em->getRepository(Colonnetabelle::class)->findOneBy(
            array(
                    'nometabella' => $nometabella,
                    'nomecampo' => $indiceDato,
                )
        );

        if ($entity && $entity->isRegistrastorico()) {
            $risposta = true;
        }

        return $risposta;
    }

    /**
     * check if single data is  changed.
     *
     * @array $originalData
     * @array $newData
     *
     * return @string
     */
    private function isDataChanged($nometabella, $datooriginale, $singoloDato, $indiceDato, &$changes)
    {
        if (($datooriginale !== $singoloDato) && $this->isHistoricized($nometabella, $indiceDato)) {
            $changes[$indiceDato] = $datooriginale;
        }
    }

    /**
     * check if something changes.
     *
     * @array $originalData
     * @array $newData
     *
     * return @array
     */
    public function isRecordChanged($nometabella, $originalData, $newData)
    {
        $changes = array();
        foreach ($newData as $indiceDato => $singoloDato) {
            $this->isDataChanged($nometabella, $originalData[$indiceDato], $singoloDato, $indiceDato, $changes);
        }

        return $changes;
    }
}
