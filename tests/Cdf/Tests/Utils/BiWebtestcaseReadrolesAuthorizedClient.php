<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Component\BrowserKit\Cookie;

abstract class BiWebtestcaseReadrolesAuthorizedClient extends BiWebtestcaseAuthorizedClient
{
    protected function logInUser()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $session = $container->get('session');

        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /* @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $username4test = $container->getParameter('bi_core.userreadroles4test');
        $user = $userManager->findUserBy(array('username' => $username4test));
        $loginManager->loginUser($firewallName, $user);

        /* save the login token into the session and put it in a cookie */
        $container->get('session')->set('_security_'.$firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        return $client;
    }
}
