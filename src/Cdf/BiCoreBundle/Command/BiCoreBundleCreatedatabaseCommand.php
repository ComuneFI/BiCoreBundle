<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\ORM\EntityManagerInterface;

class BiCoreBundleCreatedatabaseCommand extends Command
{
    protected static $defaultName = 'bicorebundle:createdatabase';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->setDescription('Creazione database bi')
                ->setHelp('Creazione di un nuovo database di bi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;
        $driver = $driver = $em->getConnection()->getDatabasePlatform()->getName();

        if ('sqlite' != $driver) {
            $command = $this->getApplication()->find('doctrine:database:create');
            $arguments = array('--if-not-exists' => true);
            $inputcmd = new ArrayInput($arguments);
            $command->run($inputcmd, $output);
        }
        $command = $this->getApplication()->find('doctrine:schema:update');
        $arguments = array('--force' => true);
        $inputcmd = new ArrayInput($arguments);
        $command->run($inputcmd, $output);
        return 0;
    }
}
