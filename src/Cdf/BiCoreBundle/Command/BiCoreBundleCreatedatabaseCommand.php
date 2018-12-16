<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\Common\Persistence\ObjectManager;

class BiCoreBundleCreatedatabaseCommand extends Command
{
    protected static $defaultName = 'bicorebundle:createdatabase';

    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        $this
                ->setDescription('Creazione database bi')
                ->setHelp('Creazione di un nuovo database di bi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;
        $driver = $em->getConnection()->getDriver()->getName();

        if ('pdo_sqlite' != $driver) {
            $command = $this->getApplication()->find('doctrine:database:create');
            $arguments = array('--if-not-exists' => true);
            $inputcmd = new ArrayInput($arguments);
            $command->run($inputcmd, $output);
        }
        $command = $this->getApplication()->find('doctrine:schema:create');
        $arguments = array('');
        $inputcmd = new ArrayInput($arguments);
        $command->run($inputcmd, $output);
    }
}
