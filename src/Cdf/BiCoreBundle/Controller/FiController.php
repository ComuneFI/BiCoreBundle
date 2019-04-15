<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FiController extends AbstractController
{
    use FiCoreControllerTrait, FiCoreCrudControllerTrait, FiCoreTabellaControllerTrait;

    protected $bundle;
    protected $controller;
    protected $permessi;

    public function __construct(PermessiManager $permessi)
    {
        $matches = array();
        $controllo = new \ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (0 == count($matches)) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = $permessi;
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
}
