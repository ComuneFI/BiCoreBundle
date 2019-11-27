<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Operatori;
use Cdf\BiCoreBundle\Entity\Permessi;

class PermessiRepository extends EntityRepository
{
    public function findPermessoModuloOperatore($modulo, $operatorepassato)
    {
        $em = $this->getEntityManager();
        if (is_string($operatorepassato)) {
            $operatore = $em
                    ->getRepository(Operatori::class)
                    ->findOneBy(array('username' => $operatorepassato));
            if (false === $operatore) {
                return false;
            }
        } elseif (is_a($operatorepassato, Operatori::class)) {
            $operatore = $operatorepassato;
        } else {
            return false;
        }
        if (!$operatore || !$operatore->getRuoli()) {
            return false;
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
