<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\Environment as TwigEnvironment;
use Twig\TwigFunction;
use function count;

class MenuExtension extends AbstractExtension
{

    protected EntityManagerInterface $em;
    protected UrlGeneratorInterface $urlgenerator;
    protected TokenStorageInterface $user;
    protected string $rootpath;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlgenerator, TokenStorageInterface $user, string $rootpath)
    {
        $this->em = $em;
        $this->urlgenerator = $urlgenerator;
        $this->user = $user;
        $this->rootpath = $rootpath;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generamenu', [$this, 'generamenu'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
                    ]),
        ];
    }

    public function generamenu(TwigEnvironment $environment): string
    {
        /** @phpstan-ignore-next-line */
        $router = $this->urlgenerator->match('/')['_route'];
        $rispostahome = array();
        $rispostahome[] = array('percorso' => $this->getUrlObject('', $router, ''),
            'nome' => 'Home',
            'target' => '_self',
        );

        $em = $this->em;
        /* @var $qb QueryBuilder */
        $qb = $em->createQueryBuilder();
        $qb->select(array('a'));
        $qb->from('BiCoreBundle:Menuapplicazione', 'a');
        $qb->where('a.attivo = :attivo and (a.padre is null or a.padre = 0)');
        $qb->setParameter('attivo', true);
        $qb->orderBy('a.padre', 'ASC');
        $qb->orderBy('a.ordine', 'ASC');
        $menu = $qb->getQuery()->getResult();

        $risposta = array_merge($rispostahome, $this->getMenu($menu));

        $username = '';
        $urlLogout = '';

        $this->generaManualeMkdocMenu($risposta);

        $this->generaManualePdfMenu($risposta);

        /** @phpstan-ignore-next-line */
        if ('ssocdf' === $this->user->getToken()->getFirewallName()) {
            $username = $this->user->getToken()->getUser()->getUsername();
            $urlLogout = $this->urlgenerator->generate('fi_autenticazione_signout');
        }

        /** @phpstan-ignore-next-line */
        if ('ssolineacomune' === $this->user->getToken()->getFirewallName()) {
            $username = $this->user->getToken()->getUser()->getUsername();
            $urlLogout = $this->urlgenerator->generate('fi_Lineacomuneauth_signout');
        }

        /** @phpstan-ignore-next-line */
        if ('main' === $this->user->getToken()->getFirewallName()) {
            $username = $this->user->getToken()->getUser()->getUsername();
            $urlLogout = $this->urlgenerator->generate('fos_user_security_logout');
        }

        $risposta[] = array('percorso' => $this->getUrlObject($username, '', ''), 'nome' => $username, 'target' => '',
            'sottolivello' => array(
                array('percorso' => $urlLogout, 'nome' => 'Logout', 'target' => '_self'),
            ),
        );
        return $environment->render('@BiCore/Menu/menu.html.twig', array('risposta' => $risposta));
    }

    /**
     *
     * @param string[][] $risposta
     * @return void
     */
    protected function generaManualePdfMenu(array &$risposta): void
    {
        $pathmanuale = $this->rootpath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'manuale.pdf';
        if (file_exists($pathmanuale)) {
            $risposta[] = array(
                'percorso' => $this->getUrlObject('Manuale', $pathmanuale, '_blank'),
                'nome' => 'Manuale (Pdf)', 'target' => '_blank',);
        }
    }

    /**
     *
     * @param string[][] $risposta
     * @return void
     */
    protected function generaManualeMkdocMenu(&$risposta)
    {
        $mkdocsfile = 'manuale' . DIRECTORY_SEPARATOR . 'index.html';
        $pathmanualemkdocs = $this->rootpath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $mkdocsfile;
        if (file_exists($pathmanualemkdocs)) {
            $risposta[] = array(
                'percorso' => array("percorso" => $this->urlgenerator->generate("homepage") . $mkdocsfile),
                'nome' => 'Manuale', 'target' => '_blank',);
        }
    }

    /**
     *
     * @param \Cdf\BiCoreBundle\Entity\Menuapplicazione[] $menu
     * @return array<mixed>
     */
    protected function getMenu($menu)
    {
        $risposta = array();
        $em = $this->em;

        foreach ($menu as $item) {
            $visualizzare = true;

            if ($item->isAutorizzazionerichiesta()) {
                $permessi = new PermessiManager($this->em, $this->user);
                $visualizzare = $permessi->canRead($item->getTag());
            }

            if ($visualizzare) {
                $qb = $em->createQueryBuilder();
                $qb->select(array('a'));
                $qb->from('BiCoreBundle:Menuapplicazione', 'a');
                $qb->where('a.padre = :padre_id');
                $qb->andWhere('a.attivo = :attivo');
                $qb->orderBy('a.padre', 'ASC');
                $qb->orderBy('a.ordine', 'ASC');
                $qb->setParameter('padre_id', $item->getId());
                $qb->setParameter('attivo', true);
                $submenu = $qb->getQuery()->getResult();

                $sottomenutabelle = $this->getSubMenu($submenu);

                $percorso = $this->getUrlObject($item->getNome(), $item->getPercorso(), $item->getTarget());
                $risposta[] = array(
                    'percorso' => $percorso,
                    'nome' => $item->getNome(),
                    'sottolivello' => $sottomenutabelle,
                    'target' => $item->getTarget(),
                    'notifiche' => $item->hasNotifiche(),
                    'tag' => $item->getTag(),
                    'percorsonotifiche' => $this->getUrlObject($item->getNome(), $item->getPercorsonotifiche(), ''),
                );
                unset($submenu);
                unset($sottomenutabelle);
            }
        }

        return $risposta;
    }

    /**
     *
     * @param \Cdf\BiCoreBundle\Entity\Menuapplicazione[] $submenu
     * @return array<mixed>
     */
    protected function getSubMenu($submenu)
    {
        $sottomenutabelle = array();
        foreach ($submenu as $subitem) {
            $visualizzare = true;
            if ($subitem->isAutorizzazionerichiesta()) {
                $permessi = new PermessiManager($this->em, $this->user);
                $visualizzare = $permessi->canRead($subitem->getTag());
            }

            if ($visualizzare) {
                $vettoresottomenu = $this->getMenu(array($subitem));
                $sottomenu = $vettoresottomenu[0];

                if (isset($sottomenu['sottolivello']) && count($sottomenu['sottolivello']) > 0) {
                    $sottolivellomenu = array('sottolivello' => $sottomenu['sottolivello']);
                    $menuobj = $this->getUrlObject($subitem->getNome(), $subitem->getPercorso(), $subitem->getTarget());
                    $sottomenutabelle[] = array_merge($menuobj, $sottolivellomenu);
                } else {
                    $sottomenutabelle[] = $this->getUrlObject($subitem->getNome(), $subitem->getPercorso(), $subitem->getTarget());
                }
            }
        }

        return $sottomenutabelle;
    }

    /**
     *
     * @param string $nome
     * @param string $percorso
     * @param string $target
     * @return array<mixed>
     */
    protected function getUrlObject(string $nome, ?string $percorso, ?string $target)
    {
        if ($this->routeExists($percorso)) {
            $percorso = $this->urlgenerator->generate($percorso);
        } else {
            $percorso = '#';
        }
        if (!$target) {
            $target = '_self';
        }

        return array('percorso' => $percorso, 'nome' => $nome, 'target' => $target);
    }

    protected function routeExists(?string $name): bool
    {
        if ($name === null) {
            return false;
        }
        $router = $this->urlgenerator;

        /** @phpstan-ignore-next-line */
        if ((null === $router->getRouteCollection()->get($name)) ? false : true) {
            return true;
        } else {
            return false;
        }
    }

    protected function urlExists(string $name): bool
    {
        if ($this->checkUrl($name, false)) {
            return true;
        } else {
            if ($this->checkUrl($name, true)) {
                return true;
            } else {
                return false;
            }
        }
    }

    protected function checkUrl(string $name, bool $useProxy): bool
    {
        $ch = curl_init($name);

        /** @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_URL, $name);
        if (!$useProxy) {
            /** @phpstan-ignore-next-line */
            curl_setopt($ch, CURLOPT_PROXY, null);
        }
        /** @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_NOBODY, true);
        /** @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        /** @phpstan-ignore-next-line */
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
        /** @phpstan-ignore-next-line */
        curl_exec($ch);
        /** @phpstan-ignore-next-line */
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 === $retcode || 401 === $retcode) {
            $exist = true;
        } else {
            $exist = false;
        }
        /** @phpstan-ignore-next-line */
        curl_close($ch);

        return $exist;
    }
}
