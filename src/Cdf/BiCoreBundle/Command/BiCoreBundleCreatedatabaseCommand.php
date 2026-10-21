<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\ORM\EntityManagerInterface;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'bicorebundle:createdatabase', description: 'Creazione database bi')]
class BiCoreBundleCreatedatabaseCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->setHelp('Creazione di un nuovo database di bi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;
//        $driver = $driver = $em->getConnection()->getDatabasePlatform()->getName();
        $platform = $em->getConnection()->getDatabasePlatform();

        if ($platform instanceof \Doctrine\DBAL\Platforms\SQLitePlatform) {
        //if ('sqlite' != $driver) {
            $command = $this->getApplication()->find('doctrine:database:create');
            $arguments = ['--if-not-exists' => true];
            $inputcmd = new ArrayInput($arguments);
            $command->run($inputcmd, $output);
        }
        $command = $this->getApplication()->find('doctrine:schema:update');
        $arguments = ['--force' => true];
        $inputcmd = new ArrayInput($arguments);
        $command->run($inputcmd, $output);
        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
