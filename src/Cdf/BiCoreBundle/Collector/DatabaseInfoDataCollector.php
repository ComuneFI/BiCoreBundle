<?php

namespace Cdf\BiCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseInfoDataCollector extends DataCollector
{
    /* @var $em \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'database_driver' => $this->em->getConnection()->getDriver()->getName(),
            'database_host' => $this->em->getConnection()->getHost(),
            'database_port' => $this->em->getConnection()->getPort(),
            'database_name' => $this->em->getConnection()->getDatabase(),
            'database_user' => $this->em->getConnection()->getUsername(),
            'database_password' => $this->em->getConnection()->getPassword(),
        );
    }

    public function getDatabaseDriver()
    {
        $driverName = 'Driver non gestito da questo pannello';

        if ('pdo_mysql' === $this->data['database_driver']) {
            $driverName = 'MySql';
        }
        if ('pdo_pgsql' === $this->data['database_driver']) {
            $driverName = 'PostgreSQL';
        }
        if ('pdo_sqlite' === $this->data['database_driver']) {
            $driverName = 'SQLite';
        }
        if ('oci8' === $this->data['database_driver']) {
            $driverName = 'Oracle';
        }

        return $driverName;
    }

    public function getDatabaseHost()
    {
        return $this->data['database_host'];
    }

    public function getDatabasePort()
    {
        return $this->data['database_port'];
    }

    public function getDatabaseName()
    {
        return $this->data['database_name'];
    }

    public function getDatabaseUser()
    {
        return $this->data['database_user'];
    }

    public function getDatabasePassword()
    {
        return $this->data['database_password'];
    }

    public function reset()
    {
        return true;
    }

    public function getName()
    {
        return 'databaseInfo';
    }
}
