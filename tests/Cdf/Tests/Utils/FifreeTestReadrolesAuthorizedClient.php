<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Component\Panther\PantherTestCase;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

abstract class FifreeTestReadrolesAuthorizedClient extends FifreeTestAuthorizedClient
{

    protected function setUp()
    {

        $this->client = static::createPantherClient();
        
        $this->container = static::$kernel->getContainer();
        $username4test = $this->container->getParameter('usernoroles4test');
        $password4test = $this->container->getParameter('usernorolespwd4test');
        $this->em = $this->container->get("doctrine")->getManager();
        
        $testUrl = '/';
        $this->client->request('GET', $testUrl);
        $this->client->waitFor('#Login');
        $this->login($username4test, $password4test);
    }
}
