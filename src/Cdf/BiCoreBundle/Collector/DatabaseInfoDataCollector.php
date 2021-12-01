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

    public function getDatabaseDriver(): string
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

    public function getDatabaseHost(): ?string
    {
        return $this->data['database_host'];
    }

    public function getDatabasePort(): ?string
    {
        return $this->data['database_port'];
    }

    public function getDatabaseName(): string
    {
        return $this->data['database_name'];
    }

    public function getDatabaseUser(): ?string
    {
        return $this->data['database_user'];
    }

    public function getDatabasePassword(): ?string
    {
        return $this->data['database_password'];
    }

    public function reset(): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'databaseInfo';
    }
}
