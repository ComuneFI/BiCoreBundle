<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use function count;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Symfony\Component\Inflector\Inflector;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;

class FiApiController extends AbstractController {

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

    public function __construct(PermessiManager $permessi, Environment $template) {
        $matches = [];
        $controllo = new ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (0 == count($matches)) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }
        $this->project = $this->getProject();

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = $permessi;
        $this->template = $template;

        $this->model = $this->controller; //they matches
        
        $results = Inflector::pluralize($this->model);
        if (is_array($results)) {
            foreach($results as $result) {
                //get name of api controller
                $apiClassPath = ApiUtils::getApiControllerClass($this->project, $result);
                if (class_exists($apiClassPath)) {
                    $this->collection = $result;
                break;
                }
            }
        }
        else {
            $this->collection = $results;
            
        }

        $this->modelClass = ApiUtils::getModelClass($this->project, $this->model);
        $this->formClass =  ApiUtils::getFormClass($this->model);
        $this->controllerItem = ApiUtils::getModelControllerClass($this->project, $this->model);    
        $this->apiController = ApiUtils::getApiControllerClass($this->project, $this->collection);
        
    }

    protected function getBundle() {
        return $this->bundle;
    }

    protected function getController() {
        return $this->controller;
    }

    protected function getPermessi() {
        return $this->permessi;
    }

    protected function getTemplate() {
        return $this->template;
    }

    public function getProject() {
        $annotations = array();
        $r = new ReflectionClass(get_class($this));
        $doc = $r->getDocComment();
        preg_match_all('#@var\(biproject="(.*?)"\)\n#s', $doc, $annotations);
        return $annotations[1][0];
    }

}
