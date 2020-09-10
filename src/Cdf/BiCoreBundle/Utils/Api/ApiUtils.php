<?php

namespace Cdf\BiCoreBundle\Utils\Api;

use Symfony\Component\Finder\Finder;

class ApiUtils {

    private $getAll;
    private $getCount;
    private $create;
    private $apiCollection;

    //namespaces
    private static $namespacePrefix = 'Swagger';
    private static $namespaceApi = 'Api';
    private static $namespaceModel = "Model";
    private static $namespaceForm = "App\\Form";
    //suffix and prefix
    private static $suffixApiController = 'Api';
    private static $prefixControllerModelItem = "DaosRow";
    private static $prefixModelItem= "DaosRow";


    //TODO: check these variables
    private static $apiBundlesPath = '../../vendor/fi';    
   
    //TODO: evaluate to move this variable into configs
    private static $regexPathModels = '/Swagger\\\(.*)\\\Model\\\DaosRow/';

    public function __construct($apiCollection) {
        $this->apiCollection = lcfirst($apiCollection);
        $this->getAll = "ControllerReadAll";
        $this->getCount = "ControllerCount";
        $this->create = "ControllerCreate";
        $this->delete = "ControllerDeleteItem";
        $this->get = "ControllerReadItem";
        $this->update = "ControllerUpdateItem";
        $this->getAllToString = "ControllerReadAllToString";
    }

    /**
     * Return path where core bundle will look for api models
     */
    public function bundlesPath() {
        return self::$apiBundlesPath;
    }

    /**
     * Return namespace prefix for api external bundles, i.e. Swagger
     */
    public static function namespacePrefix() {
        return self::$namespacePrefix;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public static function namespaceModels() {
        return self::$namespaceModel;
    }

    /**
     * Return the name of Api Controller.
     * Given the project name (i.e. Insurance) and the collection name (i.e. Claims) it returns the complete path of API controller
     * class (i.e. \\Swagger\\Insurance\\Api\\ClaimsApi)
     */
    public static function getApiControllerClass($project, $entityName):String 
    {
        $className = "\\".self::$namespacePrefix."\\$project\\".self::$namespaceApi."\\$entityName".self::$suffixApiController;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ControllersItemClaim)
     */
    public static function getModelControllerClass($project, $modelName):String 
    {
        $className = "\\".self::$namespacePrefix."\\$project\\".self::$namespaceModel."\\".self::$prefixControllerModelItem.$modelName;
        return $className;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. \\Swagger\\Insurance\\Model\\ModelsClaim)
     */
    public static function getModelClass($project, $modelName):String 
    {
        $className = "\\".self::$namespacePrefix."\\$project\\".self::$namespaceModel."\\".self::$prefixModelItem.$modelName;
        return $className;
    }

    /**
     * Return the name of Form class.
     * Given the model name (i.e. Claim) it returns the complete path of API Model controller item
     * class (i.e. App\\Form\\Claim)
     */
    public static function getFormClass($modelName):String 
    {
        $className = self::$namespaceForm."\\".$modelName;
        return $className;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function regexPathModels() {
        return self::$regexPathModels;
    }

    /**
     * Return the method string to retrieve all elements / or filtering on them
     */
    public function getAll(): String {
        return $this->apiCollection . $this->getAll;
    }

    /**
     * Return the method string to retrieve all elements descriptions (it's possible to filter them as for getAll)
     */
    public function getAllToString(): String {
        return $this->apiCollection . $this->getAllToString;
    }

    /**
     * Return the method string to retrieve 1 element
     */
    public function getItem(): String {
        return $this->apiCollection . $this->get;
    }

    /**
     * Return the method string to update 1 element
     */
    public function getUpdateItem(): String {
        return $this->apiCollection . $this->update;
    }

    /**
     * Return the method string to count all elemements inside a collection
     */
    public function getCount(): String {
        return $this->apiCollection . $this->getCount;
    }

    /**
     * Return the method string to create an element 
     */
    public function getCreate(): String {
        return $this->apiCollection . $this->create;
    }

    /**
     * Return the method string to delete an element 
     */
    public function getDelete(): String {
        return $this->apiCollection . $this->delete;
    }

    /**
     * It looks for Models existent into included external bundles.
     * It uses ApiUtils in order to know where to search and what look for.
     */
    public function apiModels(): array {
        //where to look for
        $path = self::bundlesPath();
        $regex = self::regexPathModels();
        //what to look for   
        $models = array();
        $finder = new Finder;
        $iter = new \hanneskod\classtools\Iterator\ClassIterator($finder->in($path));

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
