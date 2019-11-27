<?php

namespace Cdf\BiCoreBundle\Tests\Commands;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClonaruoloCommandTest extends WebTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testClonaruolo()
    {
        $kernel = static::$kernel;
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $application = new Application($kernel);

        //Test per fallimento import se non è stato fatto un export
        $commandclonaruolo = $application->find('bicorebundle:clonaruolo');
        $commandTesterClonaruolo = new CommandTester($commandclonaruolo);
        $nuovoruolo = "Altro amministratore";
        $commandTesterClonaruolo->execute(array("ruoloesistente" => "Amministratore", "nuovoruolo" => $nuovoruolo));
        $outputclonaruolo = $commandTesterClonaruolo->getDisplay();
        //echo $outputclonaruolo;
        $this->assertRegExp('/.../', $outputclonaruolo);

        $query = $em->createQueryBuilder()
                ->select('p')
                ->from('BiCoreBundle:Ruoli', 'p')
                ->where("p.ruolo = :ruolo")
                ->setParameter('ruolo', $nuovoruolo)
                ->getQuery();

        $resultruolonew = $query->getResult();
        $ruolonew = $resultruolonew[0];

        $query = $em->createQueryBuilder()
                        ->delete('BiCoreBundle:Permessi', 'p')
                        ->where("p.ruoli = :ruolo")
                        ->setParameter('ruolo', $ruolonew)
                        ->getQuery()->execute();
        
        $em->remove($ruolonew);
        $em->flush();
    }

}
