<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;

use function count;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Inflector\Inflector;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @codeCoverageIgnore
 */
class FiApiController extends AbstractController
{
    use FiApiCoreControllerTrait;
    use FiApiCoreCrudControllerTrait;
    use FiCoreTabellaControllerTrait;

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
    protected $enumOptions;
    protected $inflectorExceptions;
    protected $params;
    protected EntityManagerInterface $em;

    public function __construct(PermessiManager $permessi, Environment $template, ParameterBagInterface $params, EntityManagerInterface $em)
    {
        $matches = [];
        $controllo = new ReflectionClass(get_class($this));

        preg_match('/(.*)\\\(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        if (0 == count($matches)) {
            preg_match('/(.*)(.*)\\\Controller\\\(.*)Controller/', $controllo->name, $matches);
        }
        $this->project = $this->getProject();
        $this->params = $params;

        $this->bundle = ($matches[count($matches) - 2] ? $matches[count($matches) - 2] : $matches[count($matches) - 3]);
        $this->controller = $matches[count($matches) - 1];
        $this->permessi = $permessi;
        $this->template = $template;
        $this->em = $em;

        $this->model = $this->controller; //they matches
        $this->collection = $this->pluralize($this->model);
        $apiUtil = new ApiUtils();
        $this->modelClass = $apiUtil->getModelClass($this->project, $this->model);
        $this->formClass = $apiUtil->getFormClass($this->model);
        $this->controllerItem = $apiUtil->getModelControllerClass($this->project, $this->model);
        $this->apiController = $apiUtil->getApiControllerClass($this->project, $this->collection);
        $this->options = array();
        $this->enumOptions = array();
        $this->inflectorExceptions = array();
        //it generates options one time for all
        $this->loadInflectorExceptions();
        $this->generateEnumAndOptions();
    }

    protected function loadInflectorExceptions()
    {
        $vars = $this->params->get("bi_core.api_inflector_exceptions");
        if (($vars)) {
            $values = json_decode($vars, true);
            $this->inflectorExceptions = $values;
        }
    }

    /**
     * Copy this method into your controller in case of exceptions
     */
    protected function pluralizeForm($singleForm)
    {
        if (isset($this->inflectorExceptions[$singleForm])) {
            return $this->inflectorExceptions[$singleForm];
        }
        return Inflector::pluralize($singleForm);
    }

    /**
     * Pluralize a single form giving as response the correct plurale form matching with existent objects
     */
    protected function pluralize($singleForm)
    {
        $outcome = '';
        $results = $this->pluralizeForm($singleForm);

        if (is_array($results)) {
            $apiUtil = new ApiUtils();
            foreach ($results as $result) {
                //get name of api controller
                $apiClassPath = $apiUtil->getApiControllerClass($this->project, $result);
                if (class_exists($apiClassPath)) {
                    $outcome = $result;
                    break;
                }
            }
        } else {
            $outcome = $results;
        }
        return $outcome;
    }

    /**
     * Generate option choices for edit form
     */
    protected function generateEnumAndOptions()
    {
        $itemController = new $this->controllerItem();
        $fieldMappings = $itemController::swaggerTypes();

        //dump($fieldMappings);

        foreach (array_keys($fieldMappings) as $fieldName) {
            //is it a foreign key field?
            if (\str_contains($fieldName, '_id')) {
                $tools = $this->getApiTools($fieldName, '_id');
                $apiController = $tools['controller'];
                $apiBook = $tools['book'];

                $method = $apiBook->getAllToString();
                $results = $apiController->$method();

                $arrayContainer = array();
                foreach ($results as $myItem) {
                    //transform this items for options
                    $element = array("id" => $myItem->getCode(), "descrizione" => $myItem->getText(), "valore" => $myItem->getText());
                    array_push($arrayContainer, $element);
                }
                $this->options[$tools['entity']] = $arrayContainer;
            } elseif (\str_contains($fieldName, '_enum')) {
                //dump("in fieldname ".$fieldName );
                $tools = $this->getApiTools($fieldName, '_enum');
                $apiController = $tools['controller'];
                $apiBook = $tools['book'];

                $getAllToStringMethod = $apiBook->getAllToString();
                $results = $apiController->$getAllToStringMethod();

                $decodeMap = array();
                $arrayContainer = array();
                foreach ($results as $result) {
                    $decodeMap[$result['code']] = $result['text'];
                    $element = array("id" => $result['code'], "descrizione" => $result['text'], "valore" => $result['text']);
                    array_push($arrayContainer, $element);
                }
                $this->options[$tools['entity']] = $arrayContainer;

                $arrayItem = array('nometabella' => $this->controller, 'nomecampo' => "$this->controller.$fieldName", 'etichetta' => "$fieldName",
                    'escluso' => false,
                    'decodifiche' => $decodeMap);

                array_push($this->enumOptions, $arrayItem);
            }
        }
    }

    /**
     * It checks if there are some enum collections having a match with defined column fields
     * and append attribute "decofiche" to them.
     * THIS HAVE TO BE INVOKED BY SPECIFIC ENTITY CONTROLLER.
     */
    protected function mergeColumnsAndEnumOptions(array &$modellocolonne)
    {
        foreach ($this->enumOptions as $enumOption) {
            if (isset($modellocolonne[$enumOption['nomecampo']])) {
                $modellocolonne[$enumOption['nomecampo']]['decodifiche'] = $enumOption['decodifiche'];
            }
        }
    }

    /**
     * It returns an array with 'controller' with the apiController
     * and 'book' the apiBook
     * and 'entity' the given fieldName less the suffix
     */
    protected function getApiTools($fieldName, $suffixString): array
    {
        $entityName = substr($fieldName, 0, strpos($fieldName, $suffixString));

        $parametri = array('str' => $entityName, 'primamaiuscola' => true);
        $outcome = StringUtils::toCamelCase($parametri);
        $outcome = $this->pluralize($outcome);

        $apiUtil = new ApiUtils();
        $apiControllerClass = $apiUtil->getApiControllerClass($this->project, $outcome);
        $apiController = new $apiControllerClass();

        //$apiBook = new ApiUtils($entityName);
        $apiBook = new ApiUtils($outcome);

        $results = [
            'controller' => $apiController,
            'book' => $apiBook,
            'entity' => $entityName,
        ];
        return $results;
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

    public function getProject()
    {
        $annotations = array();
        $r = new ReflectionClass(get_class($this));
        $doc = $r->getDocComment();
        preg_match_all('#@var\(biproject="(.*?)"\)[\r\n]+#s', $doc, $annotations);
        return $annotations[1][0];
    }
}
