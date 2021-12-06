<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Component\BrowserKit\Cookie;

abstract class BiWebtestcaseNorolesAuthorizedClient extends BiWebtestcaseAuthorizedClient
{

    protected function logInUser()
    {

        $client = static::createClient();
        $container = $client->getContainer();

        $username4test = $container->getParameter('bi_core.usernoroles4test');
        $user = $this->getUserFromUsername($username4test);

        $client->loginUser($user);

        /* save the login token into the session and put it in a cookie */
        return $client;
    }
}
