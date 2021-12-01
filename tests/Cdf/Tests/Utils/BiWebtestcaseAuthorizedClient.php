<?php

namespace Cdf\BiCoreBundle\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class BiWebtestcaseAuthorizedClient extends WebTestCase {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp(): void {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
                ->get('doctrine')
                ->getManager()
        ;
    }

    protected function getParametriTabella($nomecontroller, $crawler) {
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

    protected function getUserFromUsername(string $username) {

        $users = $this->em->createQueryBuilder()
                ->select('r')
                ->from('BiCoreBundle:Operatori', 'r')
                ->where('r.username = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getResult();
        return $users[0];
    }

    protected function logInAdmin() {
        $client = static::createClient();
        $container = $client->getContainer();

        $username4test = $container->getParameter('bi_core.admin4test');
        $user = $this->getUserFromUsername($username4test);

        $client->loginUser($user);

        /* save the login token into the session and put it in a cookie */
        return $client;
    }

    protected function logInUsernoreoles() {
        $client = static::createClient();
        $container = $client->getContainer();

        $username4test = $container->getParameter('bi_core.usernoroles4test');
        $user = $this->getUserFromUsername($username4test);

        $client->loginUser($user);
        return $client;
    }

    protected function logInUserreadroles() {
        $client = static::createClient();
        $container = $client->getContainer();

        $username4test = $container->getParameter('bi_core.userreadroles4test');
        $user = $this->getUserFromUsername($username4test);

        $client->loginUser($user);
        return $client;
    }

}
