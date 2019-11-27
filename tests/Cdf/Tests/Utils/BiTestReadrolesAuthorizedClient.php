<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

abstract class BiTestReadrolesAuthorizedClient extends BiTestAuthorizedClient
{
    protected function setUp(): void
    {
        $this->client = static::createPantherClient();

        $this->container = static::$kernel->getContainer();
        $username4test = $this->container->getParameter('bi_core.usernoroles4test');
        $password4test = $this->container->getParameter('bi_core.usernorolespwd4test');
        $this->em = $this->container->get('doctrine')->getManager();

        $testUrl = '/';
        $this->client->request('GET', $testUrl);
        $this->client->waitFor('#Login');
        $this->login($username4test, $password4test);
    }
}
