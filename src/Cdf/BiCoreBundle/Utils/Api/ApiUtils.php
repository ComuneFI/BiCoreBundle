<?php

namespace Cdf\BiCoreBundle\Utils\Api;

use Symfony\Component\Finder\Finder;
use hanneskod\classtools\Iterator\ClassIterator;

class ApiUtils
{

    private $getAll;
    private $getCount;
    private $create;
    private $apiCollection;
    //namespaces
    private $namespacePrefix = 'Swagger';
    private $namespaceApi = 'Api';
    private $namespaceModel = "Model";
    private $namespaceForm = "App\\Form";
    //suffix and prefix
    private $suffixApiController = 'Api';
    private $prefixControllerModelItem = "DaosRow";
    private $prefixModelItem = "DaosRow";

    //TODO: evaluate to move this variable into configs
    private $regexPathModels = '/Swagger\\\(.*)\\\Model\\\DaosRow/';

    private $rootDir;

    public function __construct($apiCollection = null)
    {
        if (isset($apiCollection)) {
            $this->apiCollection = lcfirst($apiCollection);
        }
        $this->getAll = "ControllerReadAll";
        $this->getCount = "ControllerCount";
        $this->create = "ControllerCreate";
        $this->delete = "ControllerDeleteItem";
        $this->get = "ControllerReadItem";
        $this->update = "ControllerUpdateItem";
        $this->getAllToString = "ControllerReadAllToString";

        $this->initVariables();
    }

    private function initVariables()
    {
        $this->namespacePrefix = 'Swagger';
        $this->namespaceApi = 'Api';
        $this->namespaceModel = "Model";
        $this->namespaceForm = "App\\Form";
        //suffix and prefix
        $this->suffixApiController = 'Api';
        $this->prefixControllerModelItem = "DaosRow";
        $this->prefixModelItem = "DaosRow";
        $this->regexPathModels = '/Swagger\\\(.*)\\\Model\\\DaosRow/';
    }

    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return path where core bundle will look for api models
     */
    public function bundlesPath()
    {
        return $this->rootDir . '/fi';
    }

    /**
     * Return namespace prefix for api external bundles, i.e. Swagger
     */
    public function namespacePrefix()
    {
        return $this->namespacePrefix;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function namespaceModels()
    {
        return $this->namespaceModel;
    }

    /**
     * Return the name of Api Controller.
     * Given the project name (i.e. Insurance) and the collection name (i.e. Claims) it returns the complete path of API controller
     * class (i.e. \\Swagger\\Insurance\\Api\\ClaimsApi)
     */
    public function getApiControllerClass($project, $entityName): String
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceApi . "\\$entityName" . $this->suffixApiController;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ControllersItemClaim)
     */
    public function getModelControllerClass($project, $modelName): String
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceModel . "\\" . $this->prefixControllerModelItem . $modelName;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ModelsClaim)
     */
    public function getModelClass($project, $modelName): String
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceModel . "\\" . $this->prefixModelItem . $modelName;
        return $className;
    }

    /**
     * Return the name of Form class.
     * Given the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. App\\Form\\Claim)
     */
    public function getFormClass($modelName): String
    {
        $className = $this->namespaceForm . "\\" . $modelName;
        return $className;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function regexPathModels()
    {
        return $this->regexPathModels;
    }

    /**
     * Return the method string to retrieve all elements / or filtering on them
     */
    public function getAll(): String
    {
        return $this->apiCollection . $this->getAll;
    }

    /**
     * Return the method string to retrieve all elements descriptions (it's possible to filter them as for getAll)
     */
    public function getAllToString(): String
    {
        return $this->apiCollection . $this->getAllToString;
    }

    /**
     * Return the method string to retrieve 1 element
     */
    public function getItem(): String
    {
        return $this->apiCollection . $this->get;
    }

    /**
     * Return the method string to update 1 element
     */
    public function getUpdateItem(): String
    {
        return $this->apiCollection . $this->update;
    }

    /**
     * Return the method string to count all elemements inside a collection
     */
    public function getCount(): String
    {
        return $this->apiCollection . $this->getCount;
    }

    /**
     * Return the method string to create an element
     */
    public function getCreate(): String
    {
        return $this->apiCollection . $this->create;
    }

    /**
     * Return the method string to delete an element
     */
    public function getDelete(): String
    {
        return $this->apiCollection . $this->delete;
    }

    /**
     * It looks for Models existent into included external bundles.
     * It uses ApiUtils in order to know where to search and what look for.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function apiModels(): array
    {
        //where to look for
        $path = $this->bundlesPath();
        $regex = $this->regexPathModels();
        //what to look for
        $models = array();
        $finder = new Finder;
        $iter = new ClassIterator($finder->files()->in($path));

        $matches = array();
        // Print the file names of classes, interfaces and traits in given path
        foreach ($iter->getClassMap() as $classname => $splFileInfo) {
            preg_match($regex, $classname, $matches);

            if (count($matches) > 0) {
                $models[] = $matches[1] . '.' . substr($classname, strlen($matches[0])) . ' (API)';
            }
        }
        return $models;
    }
}
