<?php

namespace Cdf\BiCoreBundle\Utils\Api;

use Symfony\Component\Finder\Finder;

class ApiUtils {

    private $getAll;
    private $getCount;
    private $create;
    private $apiCollection;
    //TODO: check these variables
    private static $apiBundlesPath = '../../vendor/fi';
    private static $namespacePrefix = 'Swagger\\';
    private static $namespaceModels = '\\Model\\Models';
    private static $namespaceControllersItem = '\\Model\\ControllersItem';
    private static $regexPathModels = '/Swagger\\\(.*)\\\Model\\\Models/';

    public function __construct($apiCollection) {
        $this->apiCollection = $apiCollection;
        $this->getAll = "ControllerReadAll";
        $this->getCount = "ControllerCount";
        $this->create = "ControllerCreate";
        $this->delete = "ControllerDeleteItem";
        $this->get = "ControllerReadItem";
        $this->update = "ControllerUpdateItem";
    }

    /**
     * Return path where core bundle will look for api models
     */
    public function bundlesPath() {
        return self::$apiBundlesPath;
    }

    /**
     * Return namespace prefix for api external bundles, i.e. Swagger\\\
     */
    public function namespacePrefix() {
        return self::$namespacePrefix;
    }

    /**
     * Return namespace component for api controller items of external bundles, i.e. \\\Model\\\ControllersItem*
     */
    public function namespaceControllersItem() {
        return self::$namespaceControllersItem;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \\\Model\\\Models*
     */
    public function namespaceModels() {
        return self::$namespaceModels;
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
