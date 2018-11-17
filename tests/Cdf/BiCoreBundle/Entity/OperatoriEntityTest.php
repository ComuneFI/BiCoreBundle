<?php

namespace Cdf\BiCoreBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OperatoriEntityTest extends KernelTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
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
                ->getRepository('BiCoreBundle:Operatori')
                ->findByUsername('admin')
        ;

        $this->assertCount(1, $object);
    }

    public function testfindBySuperadmin()
    {
        $operatori = $this->em
                ->getRepository('BiCoreBundle:Ruoli')
                ->findBy(array('superadmin' => true));

        $this->assertCount(1, $operatori, 'Non trovato il ruolo super admin');
    }

    public function testfindruoli()
    {
        $operatori = $this->em
                ->getRepository('BiCoreBundle:Ruoli')
                ->findAll();
        $this->assertGreaterThan(0, count($operatori), 'Non trovati ruoli');
    }

    public function testfindoperatori()
    {
        $operatori = $this->em
                ->getRepository('BiCoreBundle:Operatori')
                ->findAll();
        $this->assertGreaterThan(0, count($operatori), 'Non trovati operatori');
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
}
