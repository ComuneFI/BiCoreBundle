<?php

namespace Cdf\BiCoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Cdf\BiCoreBundle\Entity\Opzionitabelle;

class OpzionitabelleRepository extends EntityRepository
{
    /**
     *
     * @param string $tabella
     * @return array<Opzionitabelle>
     */
    public function findOpzioniTabella(string $tabella): array
    {
        $em = $this->getEntityManager();
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->select(array('t'));
        $qb->from(Opzionitabelle::class, 't');
        $qb->where("(t.nometabella = '*' OR t.nometabella = :tabella)");
        $qb->setParameter('tabella', $tabella);
        //$opzioni = $qb->getQuery()->useQueryCache(true)->useResultCache(true, null, 'Opzionitabella')->getResult();
        $opzioni = $qb->getQuery()->getResult();

        return $opzioni;
    }
}
