<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use ReflectionClass;

use function count;

class FiController extends AbstractController
{
    use FiCoreControllerTrait;
    use FiCoreCrudControllerTrait;
    use FiCoreTabellaControllerTrait;

    protected string $bundle;
    protected string $controller;

    public function __construct(protected PermessiManager $permessi, protected Environment $template, protected EntityManagerInterface $em)
    {
        $matches = [];
        $controllo = new ReflectionClass(static::class);

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (0 === count($matches)) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }

        $this->bundle = ($matches[count($matches) - 2] ?: $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
    }

    protected function getBundle(): string
    {
        return $this->bundle;
    }

    protected function getController(): string
    {
        return $this->controller;
    }

    protected function getPermessi(): PermessiManager
    {
        return $this->permessi;
    }

    protected function getTemplate(): Environment
    {
        return $this->template;
    }
}
