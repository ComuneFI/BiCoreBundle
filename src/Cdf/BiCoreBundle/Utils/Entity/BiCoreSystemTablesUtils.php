<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Entity\Ruoli;
use Doctrine\ORM\EntityManager;

class BiCoreSystemTablesUtils
{
    /* @var $em EntityManager */

    private EntityManager $em;

    /**
     *
     * @var array<mixed>
     */
    private $entities = array();

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->entities[Ruoli::class] = array('priority' => 10);
        $this->entities["Cdf\BiCoreBundle\Entity\Operatori"] = array('priority' => 50);
        $this->entities["Cdf\BiCoreBundle\Entity\Permessi"] = array('priority' => 100);
        $this->entities["Cdf\BiCoreBundle\Entity\Storicomodifiche"] = array('priority' => 110);
        $this->entities["Cdf\BiCoreBundle\Entity\Colonnetabelle"] = array('priority' => 120);
        $this->entities["Cdf\BiCoreBundle\Entity\Opzionitabelle"] = array('priority' => 150);
        $this->entities["Cdf\BiCoreBundle\Entity\Menuapplicazione"] = array('priority' => 160);

        $this->countEntitiesRows();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
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
