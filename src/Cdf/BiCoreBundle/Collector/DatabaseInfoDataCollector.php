<?php

namespace Cdf\BiCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use \Throwable;

class DatabaseInfoDataCollector extends DataCollector
{
    /* @var $em \Doctrine\ORM\EntityManager */

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function collect(Request $request, Response $response, Throwable $exception = null)
    {
        $driverinfo = $this->em->getConnection()->getParams();
        $this->data = array(
            'database_driver' => $driverinfo["driver"],
            'database_host' => $driverinfo["host"],
            'database_port' => $driverinfo["port"],
            'database_name' => array_key_exists("dbname", $driverinfo) ? $driverinfo["dbname"] : $driverinfo["path"],
            'database_user' => $driverinfo["user"],
            'database_password' => $driverinfo["password"],
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
