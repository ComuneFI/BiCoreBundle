<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use function count;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class FiApiController extends AbstractController
{
    use FiApiCoreControllerTrait;
    use FiApiCoreCrudControllerTrait;
    use FiApiCoreTabellaControllerTrait;

    protected $bundle;
    protected $template;
    protected $controller;
    protected $permessi;
    //API rest attributes
    protected $project;
    protected $model;
    protected $collection;
    protected $modelClass;
    protected $formClass;
    protected $controllerItem;
    protected $apiController;

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

         //TODO: input variables that must to be dynamic
        $this->project = 'Insurance';
        $this->model = 'Claim';
        $this->collection = 'Claims';
        $this->modelClass = '\\Swagger\\'.$this->project.'\\Model\\Models'.$this->model;
        $this->formClass = 'App\\Form\\'.$this->model;
        $this->controllerItem = '\\Swagger\\'.$this->project.'\\Model\\ControllersItem'.$this->model;
        $this->apiController = '\\Swagger\\'.$this->project.'\\Api\\'.$this->collection.'Api';
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
