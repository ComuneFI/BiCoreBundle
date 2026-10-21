<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Operatori;
use Cdf\BiCoreBundle\Entity\Permessi;

class PermessiRepository extends EntityRepository
{

    public function findPermessoModuloOperatore(string $modulo, ?Operatori $operatore): ?Permessi
    {
        $em = $this->getEntityManager();
        if (!$operatore->getRuoli() instanceof \Cdf\BiCoreBundle\Entity\Ruoli) {
            return null;
        }
        $permesso = $em->getRepository(Permessi::class)
                ->findOneBy(['operatori_id' => $operatore->getId(), 'modulo' => $modulo]);

        if (!$permesso instanceof \Cdf\BiCoreBundle\Entity\Permessi) {
            $permesso = $em
                    ->getRepository(Permessi::class)
                    ->findOneBy(['ruoli_id' => $operatore->getRuoli()->getId(), 'modulo' => $modulo, 'operatori_id' => null]);
            if (!$permesso instanceof \Cdf\BiCoreBundle\Entity\Permessi) {
                $permesso = $em
                        ->getRepository(Permessi::class)
                        ->findOneBy(['ruoli_id' => null, 'modulo' => $modulo, 'operatori_id' => null]);
            }
        }

        return $permesso;
    }
}
