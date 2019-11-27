<?php

namespace Cdf\BiCoreBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Entity\Permessi;

class PermessiEntityTest extends KernelTestCase
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
    public function testSearchByPermssi()
    {
        $object = $this->em
                ->getRepository(Permessi::class)
                ->findByCrud('cru')
        ;

        $this->assertCount(1, $object);
    }
    public function testSearchByModuloPermssi()
    {
        $object = $this->em
                ->getRepository(Permessi::class)
                ->findPermessoModuloOperatore("Magazzino", "userreadroles")
        ;
        $this->assertTrue($object->getCrud() === "r");
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
