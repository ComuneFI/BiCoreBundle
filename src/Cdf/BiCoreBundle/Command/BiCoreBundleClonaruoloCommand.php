<?php

namespace Cdf\BiCoreBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'bicorebundle:clonaruolo', description: 'Clona i permessi di un ruolo esistente su un nuovo ruolo')]
class BiCoreBundleClonaruoloCommand extends Command
{

    protected function configure(): void
    {
        $this
                ->setHelp('Specificare come parametri il nome del ruolo da clonare e quello nuovo')
                ->addArgument('ruoloesistente', InputArgument::REQUIRED, 'Ruolo esistente')
                ->addArgument('nuovoruolo', InputArgument::REQUIRED, 'Nuovo ruolo')
        ;
    }

    public function __construct(private readonly EntityManagerInterface $em)
    {
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ruoloesistente = $input->getArgument('ruoloesistente');
        $nuovoruolo = $input->getArgument('nuovoruolo');

        if (!$ruoloesistente) {
            throw new Exception('Inserire il ruolo da clonare');
        }
        if (!$nuovoruolo) {
            throw new Exception('Inserire il nuvo ruolo');
        }

        $query = $this->em->createQueryBuilder()
                ->select('r')
                ->from(\Cdf\BiCoreBundle\Entity\Ruoli::class, 'r')
                ->where('r.ruolo = :ruolo')
                ->setParameter('ruolo', $ruoloesistente)
                ->getQuery()
        ;
        $ruoloesistenteobj = $query->getResult();
        if (!$ruoloesistenteobj) {
            throw new Exception('Non esiste il ruolo ' . $ruoloesistente);
        }
        $newruoloesistente = $ruoloesistenteobj[0];

        $query = $this->em->createQueryBuilder()
                ->select('r')
                ->from(\Cdf\BiCoreBundle\Entity\Ruoli::class, 'r')
                ->where('r.ruolo = :ruolo')
                ->setParameter('ruolo', $nuovoruolo)
                ->getQuery()
        ;
        $nuovruoloobj = $query->getResult();
        if ($nuovruoloobj) {
            throw new Exception('Esiste già il ruolo ' . $nuovoruolo);
        }

        $output->writeln('<info>Inizio clonazione del ruolo ' . $ruoloesistente . ' in ' . $nuovoruolo . '</info>');
        $newnuovoruolo = clone $newruoloesistente;
        $newnuovoruolo->setRuolo($nuovoruolo);
        $this->em->persist($newnuovoruolo);
        $this->em->flush();

        $query = $this->em->createQueryBuilder()
                ->select('p')
                ->from(\Cdf\BiCoreBundle\Entity\Permessi::class, 'p')
                ->where('p.ruoli = :ruolo')
                ->setParameter('ruolo', $newruoloesistente)
                ->getQuery()
        ;
        $permessiobj = $query->getResult();
        foreach ($permessiobj as $permesso) {
            $newpermessi = clone $permesso;
            $newpermessi->setRuoli($newnuovoruolo);
            $this->em->persist($newpermessi);
            $this->em->flush();
        }
        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
