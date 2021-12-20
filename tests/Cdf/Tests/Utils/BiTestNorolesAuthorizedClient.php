<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

abstract class BiTestNorolesAuthorizedClient extends BiTestAuthorizedClient
{

    protected static $client;

    protected function setUp(): void
    {

        self::$client = static::createPantherClient();
        try {
            $this->container = static::$kernel->getContainer();
            $username4test = $this->container->getParameter('bi_core.usernoroles4test');
            $password4test = $this->container->getParameter('bi_core.usernorolespwd4test');
            $this->em = $this->container->get('doctrine')->getManager();

            $testUrl = '/';
            self::$client->request('GET', $testUrl);
            self::$client->waitFor('#Login');
            $this->login($username4test, $password4test);
        } catch (\Exception $exc) {
            self::$client->takeScreenshot('tests/var/error.png');
            throw new \Exception($exc);
        }
    }
}
