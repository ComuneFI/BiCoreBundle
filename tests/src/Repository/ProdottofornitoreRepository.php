<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ProdottofornitoreRepository extends EntityRepository
{
    public function findDisponibili()
    {
        return $this->getEntityManager()->createQueryBuilder()
                        ->select(array('a'))
                        ->from('App:Prodottofornitore', 'a')
                        ->where('a.quantitadisponibile > 0')
                        ->getQuery()
                        ->getResult();
    }
    public function findNonDisponibili()
    {
        return $this->getEntityManager()->createQueryBuilder()
                        ->select(array('a'))
                        ->from('App:Prodottofornitore', 'a')
                        ->where('a.quantitadisponibile = 0')
                        ->getQuery()
                        ->getResult();
    }
}
