<?php

namespace Cdf\BiCoreBundle\Controller;

use Cdf\BiCoreBundle\Service\Permessi\PermessiManager;

use function count;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Inflector\EnglishInflector;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


use Cdf\BiCoreBundle\Service\Api\ApiManager;
use Cdf\BiCoreBundle\Service\Api\Oauth2TokenService;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @codeCoverageIgnore
 */
class FiApiController extends AbstractController
{
    use FiApiCoreControllerTrait;
    use FiApiCoreCrudControllerTrait;
    use FiCoreTabellaControllerTrait;

    protected string $bundle;
    protected Environment $template;
    protected string $controller;
    protected PermessiManager $permessi;
    //API rest attributes
    protected string $project;
    protected string $model;
    protected string $collection;
    protected string $modelClass;
    protected string $formClass;
    protected string $controllerItem;
    protected string $apiController;
    /** @var array<mixed> */
    protected array $options;
    /** @var array<mixed> */
    protected array $enumOptions;
    /** @var array<mixed> */
    protected array $inflectorExceptions;
    protected ParameterBagInterface $params;
    protected EntityManagerInterface $em;
    protected ApiManager $apiManager;
    protected string $apiProjectCollection;



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

        //*** instantiate ApiManager ***
        $this->apiManager = new ApiManager( 
            $this->params->get("bi_core.oauth2_enabled"), 
            new Oauth2TokenService(
                $this->params->get("bi_core.oauth2_endpoint"),
                $this->params->get("bi_core.oauth2_clientkey")
                ) 
            );

        $this->apiManager->setProjectName( $this->project );
        $this->apiProjectCollection = strtolower($this->collection);

        $projectRoot = $apiUtil->getProjectRoot( $this->project );

        $projectConfiguration = $projectRoot."Configuration";
        $projectHeaderSelector = $projectRoot."HeaderSelector";

        $config = $projectConfiguration::getDefaultConfiguration();
        $headerSelector = new $projectHeaderSelector( $config );

        $this->apiManager->setApiClientConfigs( $headerSelector, $config );

        $this->apiManager->setApiController( $this->apiProjectCollection );

        //it generates options one time for all
        $this->loadInflectorExceptions();
        $this->generateEnumAndOptions();
    }

    protected function loadInflectorExceptions() : void
    {
        $vars = $this->params->get("bi_core.api_inflector_exceptions");
        if (($vars)) {
            $values = json_decode($vars, true);
            $this->inflectorExceptions = $values;
        }
    }

    protected function getCollectionName() : string 
    {
        return $this->apiProjectCollection;
    }

    /**
     * Copy this method into your controller in case of exceptions
     *
     * @param string $singleForm
     * @return array<mixed>|string
     */
    protected function pluralizeForm(string $singleForm)
    {
        if (isset($this->inflectorExceptions[$singleForm])) {
            return $this->inflectorExceptions[$singleForm];
        }
        $inflector = new EnglishInflector();
        return $inflector->pluralize($singleForm);
    }

    /**
     * Pluralize a single form giving as response the correct plurale form matching with existent objects
     */
    protected function pluralize(string $singleForm) : string
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
    protected function generateEnumAndOptions() : void
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
    /**
     * It returns an array with 'controller' with the apiController
     * and 'book' the apiBook
     * and 'entity' the given fieldName less the suffix

     * @param array<mixed> $modellocolonne
     */
    protected function mergeColumnsAndEnumOptions(array &$modellocolonne) : void
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

     * @param string $fieldName
     * @param string $suffixString
     * @return array<mixed>
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

    protected function getBundle() : string
    {
        return $this->bundle;
    }

    protected function getController() : string
    {
        return $this->controller;
    }

    protected function getPermessi() : PermessiManager
    {
        return $this->permessi;
    }

    protected function getTemplate() : Environment
    {
        return $this->template;
    }

    public function getProject() : string
    {
        $annotations = array();
        $r = new ReflectionClass(get_class($this));
        $doc = $r->getDocComment();
        preg_match_all('#@var\(biproject="(.*?)"\)[\r\n]+#s', $doc, $annotations);
        return $annotations[1][0];
    }
}
