<?php

namespace Cdf\BiCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class DatabaseInfoDataCollector extends DataCollector
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
        $driverinfo = $this->em->getConnection()->getParams();
        if (isset($driverinfo["dbname"])) {
            $dbname = $driverinfo["dbname"];
        } else {
            $dbname = $driverinfo["path"] ?? "";
        }
        $this->data = [
            'database_driver' => $driverinfo["driver"] ?? "",
            'database_host' => $driverinfo["host"] ?? "",
            'database_port' => $driverinfo["port"] ?? "",
            'database_name' => $dbname,
            'database_user' => $driverinfo["user"] ?? "",
            'database_password' => $driverinfo["password"] ?? ""
        ];
    }

    public function getDatabaseDriver(): string
    {
        if ('pdo_mysql' === $this->data['database_driver']) {
            return 'MySql';
        }
        if ('pdo_pgsql' === $this->data['database_driver']) {
            return 'PostgreSQL';
        }
        if ('pdo_sqlite' === $this->data['database_driver']) {
            return 'SQLite';
        }
        if ('oci8' === $this->data['database_driver']) {
            return 'Oracle';
        }

        return 'Driver non gestito da questo pannello';
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
