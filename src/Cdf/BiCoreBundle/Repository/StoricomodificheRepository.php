<?php

namespace Cdf\BiCoreBundle\Repository;

use Cdf\BiCoreBundle\Entity\Storicomodifiche;
use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Cdf\BiCoreBundle\Entity\Operatori;
use DateTime;

class StoricomodificheRepository extends EntityRepository
{
    /**
     * save field modification in history table.
     *
     * @param string $nometabella
     * @param array<mixed> $changes
     * @param int $id
     * @param Operatori $user
     * @return void
     */
    public function saveHistory(string $nometabella, array $changes, int $id, Operatori $user): void
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

    /**
     *
     * @param mixed $change
     * @return mixed
     */
    private function getValoreprecedenteImpostare($change)
    {
        if (is_object($change)) {
            if ($change instanceof DateTime) {
                $risposta = $change->format('d/m/Y H:i:s');
            } else {
                /** @phpstan-ignore-next-line */
                $risposta = $change->__toString() . ' (' . $change->getId() . ')';
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
     * return @bool
     */
    private function isHistoricized(string $nometabella, string $indiceDato): bool
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
     * @param string $nometabella
     * @param mixed $datooriginale
     * @param mixed $singoloDato
     * @param string $indiceDato
     * @param array<mixed> $changes
     * @return void
     */
    private function isDataChanged(string $nometabella, $datooriginale, $singoloDato, string $indiceDato, array &$changes): void
    {
        if (($datooriginale !== $singoloDato) && $this->isHistoricized($nometabella, $indiceDato)) {
            $changes[$indiceDato] = $datooriginale;
        }
    }

    /**
     * check if something changes.
     *
     * @param string $nometabella
     * @param array<mixed> $originalData
     * @param array<mixed> $newData
     * @return array<mixed>
     */
    public function isRecordChanged(string $nometabella, array $originalData, array $newData): array
    {
        $changes = array();
        foreach ($newData as $indiceDato => $singoloDato) {
            $this->isDataChanged($nometabella, $originalData[$indiceDato], $singoloDato, $indiceDato, $changes);
        }

        return $changes;
    }
}
