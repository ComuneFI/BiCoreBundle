<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class OrdineRepository extends EntityRepository
{
    public function findOrdiniAnnoMese($anno, $mese)
    {
        $dal = \DateTime::createFromFormat('Y-m-d', $anno.'-'.$mese.'-01');
        $al = \date('Y-m-t', strtotime($anno.'-'.$mese.'-01'));

        return $this->getEntityManager()->createQueryBuilder()
                        ->select(array('a'))
                        ->from('\\App\\Entity\\Ordine', 'a')
                        ->where('a.data between :dal AND :al')
                        ->setParameter('dal', $dal)
                        ->setParameter('al', $al)
                        ->orderBy('a.data', 'asc')
                        ->getQuery()
                        ->getResult();
    }
}
