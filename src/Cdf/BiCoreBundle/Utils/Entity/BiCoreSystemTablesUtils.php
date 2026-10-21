<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Entity\Ruoli;
use Doctrine\ORM\EntityManager;

class BiCoreSystemTablesUtils
{
    /**
     *
     * @var array<mixed>
     */
    private array $entities = [];

    public function __construct(private readonly EntityManager $em)
    {
        $this->entities[Ruoli::class] = ['priority' => 10];
        $this->entities[\Cdf\BiCoreBundle\Entity\Operatori::class] = ['priority' => 50];
        $this->entities[\Cdf\BiCoreBundle\Entity\Permessi::class] = ['priority' => 100];
        $this->entities[\Cdf\BiCoreBundle\Entity\Storicomodifiche::class] = ['priority' => 110];
        $this->entities[\Cdf\BiCoreBundle\Entity\Colonnetabelle::class] = ['priority' => 120];
        $this->entities[\Cdf\BiCoreBundle\Entity\Opzionitabelle::class] = ['priority' => 150];
        $this->entities[\Cdf\BiCoreBundle\Entity\Menuapplicazione::class] = ['priority' => 160];

        $this->countEntitiesRows();
    }

    public function countEntitiesRows(): void
    {
        $records = $this->entities;
        foreach ($records as $entity => $detail) {
            $qb = $this->em;
            $numrows = $qb->createQueryBuilder()
                    ->select('count(table.id)')
                    ->from($entity, 'table')
                    ->getQuery()
                    ->getSingleScalarResult();
            $this->entities[$entity]['rows'] = $numrows;
        }
    }

    /**
     *
     * @return array<mixed>
     */
    public function getSystemEntities(): array
    {
        return $this->entities;
    }

    /**
     *
     * @return array<mixed>
     */
    public function getDefaultDataSystemEntities(): array
    {
        return $this->entities;
    }
}
