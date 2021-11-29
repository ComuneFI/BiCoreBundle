<?php

namespace Cdf\BiCoreBundle\Tests\Collector;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseInfoDataCollectorTest extends WebTestCase
{
    public function testAction()
    {
        $client = static::createClient();

        // Enable the profiler for the next request
        // (it does nothing if the profiler is not available)
        $client->enableProfiler();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $client->getKernel()->getContainer()->get('test.service_container')->get('doctrine')->getManager();
        
        $driverinfo = $em->getConnection()->getParams();
       
        $dbhostconnection = $driverinfo["host"];
        $dbportconnection = $driverinfo["port"];
        $dbdatabaseconnection = array_key_exists("dbname", $driverinfo) ? $driverinfo["dbname"] : $driverinfo["path"];
        $dbpwdconnection = $driverinfo["password"];
        $dbuserconnection = $driverinfo["user"];

        $crawler = $client->request('GET', '/login');

        if ($profile = $client->getProfile()) {
            $profilerinfo = $profile->getCollector('databaseInfo');
            $this->assertEquals($dbhostconnection, $profilerinfo->getDatabaseHost());
            $this->assertEquals($dbportconnection, $profilerinfo->getDatabasePort());
            $this->assertEquals($dbdatabaseconnection, $profilerinfo->getDatabaseName());
            $this->assertEquals($dbpwdconnection, $profilerinfo->getDatabasePassword());
            $this->assertEquals($dbuserconnection, $profilerinfo->getDatabaseUser());
            $dbdrivertext = $profilerinfo->getDatabaseDriver();
            $len = (strlen($dbdrivertext) > 0);
            $this->assertTrue($len);
        }
    }
}
