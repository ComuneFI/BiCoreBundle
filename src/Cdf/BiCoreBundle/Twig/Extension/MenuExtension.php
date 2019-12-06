<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig_Environment;
use Twig_SimpleFunction;
use function count;

class MenuExtension extends AbstractExtension
{

    protected $em;
    protected $urlgenerator;
    protected $user;

    public function __construct(ObjectManager $em, UrlGeneratorInterface $urlgenerator, TokenStorageInterface $user, $rootpath)
    {
        $this->em = $em;
        $this->urlgenerator = $urlgenerator;
        $this->user = $user;
        $this->rootpath = $rootpath;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('generamenu', [$this, 'generamenu'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
                    ]),
        ];
    }

    public function generamenu(Twig_Environment $environment)
    {
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
        
        if ('ssocdf' === $this->user->getToken()->getProviderKey()) {
            $username = $this->user->getToken()->getUser()->getUsername();
            $urlLogout = $this->urlgenerator->generate('fi_autenticazione_signout');
        }

        if ('ssolineacomune' === $this->user->getToken()->getProviderKey()) {
            $username = $this->user->getToken()->getUser()->getUsername();
            $urlLogout = $this->urlgenerator->generate('fi_Lineacomuneauth_signout');
        }

        if ('main' === $this->user->getToken()->getProviderKey()) {
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

    protected function generaManualePdfMenu(&$risposta)
    {
        $pathmanuale = $this->rootpath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'manuale.pdf';
        if (file_exists($pathmanuale)) {
            $risposta[] = array(
                'percorso' => $this->getUrlObject('Manuale', $pathmanuale, '_blank'),
                'nome' => 'Manuale (Pdf)', 'target' => '_blank',);
        }
    }

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

    protected function getUrlObject($nome, $percorso, $target)
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

    protected function routeExists($name)
    {
        $router = $this->urlgenerator;

        if ((null === $router->getRouteCollection()->get($name)) ? false : true) {
            return true;
        } else {
            return false;
        }
    }

    protected function urlExists($name)
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

    protected function checkUrl($name, $proxy)
    {
        $ch = curl_init($name);

        curl_setopt($ch, CURLOPT_URL, $name);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        } else {
            curl_setopt($ch, CURLOPT_PROXY, null);
        }
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout in seconds
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 === $retcode || 401 === $retcode) {
            $exist = true;
        } else {
            $exist = false;
        }
        curl_close($ch);

        return $exist;
    }
}
