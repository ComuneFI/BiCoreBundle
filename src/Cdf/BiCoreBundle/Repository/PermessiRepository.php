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
        if (!$operatore->getRuoli()) {
            return null;
        }
        $permesso = $em->getRepository(Permessi::class)
                ->findOneBy(array('operatori_id' => $operatore->getId(), 'modulo' => $modulo));

        if (!$permesso) {
            $permesso = $em
                    ->getRepository(Permessi::class)
                    ->findOneBy(array('ruoli_id' => $operatore->getRuoli()->getId(), 'modulo' => $modulo, 'operatori_id' => null));
            if (!$permesso) {
                $permesso = $em
                        ->getRepository(Permessi::class)
                        ->findOneBy(array('ruoli_id' => null, 'modulo' => $modulo, 'operatori_id' => null));
            }
        }

        return $permesso;
    }
}
