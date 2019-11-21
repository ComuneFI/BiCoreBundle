<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class BiWebtestcaseAuthorizedClient extends WebTestCase
{

    protected $em = null;

    protected function setUp(): void
    {
        $this->logInAdmin();
        $this->em = static::createClient()->getContainer()->get('doctrine')->getManager();
    }

    protected function getParametriTabella($nomecontroller, $crawler)
    {
        $parametri = array();
        $attributi = array(
            'baseurl', 'bundle', 'colonneordinamento', 'em', 'entityclass', 'entityname',
            'filtri', 'formclass', 'idpassato', 'modellocolonne', 'nomecontroller',
            'paginacorrente', 'paginetotali', 'permessi', 'prefiltri', 'righeperpagina', 'righetotali', 'estraituttirecords',
            'tablename', 'titolotabella', 'multiselezione', 'editinline', 'traduzionefiltri', 'urltabella',
        );
        foreach ($attributi as $attributo) {
            $parametri[$attributo] = $crawler->filter('#Parametri' . $nomecontroller . '.parametri-tabella')->attr('data-' . $attributo);
        }

        return $parametri;
    }

    protected function logInAdmin()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $session = $container->get('session');

        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /* @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $username4test = $container->getParameter('bi_core.admin4test');
        $user = $userManager->findUserBy(array('username' => $username4test));
        $loginManager->loginUser($firewallName, $user);

        /* save the login token into the session and put it in a cookie */
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        return $client;
    }

    protected function logInUsernoreoles()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $session = $container->get('session');

        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /* @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $username4test = $container->getParameter('bi_core.usernoroles4test');
        $user = $userManager->findUserBy(array('username' => $username4test));
        $loginManager->loginUser($firewallName, $user);

        /* save the login token into the session and put it in a cookie */
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        return $client;
    }

    protected function logInUserreadroles()
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
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        return $client;
    }

}
