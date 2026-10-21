<?php

namespace Cdf\BiCoreBundle\Utils\Api;

use Symfony\Component\Finder\Finder;
use ReflectionClass;

/**
 * API Utilities for managing API models and controllers in Symfony 6+
 * 
 * Replaces hanneskod/classtools with native PHP reflection
 */
class ApiUtils
{
    private readonly string $getAll;
    private readonly string $getCount;
    private readonly string $create;
    private string $apiCollection;
    
    // Namespaces
    private string $namespacePrefix;
    private string $namespaceApi;
    private string $namespaceModel;
    private string $namespaceForm;
    
    // Suffix and prefix
    private string $suffixApiController;
    private string $prefixControllerModelItem;
    private string $prefixModelItem;
    
    private readonly string $delete;
    private readonly string $get;
    private readonly string $update;
    private readonly string $getAllToString;
    
    // Regex for finding models
    private string $regexPathModels;
    
    private string $rootDir;

    public function __construct(string $apiCollection = null)
    {
        if (isset($apiCollection)) {
            $this->apiCollection = lcfirst($apiCollection);
        }
        $this->getAll = 'ControllerReadAll';
        $this->getCount = 'ControllerCount';
        $this->create = 'ControllerCreate';
        $this->delete = 'ControllerDeleteItem';
        $this->get = 'ControllerReadItem';
        $this->update = 'ControllerUpdateItem';
        $this->getAllToString = 'ControllerReadAllToString';

        $this->initVariables();
    }

    private function initVariables(): void
    {
        $this->namespacePrefix = 'Swagger';
        $this->namespaceApi = 'Api';
        $this->namespaceModel = 'Model';
        $this->namespaceForm = 'App\\Form';
        $this->suffixApiController = 'Api';
        $this->prefixControllerModelItem = 'DaosRow';
        $this->prefixModelItem = 'DaosRow';
        $this->regexPathModels = '/Swagger\\\(.*)\\\Model\\\DaosRow/';
    }

    public function setRootDir(string $rootDir): void
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return path where core bundle will look for api models
     */
    public function bundlesPath(): string
    {
        return $this->rootDir . '/fi';
    }

    /**
     * Return namespace prefix for api external bundles, i.e. Swagger
     */
    public function namespacePrefix(): string
    {
        return $this->namespacePrefix;
    }

    /**
     * Return namespace component for api models of external bundles, i.e. \Model\Models*
     */
    public function namespaceModels(): string
    {
        return $this->namespaceModel;
    }

    /**
     * Return the project Root path string.
     * In case of project Insurance, it will return the string "\\Swagger\\Insurance\\"
     */
    public function getProjectRoot(string $projectName): string
    {
        return '\\' . $this->namespacePrefix() . '\\' . $projectName . '\\';
    }

    /**
     * Return the name of Api Controller.
     * Given the project name (i.e. Insurance) and the collection name (i.e. Claims) 
     * it returns the complete path of API controller class (i.e. \\Swagger\\Insurance\\Api\\ClaimsApi)
     */
    public function getApiControllerClass(string $project, string $entityName): string
    {
        return '\\' . $this->namespacePrefix . '\\' . $project . '\\' . $this->namespaceApi 
            . '\\' . $entityName . $this->suffixApiController;
    }

    /**
     * Return the name of Model Controller.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) 
     * it returns the complete path of API Model controller item class 
     * (i.e. \\Swagger\\Insurance\\Model\\ControllersItemClaim)
     */
    public function getModelControllerClass(string $project, string $modelName): string
    {
        return '\\' . $this->namespacePrefix . '\\' . $project . '\\' . $this->namespaceModel 
            . '\\' . $this->prefixControllerModelItem . $modelName;
    }

    /**
     * Return the name of Model.
     * Given the project name (i.e. Insurance) and the model name (i.e. Claim) 
     * it returns the complete path of API Model class (i.e. \\Swagger\\Insurance\\Model\\ModelsClaim)
     */
    public function getModelClass(string $project, string $modelName): string
    {
        return '\\' . $this->namespacePrefix . '\\' . $project . '\\' . $this->namespaceModel 
            . '\\' . $this->prefixModelItem . $modelName;
    }

    /**
     * Return the name of stored collection
     */
    public function getApiCollection(): string
    {
        return $this->apiCollection;
    }

    /**
     * Return the name of Form class.
     * Given the model name (i.e. Claim) it returns the complete path of Form class
     * (i.e. App\\Form\\Claim)
     */
    public function getFormClass(string $modelName): string
    {
        return $this->namespaceForm . '\\' . $modelName;
    }

    /**
     * Return regex pattern for finding models
     */
    public function regexPathModels(): string
    {
        return $this->regexPathModels;
    }

    /**
     * Return the method string to retrieve all elements
     */
    public function getAll(): string
    {
        return $this->apiCollection . $this->getAll;
    }

    /**
     * Return the method string to retrieve all elements descriptions
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
     * Return the method string to count all elements inside a collection
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
     * Look for Models existent into included external bundles using Symfony Finder.
     * Replaces hanneskod/classtools with native PHP Reflection.
     *
     * @return array<int, string>
     */
    public function apiModels(): array
    {
        $path = $this->bundlesPath();
        $regex = $this->regexPathModels();
        $models = [];

        // Check if path exists
        if (!is_dir($path)) {
            return $models;
        }

        try {
            $finder = new Finder();
            $phpFiles = $finder->files()
                ->in($path)
                ->name('*.php')
                ->followLinks();

            foreach ($phpFiles as $file) {
                $className = $this->extractClassNameFromFile((string) $file);
                
                if ($className !== null) {
                    $matches = [];
                    if (preg_match($regex, $className, $matches)) {
                        if (count($matches) > 0) {
                            $models[] = $matches[1] . '.' . substr($className, strlen($matches[0])) . ' (API)';
                        }
                    }
                }
            }
        } catch (\Exception) {
            // Log error or handle gracefully
            return $models;
        }

        return $models;
    }

    /**
     * Extract fully qualified class name from a PHP file.
     * Uses a simple regex to find namespace and class name without loading the file.
     *
     * @param string $filePath Path to PHP file
     * @return string|null Fully qualified class name or null if not found
     */
    private function extractClassNameFromFile(string $filePath): ?string
    {
        $content = @file_get_contents($filePath);
        
        if ($content === false) {
            return null;
        }

        // Extract namespace
        $namespace = '';
        if (preg_match('/^namespace\s+(.+?);/m', $content, $nsMatches)) {
            $namespace = trim($nsMatches[1]);
        }

        // Extract class name (supports class, interface, trait)
        $patterns = [
            '/^class\s+(\w+)/m',
            '/^interface\s+(\w+)/m',
            '/^trait\s+(\w+)/m',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $classMatches)) {
                $className = trim($classMatches[1]);
                
                if ($namespace) {
                    return $namespace . '\\' . $className;
                }
                return $className;
            }
        }

        return null;
    }
}