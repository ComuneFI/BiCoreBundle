<?php

namespace Cdf\BiCoreBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Cdf\BiCoreBundle\Entity\Permessi;
use Cdf\BiCoreBundle\Entity\Operatori;

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
        $user4test = $this->em
                ->getRepository(Operatori::class)
                ->findOneByUsername('userreadroles')
        ;

        $object = $this->em
                ->getRepository(Permessi::class)
                ->findPermessoModuloOperatore("Magazzino", $user4test)
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
