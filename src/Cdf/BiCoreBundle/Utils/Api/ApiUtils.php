<?php

namespace Cdf\BiCoreBundle\Utils\Api;

use Symfony\Component\Finder\Finder;
use hanneskod\classtools\Iterator\ClassIterator;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @codeCoverageIgnore
 */

class ApiUtils
{

    private string $getAll;
    private string $getCount;
    private string $create;
    private string $apiCollection;
    //namespaces
    private string $namespacePrefix = 'Swagger';
    private string $namespaceApi = 'Api';
    private string $namespaceModel = "Model";
    private string $namespaceForm = "App\\Form";
    //suffix and prefix
    private string $suffixApiController = 'Api';
    private string $prefixControllerModelItem = "DaosRow";
    private string $prefixModelItem = "DaosRow";

    private string $delete;
    private string $get;
    private string $update;
    private string $getAllToString;

    //TODO: evaluate to move this variable into configs
    private string $regexPathModels = '/Swagger\\\(.*)\\\Model\\\DaosRow/';

    private string $rootDir;

    public function __construct(string $apiCollection = null)
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

    private function initVariables() : void
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

    public function setRootDir(string $rootDir) : void
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return path where core bundle will look for api models
     */
    public function bundlesPath() : string
    {
        return $this->rootDir . '/fi';
    }

    /**
     * Return namespace prefix for api external bundles, i.e. Swagger
     */
    public function namespacePrefix() : string
    {
        return $this->namespacePrefix;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function namespaceModels() : string
    {
        return $this->namespaceModel;
    }

    /**
     * Return the project Root path string.
     * In case of project Insurance, it will return the string "\\Swagger\\Insurance\\"
     */
    public function getProjectRoot(string $projectName) : string 
    {
        return "\\".$this->namespacePrefix()."\\".$projectName."\\";
    }

    /**
     * Return the name of Api Controller.
     * Given the project name (i.e. Insurance) and the collection name (i.e. Claims) it returns the complete path of API controller
     * class (i.e. \\Swagger\\Insurance\\Api\\ClaimsApi)
     */
    public function getApiControllerClass(string $project, string $entityName): string
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceApi . "\\$entityName" . $this->suffixApiController;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ControllersItemClaim)
     */
    public function getModelControllerClass(string $project, string $modelName): string
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceModel . "\\" . $this->prefixControllerModelItem . $modelName;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ModelsClaim)
     */
    public function getModelClass(string $project, string $modelName): string
    {
        $className = "\\" . $this->namespacePrefix . "\\$project\\" . $this->namespaceModel . "\\" . $this->prefixModelItem . $modelName;
        return $className;
    }

    /**
     * Return the name of Form class.
     * Given the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. App\\Form\\Claim)
     */
    public function getFormClass(string $modelName): string
    {
        $className = $this->namespaceForm . "\\" . $modelName;
        return $className;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function regexPathModels() : string
    {
        return $this->regexPathModels;
    }

    /**
     * Return the method string to retrieve all elements / or filtering on them
     */
    public function getAll(): string
    {
        return $this->apiCollection . $this->getAll;
    }

    /**
     * Return the method string to retrieve all elements descriptions (it's possible to filter them as for getAll)
     */
    public function getAllToString(): string
    {
        return $this->apiCollection . $this->getAllToString;
    }

    /**
     * Return the method string to retrieve 1 element
     */
    public function getItem(): string
    {
        return $this->apiCollection . $this->get;
    }

    /**
     * Return the method string to update 1 element
     */
    public function getUpdateItem(): string
    {
        return $this->apiCollection . $this->update;
    }

    /**
     * Return the method string to count all elemements inside a collection
     */
    public function getCount(): string
    {
        return $this->apiCollection . $this->getCount;
    }

    /**
     * Return the method string to create an element
     */
    public function getCreate(): string
    {
        return $this->apiCollection . $this->create;
    }

    /**
     * Return the method string to delete an element
     */
    public function getDelete(): string
    {
        return $this->apiCollection . $this->delete;
    }

    /**
     * It looks for Models existent into included external bundles.
     * It uses ApiUtils in order to know where to search and what look for.
     *
     * @return array<mixed>
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
        $finder = new Finder();
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
