<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Cdf\BiCoreBundle\Entity\Operatori;

class ColonnetabelleRepository extends EntityRepository
{
    /**
     *
     * @param string $tabella
     * @param Operatori $user
     * @return array<Colonnetabelle>
     */
    public function findOpzioniColonnetabella(string $tabella, Operatori $user = null) : array
    {
        $em = $this->getEntityManager();
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->select(array('t'));
        $qb->from(Colonnetabelle::class, 't');
        $qb->where('t.nometabella = :tabella');
        $qb->andWhere('t.nomecampo is not null');
        $qb->orderBy('t.ordineindex', 'asc');
        $qb->setParameter('tabella', $tabella);
        if ($user && !$user->getRuoli()->isSuperadmin()) {
            $qb->andWhere('t.operatori_id = :operatore');
            $qb->setParameter('operatore', $user->getId());
        }
        //$opzioni = $qb->getQuery()->useQueryCache(true)->useResultCache(true, null, 'Opzionitabella')->getResult();
        $opzioni = $qb->getQuery()->getResult();

        return $opzioni;
    }
}
