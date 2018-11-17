<?php

namespace Cdf\BiCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseInfoDataCollector extends DataCollector
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'database_driver' => $this->container->get('database_connection')->getDriver()->getName(),
            'database_host' => $this->container->get('database_connection')->getHost(),
            'database_port' => $this->container->get('database_connection')->getPort(),
            'database_name' => $this->container->get('database_connection')->getDatabase(),
            'database_user' => $this->container->get('database_connection')->getUsername(),
            'database_password' => $this->container->get('database_connection')->getPassword(),
        );
    }

    public function getDatabaseDriver()
    {
        $driverName = 'Driver non gestito da questo pannello';

        if ($this->data['database_driver'] === 'pdo_mysql') {
            $driverName = 'MySql';
        }
        if ($this->data['database_driver'] === 'pdo_pgsql') {
            $driverName = 'PostgreSQL';
        }
        if ($this->data['database_driver'] === 'pdo_sqlite') {
            $driverName = 'SQLite';
        }
        if ($this->data['database_driver'] === 'oci8') {
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
