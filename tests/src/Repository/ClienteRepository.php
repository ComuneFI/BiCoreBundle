<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ClienteRepository extends EntityRepository
{
    public function findAttivi()
    {
        return $this->getEntityManager()->createQueryBuilder()
                        ->select(array('a'))
                        ->from('\\App\\Entity\\Cliente', 'a')
                        ->where('a.attivo = true')
                        ->orderBy('a.nominativo', 'asc')
                        ->getQuery()
                        ->getResult();
    }

    public function findDisattivati()
    {
        return $this->getEntityManager()->createQueryBuilder()
                        ->select(array('a'))
                        ->from('\\App\\Entity\\Cliente', 'a')
                        ->where('a.attivo = false')
                        ->orderBy('a.nominativo', 'asc')
                        ->getQuery()
                        ->getResult();
    }
}
