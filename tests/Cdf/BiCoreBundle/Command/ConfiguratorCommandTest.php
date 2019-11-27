<?php

namespace Cdf\BiCoreBundle\Tests\Commands;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfiguratorCommandTest extends WebTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testConfigurator()
    {
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();

        $entity = 'Permessi';
        $fixturefile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fixtures.yml';
        @unlink($fixturefile);
        $kernel = static::$kernel;
        $application = new Application($kernel);

        //Test per fallimento import se non è stato fatto un export
        $commandimport = $application->find('bicorebundle:configuratorimport');
        $commandTesterImport = new CommandTester($commandimport);
        $commandTesterImport->execute(array('--forceupdate' => true, '--verboso' => true));
        $outputimport = $commandTesterImport->getDisplay();

        $this->assertRegExp('/.../', $outputimport);
        $this->assertStringContainsString('Non trovato file ' . $fixturefile, $outputimport);

        $commandexport = $application->find('bicorebundle:configuratorexport');
        $commandTesterExport = new CommandTester($commandexport);
        $commandTesterExport->execute(array());
        $outputexport = $commandTesterExport->getDisplay();

        $this->assertRegExp('/.../', $outputexport);
        $this->assertStringContainsString('Export Entity: Cdf\\BiCoreBundle\\Entity\\' . $entity, $outputexport);

        /* Rimuovo utente per generare l'inserimento tramite import */
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->delete();
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where('o.username= :username');
        $qb->setParameter('username', 'usernoroles');
        $qb->getQuery()->execute();
        $em->clear();

        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set('r.ruolo', ':amministratore');
        $qb->set('r.superadmin', ":true");
        $qb->set('r.user', ":null");
        $qb->set('r.admin', ":null");
        $qb->from('BiCoreBundle:Ruoli', 'r');
        $qb->where('r.ruolo= :ruolo');
        $qb->setParameter('ruolo', 'Amministratore');
        $qb->setParameter('amministratore', 'Amministratores');
        $qb->setParameter('true', true);
        $qb->setParameter('null', null);
        $qb->getQuery()->execute();
        $em->clear();

        $operatoreadmin = $em->getRepository('BiCoreBundle:Operatori')->findOneBy(array(
            'username' => 'admin',
        ));

        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set('p.ruoli', ':operatore');
        $qb->from('BiCoreBundle:Permessi', 'p');
        $qb->where('p.modulo= :modulo');
        $qb->setParameter('modulo', 'Cliente');
        $qb->setParameter('operatore', $operatoreadmin);
        $qb->getQuery()->execute();
        $em->clear();

        $operatore = $em->getRepository('BiCoreBundle:Operatori')->findOneBy(array(
            'username' => 'admin',
        ));
        $operatore->setLastLogin(new \DateTime());
        $operatore->setRoles(array('ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_UNDEFINED'));
        $operatore->setEnabled(false);
        $operatore->setOperatore(null);
        $em->persist($operatore);
        $em->flush();
        $em->clear();

        $menuapplicazione = $em->getRepository('BiCoreBundle:Menuapplicazione')->findOneBy(array(
            'percorso' => 'fi_pannello_amministrazione_homepage',
        ));
        $menuapplicazione->setAutorizzazionerichiesta(true);
        $menuapplicazione->setAttivo(false);
        $em->persist($menuapplicazione);
        $em->flush();
        $em->clear();

        $menuapplicazione2 = $em->getRepository('BiCoreBundle:Menuapplicazione')->findOneBy(array(
            'nome' => 'Amministrazione',
        ));
        $menuapplicazione2->setAutorizzazionerichiesta(false);
        $menuapplicazione2->setNotifiche(false);
        $menuapplicazione2->setTag(null);
        $menuapplicazione2->setTarget("provadacancellare");
        $em->persist($menuapplicazione2);
        $em->flush();
        $em->clear();

        $commandTesterImport2 = new CommandTester($commandimport);
        $commandTesterImport2->execute(array('--forceupdate' => true, '--verboso' => true));
        $outputimport2 = $commandTesterImport2->getDisplay();
        echo $outputimport2;
        $this->assertStringNotContainsString('Non trovato file ' . $fixturefile, $outputimport2);
        $this->assertStringContainsString('Modifica', $outputimport2);
        $this->assertStringContainsString('tramite entity find', $outputimport2);
        $this->assertStringContainsString('lastLogin cambio valore da', $outputimport2);
        $this->assertStringContainsString('ruolo cambio valore da \'Amministratores\' a \'Amministratore\'', $outputimport2);
        $this->assertStringContainsString('sistemata', $outputimport2);
        $this->assertStringContainsString('ROLE_UNDEFINED', $outputimport2);
        $this->assertStringContainsString('in formato DateTime', $outputimport2);
        $this->assertStringContainsString('cambio valore da true a false in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da false a true in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da false a NULL in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da true a NULL in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da NULL a false in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da NULL a true in formato Boolean', $outputimport2);
        $this->assertStringContainsString('cambio valore da NULL a \'admin\'', $outputimport2);
        $this->assertStringContainsString('cambio valore da \'provadacancellare\' a NULL', $outputimport2);
        
        
        $em->clear();

        $commandTesterImport3 = new CommandTester($commandimport);
        $commandTesterImport3->execute(array('--forceupdate' => true, '--verboso' => true, '--truncatetables' => true));
        $outputimport3 = $commandTesterImport3->getDisplay();
        //echo $outputimport3;
        $this->assertStringNotContainsString('Non trovato file ' . $fixturefile, $outputimport3);
        $this->assertStringContainsString('aggiunta', $outputimport3);
        $this->assertStringContainsString('tramite entity find', $outputimport3);
        $this->assertStringContainsString(' in formato Boolean', $outputimport3);
        //$this->assertStringContainsString('in formato DateTime', $outputimport3);
        //$this->assertStringContainsString('campo lastLogin cambio valore da NULL a', $outputimport3);
        $this->assertStringContainsString('tramite entity find', $outputimport3);
        $em->clear();

        unlink($fixturefile);
        $qb = $em->createQueryBuilder();
        $qb->update();
        $qb->set('o.lastLogin', ':ora');
        $qb->from('BiCoreBundle:Operatori', 'o');
        $qb->where('o.username= :username');
        $qb->setParameter('username', 'admin');
        $qb->setParameter('ora', null);
        $qb->getQuery()->execute();
    }

}
