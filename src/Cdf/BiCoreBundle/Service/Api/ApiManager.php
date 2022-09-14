<?php

/**
 * API Manager version 3.0 / embedded into Bicore
 * This service provides methods that help to manage API Rest communications.
 * Each API client can use it to reduce time to interact with API services
 */

namespace Cdf\BiCoreBundle\Service\Api;

use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\BiCoreBundle\Utils\Api\ApiManagerUtil;
use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Cdf\BiCoreBundle\Service\Api\Oauth2TokenService;
use \Exception;

/**
 * It provides services to use API Rest.
 */
class ApiManager
{

    private string $project;
    private Oauth2TokenService $oauth2Service;
    private bool $oauth2Enabled;
    private mixed $map;
    private object $headerSelector;
    private object $config;

    private ApiManagerUtil $apiManUtil;

    /**
     * Build an ApiManager instance
     */
    public function __construct(String $oauth2Parameter, Oauth2TokenService $oauth2Service)
    {
        $this->map = array();
        $this->oauth2Service = $oauth2Service;
        $this->oauth2Enabled = false;
        if ($oauth2Parameter == 1) {
            $this->oauth2Enabled = true;
        }
        $this->apiManUtil = new ApiManagerUtil();
    }

    //Set the project name of client api
    public function setProjectName(string $projectName): void
    {
        $this->project = $projectName;
    }

    //Get the project name of client api
    public function getProjectName(): string
    {
        return $this->project;
    }

    //Allow to set a config and an headerselector for the client api
    public function setApiClientConfigs(object $headerSelector, object $config): void
    {
        $this->headerSelector = $headerSelector;
        $this->config = $config;
    }

    private function isApiClientConfigs(): void
    {
        if (!isset($this->headerSelector) || !isset($this->config)) {
            throw new Exception("Header selector not set or Config not set. Set them using setApiClientConfigs() method");
        }
    }

    //Return true if oauth2 is enabled
    private function isOauth2(): bool
    {
        return $this->oauth2Enabled;
    }


    //generic setup
    public function setupCollection(string $collectionString): void
    {
        $this->setApiController($collectionString);
    }


    /**
     * Assure that exist an item mapped into ApiManager having the given collection
     * and project as key.
     */
    public function setApiController(String $collection, String $subcollection = ''): void
    {
        //if not set it throws a new exception
        $this->isApiClientConfigs();

        $apiUtils = new ApiUtils($collection);
        $parametri = array('str' => $collection, 'primamaiuscola' => true);
        $outcome = StringUtils::toCamelCase($parametri);

        $subcollectionValue = '';
        if (!empty($subcollection)) {
            $parametri2 = array('str' => $subcollection, 'primamaiuscola' => true);
            $subcollectionValue = StringUtils::toCamelCase($parametri2);
        }
        //NOT VALID FOR WINDOWS OS
        $apiClass = $apiUtils->getApiControllerClass($this->project, $outcome);

        $apiController = new $apiClass(null, $this->config, $this->headerSelector);
        $subArray = [ $subcollection => $subcollectionValue ];
        if (!empty($this->map[$collection]['sub-api'])) {
            $subArray = $this->map[$collection]['sub-api'][$subcollection] = $subcollectionValue;
        }
        $this->map($collection, [
            'util' => $apiUtils,
            'api' => $apiController,
            'sub-api' => $subArray,
            ]);
    }

    private function map(string $collection, mixed $tools): void
    {
        if (!isset($this->map[$collection])) {
            $this->map[$collection] = $tools;
        }
    }

    /**
     * It checks if error is due to a Broken Pipe
     */
    private function isBrokenPipe(Exception &$error) : bool
    {
        $outcome = false;
        if (strpos($error->getMessage(), 'broken pipe') !== false) {
            $outcome = true;
        }
        return $outcome;
    }

    /**
     * Set the token managed by wso2TokenService into api-service
     * If token is already set, then it doesn't change it
     */
    private function setToken(mixed $api): string
    {
        $token = '';
        if ($this->isOauth2()) {
            $token = $this->oauth2Service->getToken();
            if (empty($api->getConfig()->getAccessToken())) {
                $this->refreshToken($api, $token);
            }
        }
        return $token;
    }

    /**
     * Set the token managed by wso2TokenService into api-service
     * If token is already set, it replaces it
     */
    private function refreshToken(mixed $api, string $token): void
    {
        if ($this->isOauth2()) {
            $api->getConfig()->setAccessToken($token);
        }
    }


    /**
     * Return the proper method to be used
     */
    private function getMethod(String $collection, String $getMethod, String $subcollection = null): string
    {
        $tools = $this->map[$collection];
        $method = $tools['util']->$getMethod();
        if ($subcollection != null) {
            $method .= $tools['sub-api'][$subcollection];
        }
        return $method;
    }


    /**
     * Retry one more time if exception is for broken pipes
     */
    private function retry(Exception $apiEx, object $object, string $method, mixed $args): mixed
    {
        if ($this->isBrokenPipe($apiEx)) {
            return $object->$method(...$args);
        }
        throw $apiEx;
    }

    /**
     * Return the amount of element existent for the given collection.
     */
    public function getCount(String $collection, String $subcollection = null): mixed
    {
        $getCountmethod = $this->getMethod($collection, 'getCount', $subcollection);
        $tools = $this->map[$collection];
        //the array of arguments
        $arguments = array();
        if ($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $amount = null;
        $token = $this->setToken($tools['api']);
        try {
                $amount = $tools['api']->$getCountmethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $amount = $this->retry($apiEx, $tools['api'], $getCountmethod, $arguments);
        }
        return $amount;
    }

    /**
     * Get items existent for the given collection, filtered as requested.
     */
    public function getAll(string $collection, ?int $offset, ?int $limit, mixed $sort = null, ?string $condition, string $subcollection = null): mixed
    {
        $tools = $this->map[$collection];
        $getAllmethod = $this->getMethod($collection, 'getAll', $subcollection);
        $results = [];
        //the array of arguments
        $arguments = array();
        if ($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        array_push($arguments, $offset);
        array_push($arguments, $limit);
        array_push($arguments, $sort);
        array_push($arguments, $condition);

        $token = $this->setToken($tools['api']);
        try {
                $results = $tools['api']->$getAllmethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $results = $this->retry($apiEx, $tools['api'], $getAllmethod, $arguments);
        }
        return $results;
    }

    /**
     * Get items existent for the given collection in "to-string" format, filtered as requested.
     */
    public function getAllToString(string $collection, ?int $offset, ?int $limit, mixed $sort = null, ?string $cond, String $sub = null): mixed
    {
        $tools = $this->map[$collection];
        $getMethod = $this->getMethod($collection, 'getAllToString', $sub);
        $results = [];
        //the array of arguments
        $arguments = array();
        if ($sub != null) {
            array_push($arguments, $sub);
        }
        array_push($arguments, $offset);
        array_push($arguments, $limit);
        array_push($arguments, $sort);
        array_push($arguments, $cond);

        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$getMethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $results = $this->retry($apiEx, $tools['api'], $getMethod, $arguments);
        }
        return $results;
    }

      /**
     * Get items existent for the given collection, filtered as requested.
     * subcollection contains the name of sub-collection if any
     * subcollectionVar contains the value of sub-collection if any
     */
    public function getItem(String $collection, int $id, String $subcollection = null, string $subcollectionVar = null): mixed
    {
        $tools = $this->map[$collection];
        $getAllmethod = $this->getMethod($collection, 'getItem', $subcollection);
        $results = [];
        $arguments = array();
        if ($subcollectionVar != null) {
            array_push($arguments, $subcollectionVar);
        }
        array_push($arguments, $id);
        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$getAllmethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $results = $this->retry($apiEx, $tools['api'], $getAllmethod, $arguments);
        }
        return $results;
    }

    /**
     * Delete an existent item
     */
    public function deleteItem(String $collection, int $id, String $subcollection = null): mixed
    {
        $tools = $this->map[$collection];
        $deleteMethod = $this->getMethod($collection, 'getDelete', $subcollection);
        $arguments = array();
        if ($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        array_push($arguments, $id);
        $token = $this->setToken($tools['api']);
        try {
            $response = $tools['api']->$deleteMethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $response = $this->retry($apiEx, $tools['api'], $deleteMethod, $arguments);
        }
        return $response;
    }


    /**
     * Create the requested object, using the given body item.
     */
    public function postCreate(String $collection, mixed $body, String $subcollection = null): mixed
    {
        $tools = $this->map[$collection];
        $createMethod = $this->getMethod($collection, 'getCreate', $subcollection);
        $results = [];
        $arguments = array();
        array_push($arguments, $body);
        if ($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$createMethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $results = $this->retry($apiEx, $tools['api'], $createMethod, $arguments);
        }
        return $results;
    }


     /**
     * Create the requested object, using the given body item.
     */
    public function postUpdate(String $collection, object $body, int $id, String $subcollection = null): mixed
    {
        $tools = $this->map[$collection];
        $createMethod = $this->getMethod($collection, 'getUpdateItem', $subcollection);
        $results = [];
        $arguments = array();
        array_push($arguments, $body);
        array_push($arguments, $id);
        if ($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$createMethod(...$arguments);
        } catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            $results = $this->retry($apiEx, $tools['api'], $createMethod, $arguments);
        }
        return $results;
    }

    /**
     * It prepares entity values so that they can be used with types compliant with BiCoreBundle.
     * For example it transforms a date that arrive in string format into a DateTime.
     * @deprecated: evaluate to migrate on ApiManagerUtil
     */
    public function setupApiValues(mixed $entityout): mixed
    {
        return $this->apiManUtil->setupApiValues($entityout);
    }

    /**
     * Map first object transformed into the second where possible,
     * attempting to map each field of first into field of the second.
     * @deprecated: evaluate to migrate on ApiManagerUtil
     */
    public function mapData(object $modelEntity, object $controllerItem): mixed
    {
        return $this->apiManUtil->mapData($modelEntity, $controllerItem);
    }
}
