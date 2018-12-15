<?php

namespace Cdf\BiCoreBundle\Tests\Commands;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfiguratorCommandTest extends WebTestCase
{

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testConfigurator()
    {
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();

        $entity = 'Permessi';
        $fixturefile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "fixtures.yml";
        @unlink($fixturefile);
        $kernel = static::$kernel;
        $application = new Application($kernel);

        //Test per fallimento import se non è stato fatto un export
        $commandimport = $application->find('bicorebundle:configuratorimport');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array('--forceupdate' => true, '--verboso' => true));
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        $this->assertContains('Non trovato file ' . $fixturefile, $outputimport);
        /**/

        $commandexport = $application->find('bicorebundle:configuratorexport');
        $commandTesterExport = new CommandTester($commandexport);
        $commandTesterExport->execute(array());
        $outputexport = $commandTesterExport->getDisplay();

        $this->assertRegExp('/.../', $outputexport);
        $this->assertContains('Export Entity: Cdf\\BiCoreBundle\\Entity\\' . $entity, $outputexport);

        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set("o.lastLogin", ":ora");
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where("o.username= :username");
        $qb->setParameter('username', "admin");
        $qb->setParameter('ora', new \DateTime("2018-01-01"));
        $users = $qb->getQuery()->execute();
        $em->clear();


        /* Rimuovo utente per generare l'inserimento tramite import */
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->delete();
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where("o.username= :username");
        $qb->setParameter('username', "usernoroles");
        $users = $qb->getQuery()->execute();
        $em->clear();

        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set("r.ruolo", ":amministratore");
        $qb->from('BiCoreBundle:Ruoli', 'r');
        $qb->where("r.ruolo= :ruolo");
        $qb->setParameter('ruolo', "Amministratore");
        $qb->setParameter('amministratore', "Amministratores");
        $users = $qb->getQuery()->execute();
        $em->clear();

        $operatoreadmin = $em->getRepository('BiCoreBundle:Operatori')->findOneBy(array(
            'username' => "admin",
        ));

        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set("p.ruoli", ":operatore");
        $qb->from('BiCoreBundle:Permessi', 'p');
        $qb->where("p.modulo= :modulo");
        $qb->setParameter('modulo', "Cliente");
        $qb->setParameter('operatore', $operatoreadmin);
        $users = $qb->getQuery()->execute();
        $em->clear();


        /**/
        $operatore = $em->getRepository('BiCoreBundle:Operatori')->findOneBy(array(
            'username' => "admin",
        ));
        $operatore->setLastLogin(new \DateTime());
        $operatore->setRoles(array("ROLE_SUPER_ADMIN", "ROLE_ADMIN", "ROLE_UNDEFINED"));
        $em->persist($operatore);
        $em->flush();
        $em->clear();
        /**/

        $commandTesterImport2 = new CommandTester($commandimport);
        $commandTesterImport2->execute(array('--forceupdate' => true, '--verboso' => true));
        $outputimport2 = $commandTesterImport2->getDisplay();
        //echo $outputimport2;
        $this->assertNotContains('Non trovato file ' . $fixturefile, $outputimport2);
        $this->assertContains('Modifica', $outputimport2);
        $this->assertContains('tramite entity find', $outputimport2);
        //$this->assertContains('lastLogin cambio valore da', $outputimport2);
        $this->assertContains('ruolo cambio valore da Amministratores a Amministratore', $outputimport2);
        $this->assertContains('sistemata', $outputimport2);
        $this->assertContains('ROLE_UNDEFINED', $outputimport2);
        $em->clear();

        $commandTesterImport3 = new CommandTester($commandimport);
        $commandTesterImport3->execute(array('--forceupdate' => true, '--verboso' => true, '--truncatetables' => true));
        $outputimport3 = $commandTesterImport3->getDisplay();
        //echo $outputimport3;
        $this->assertNotContains('Non trovato file ' . $fixturefile, $outputimport3);
        $this->assertContains('aggiunta', $outputimport3);
        $this->assertContains('tramite entity find', $outputimport3);
        $this->assertContains(' in formato Boolean', $outputimport3);
        //$this->assertContains('in formato DateTime', $outputimport3);
        //$this->assertContains('campo lastLogin cambio valore da NULL a', $outputimport3);
        $this->assertContains('tramite entity find', $outputimport3);
        $em->clear();

        unlink($fixturefile);
    }
}
