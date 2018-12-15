<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class OpzionitabelleRepository extends EntityRepository
{
    public function findOpzioniTabella($tabella)
    {
        $em = $this->getEntityManager();
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->select(array('t'));
        $qb->from('BiCoreBundle:Opzionitabelle', 't');
        $qb->where("(t.nometabella = '*' OR t.nometabella = :tabella)");
        $qb->setParameter('tabella', $tabella);
        //$opzioni = $qb->getQuery()->useQueryCache(true)->useResultCache(true, null, 'Opzionitabella')->getResult();
        $opzioni = $qb->getQuery()->getResult();

        return $opzioni;
    }
}
