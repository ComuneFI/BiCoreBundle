<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;
use function count;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Symfony\Component\Inflector\Inflector;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use \Swagger\Insurance\Model\ModelsClaim;

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

    protected $options;

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
        $this->collection = $this->pluralize($this->model);        
        $this->modelClass = ApiUtils::getModelClass($this->project, $this->model);
        $this->formClass =  ApiUtils::getFormClass($this->model);
        $this->controllerItem = ApiUtils::getModelControllerClass($this->project, $this->model);    
        $this->apiController = ApiUtils::getApiControllerClass($this->project, $this->collection);
        $this->options = array();
        //it generates options one time for all
        $this->generateOptions();
        //dump($this->options);
    }

    /**
     * Pluralize a single form giving as response the correct plurale form matching with existent objects
     */
    private function pluralize( $singleForm ) {
        $outcome = '';
        $results = Inflector::pluralize($singleForm);
        if (is_array($results)) {
            foreach($results as $result) {
                //get name of api controller
                $apiClassPath = ApiUtils::getApiControllerClass($this->project, $result);
                if (class_exists($apiClassPath)) {
                    $outcome = $result;
                break;
                }
            }
        }
        else {
            $outcome = $results;
            
        }
        return $outcome;
    }

    /**
     * Generate option choices for edit form
     */
    protected function generateOptions() {
        $itemController = new $this->modelClass();
        $fieldMappings = $itemController::swaggerTypes();

        foreach ($fieldMappings as $fieldName=>$fieldType) {
            if ( \str_contains( $fieldName ,'_id') ) {
                //dump($fieldName);
                $entityName = substr( $fieldName, 0, strpos($fieldName, '_id'));

                $outcome = $this->pluralize(ucfirst($entityName));

                //dump($outcome);
                $apiControllerClass = ApiUtils::getApiControllerClass( $this->project, $outcome);
                $apiController = new $apiControllerClass();

                $apiBook = new ApiUtils($outcome);
                $method = $apiBook->getAllToString();
                //dump($apiControllerClass);
                //dump($method);
    
                $results = $apiController->$method();
                //dump($results);
                $arrayContainer = array();
                foreach($results as $key => $myItem) {
                    //transform this items for options
                    $element = array("id" => $myItem->getCode(), "descrizione" => $myItem->getText(), "valore" => $myItem->getText());
                    array_push($arrayContainer, $element);
                }
                $this->options[$entityName] = $arrayContainer;
            }
        }

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
