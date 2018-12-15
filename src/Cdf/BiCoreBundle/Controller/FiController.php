<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Utils\Permessi\PermessiUtils;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FiController extends AbstractController
{
    use FiCoreControllerTrait, FiCoreCrudControllerTrait, FiCoreTabellaControllerTrait;

    protected $bundle;
    protected $controller;
    protected $permessi;

    public function __construct(TokenStorageInterface $user, PermessiUtils $permessi)
    {
        $matches = array();
        $controllo = new \ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (count($matches) == 0) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = $permessi;
        $this->user = $user;
    }
    protected function getBundle()
    {
        return $this->bundle;
    }
    protected function getController()
    {
        return $this->controller;
    }
    protected function getPermessi()
    {
        return $this->permessi;
    }
    protected function getUser()
    {
        return $this->user->getToken()->getUser();
    }
}
