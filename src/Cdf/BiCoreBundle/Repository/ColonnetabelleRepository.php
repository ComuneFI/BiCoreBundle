<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ColonnetabelleRepository extends EntityRepository
{
    public function findOpzioniColonnetabella($tabella, $user = null)
    {
        $em = $this->getEntityManager();
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->select(array('t'));
        $qb->from('BiCoreBundle:Colonnetabelle', 't');
        $qb->where('t.nometabella = :tabella');
        $qb->andWhere('t.nomecampo is not null');
        $qb->orderBy('t.ordineindex', 'asc');
        $qb->setParameter('tabella', $tabella);
        if ($user && !$user->isSuperadmin()) {
            $qb->andWhere('t.operatori_id = :operatore');
            $qb->setParameter('operatore', $user->getId());
        }
        //$opzioni = $qb->getQuery()->useQueryCache(true)->useResultCache(true, null, 'Opzionitabella')->getResult();
        $opzioni = $qb->getQuery()->getResult();

        return $opzioni;
    }
}
