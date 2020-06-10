<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use function count;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class FiApiController extends AbstractController
{
    use FiCoreControllerTrait;
    use FiCoreCrudControllerTrait;
    use FiCoreTabellaControllerTrait;

    protected $bundle;
    protected $template;
    protected $controller;
    protected $permessi;

    public function __construct(PermessiManager $permessi, Environment $template)
    {
        $matches = [];
        $controllo = new ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (0 == count($matches)) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = $permessi;
        $this->template = $template;
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

    protected function getTemplate()
    {
        return $this->template;
    }
}
