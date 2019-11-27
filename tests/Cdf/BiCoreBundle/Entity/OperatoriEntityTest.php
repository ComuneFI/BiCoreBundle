<?php

namespace Cdf\BiCoreBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Entity\Operatori;
use Cdf\BiCoreBundle\Entity\Ruoli;

class OperatoriEntityTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
                ->get('doctrine')
                ->getManager()
        ;
    }

    public function testSearchBy()
    {
        $object = $this->em
                ->getRepository(Operatori::class)
                ->findByUsername('admin')
        ;

        $this->assertCount(1, $object);
    }

    public function testfindBySuperadmin()
    {
        $operatori = $this->em
                ->getRepository(Ruoli::class)
                ->findBy(array('superadmin' => true));

        $this->assertCount(1, $operatori, 'Non trovato il ruolo super admin');
    }

    public function testfindruoli()
    {
        $operatori = $this->em
                ->getRepository(Ruoli::class)
                ->findAll();
        $this->assertGreaterThan(0, count($operatori), 'Non trovati ruoli');
    }

    public function testfindoperatori()
    {
        $operatori = $this->em
                ->getRepository(Operatori::class)
                ->findAll();
        $this->assertGreaterThan(0, count($operatori), 'Non trovati operatori');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
}
