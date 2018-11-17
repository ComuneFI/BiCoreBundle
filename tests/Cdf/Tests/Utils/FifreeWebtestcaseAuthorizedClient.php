<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class FifreeWebtestcaseAuthorizedClient extends WebTestCase
{

    protected $client = null;
    protected $em = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->logInAdmin();
        $this->em = $this->client->getContainer()->get("doctrine")->getManager();
    }
    protected function getParametriTabella($crawler)
    {
        $parametri = array();
        $attributi = array(
            'baseurl', 'bundle', 'colonneordinamento', 'em', 'entityclass', 'entityname',
            'filtri', 'formclass', 'idpassato', 'modellocolonne', 'nomecontroller',
            'paginacorrente', 'paginetotali', 'permessi', 'prefiltri', 'righeperpagina', 'righetotali', 'estraituttirecords',
            'tablename', 'titolotabella', 'traduzionefiltri', 'urltabella'
        );
        foreach ($attributi as $attributo) {
            $parametri[$attributo] = $crawler->filter('#tabella-container .parametri-tabella')->attr('data-' . $attributo);
        }
        return $parametri;
    }
    protected function logInAdmin()
    {

        $container = $this->client->getContainer();
        $session = $container->get('session');

        /* @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /* @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $username4test = $container->getParameter('admin4test');
        $user = $userManager->findUserBy(array('username' => $username4test));
        $loginManager->loginUser($firewallName, $user);

        /* save the login token into the session and put it in a cookie */
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }
}
