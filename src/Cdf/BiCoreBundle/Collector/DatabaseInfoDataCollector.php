<?php

namespace Cdf\BiCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class DatabaseInfoDataCollector extends DataCollector
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        $driverinfo = $this->em->getConnection()->getParams();
        $dbname = "";
        if (isset($driverinfo["dbname"]) && array_key_exists("dbname", $driverinfo)) {
            $dbname = $driverinfo["dbname"];
        } else {
            $dbname = isset($driverinfo["path"])?$driverinfo["path"]:"";
        }
        $this->data = array(
            'database_driver' => isset($driverinfo["driver"])?$driverinfo["driver"]:"",
            'database_host' => isset($driverinfo["host"])?$driverinfo["host"]:"",
            'database_port' => isset($driverinfo["port"])?$driverinfo["port"]:"",
            'database_name' => $dbname,
            'database_user' => isset($driverinfo["user"])?$driverinfo["user"]:"",
            'database_password' => isset($driverinfo["password"])?$driverinfo["password"]:""
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
