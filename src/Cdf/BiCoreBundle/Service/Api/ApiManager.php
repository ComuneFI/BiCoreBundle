<?php

/**
 * API Manager version 3.0 / embedded into Bicore 
 * This service provides methods that help to manage API Rest communications.
 * Each API client can use it to reduce time to interact with API services
 */

namespace Cdf\BiCoreBundle\Service\Api;

use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Cdf\BiCoreBundle\Service\Api\Oauth2TokenService;

//TODO: interventi su APIManager
//TODO: Utilizzare la gestione del token oauth2 per la parte gestita da bicore


/**
 * It provides services to use API Rest.
 */
class ApiManager {

    private $project;
    private $oauth2Service;
    private $oauth2Enabled;
    private $map;    
    private $headerSelector;
    private $config;

    /**
     * Build an ApiManager instance
     */
    public function __construct( String $oauth2Parameter , Oauth2TokenService $oauth2Service) 
    {
        $this->map = array();
        $this->oauth2Service = $oauth2Service;
        $this->oauth2Enabled = false;
        if ( $oauth2Parameter == 1 ) {
            $this->oauth2Enabled = true;
        }
    }

    //Set the project name of client api
    public function setProjectName(string $projectName) {
        $this->project = $projectName;
    }

    //Get the project name of client api
    public function getProjectName() {
        return $this->project;
    }

    //Allow to set a config and an headerselector for the client api
    public function setApiClientConfigs($headerSelector, $config) {
        $this->headerSelector = $headerSelector;
        $this->config = $config;
    }

    private function isApiClientConfigs()
    {
        if ( !isset($this->headerSelector) || !isset($this->config) ) {
            throw new \Exception("Header selector not set or Config not set. Set them using setApiClientConfigs() method");
        }
    }

    //Return true if oauth2 is enabled
    private function isOauth2() {
        return $this->oauth2Enabled;
    }


    //generic setup
    public function setupCollection($collectionString) {
        $this->setApiController( $collectionString );
    }


    /**
     * Assure that exist an item mapped into ApiManager having the given collection
     * and project as key.
     */
    public function setApiController(String $collection, String $subcollection='')
    {
        //if not set it throws a new exception
        $this->isApiClientConfigs();

        $apiUtils = new ApiUtils($collection);
        $parametri = array('str' => $collection, 'primamaiuscola' => true);
        $outcome = StringUtils::toCamelCase($parametri);

        $subcollectionValue = '';
        if( !empty($subcollection) ) {
            $parametri2 = array('str' => $subcollection, 'primamaiuscola' => true);
            $subcollectionValue = StringUtils::toCamelCase($parametri2);
        }
        //NOT VALID FOR WINDOWS OS
        $apiClass = $apiUtils->getApiControllerClass( $this->project ,$outcome);

        $apiController = new $apiClass(null, $this->config, $this->headerSelector);
        if (!empty($this->map[$collection]['sub-api'])) {
            $subArray = $this->map[$collection]['sub-api'][$subcollection] = $subcollectionValue;
        } else {
            $subArray = [ $subcollection => $subcollectionValue ];
        }
        $this->map($collection, [
            'util' => $apiUtils,
            'api' => $apiController,
            'sub-api' => $subArray,
            ]
        );
    }

    private function map($collection, $tools) {
        if (!isset($this->map[$collection])) {
            $this->map[$collection] = $tools;
        }
    }

    /**
     * It checks if error is due to a Broken Pipe
     */
    private function isBrokenPipe(&$error) : bool
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
    private function setToken($api) {
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
    private function refreshToken($api, $token) {
        if ($this->isOauth2()) {
            $api->getConfig()->setAccessToken($token);

        }
    }


    /**
     * Return the proper method to be used
     */
    private function getMethod(String $collection, String $getMethod, String $subcollection = null): String 
    {
        $tools = $this->map[$collection];
        $method = $tools['util']->$getMethod();
        if ($subcollection != null) {
            $method .= $tools['sub-api'][$subcollection];
        }
        return $method;
    }

    /**
     * Return the amount of element existent for the given collection.
     */
    public function getCount(String $collection, String $subcollection = null) {
        $getCountmethod = $this->getMethod($collection, 'getCount', $subcollection);
        $tools = $this->map[$collection];
        //the array of arguments
        $arguments = array();
        if($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $amount = null;
        $token = $this->setToken($tools['api']);
        try {
                $amount = $tools['api']->$getCountmethod(...$arguments);
        }
        catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $amount = $tools['api']->$getCountmethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $amount;
    }

    /**
     * Get items existent for the given collection, filtered as requested.
     */
    public function getAll(String $collection, $offset = null, $limit = null, $sort = null, $condition = null, String $subcollection = null) {
        $tools = $this->map[$collection];
        $getAllmethod = $this->getMethod($collection, 'getAll', $subcollection);
        $results = [];
        //the array of arguments
        $arguments = array();
        if($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        array_push($arguments, $offset);
        array_push($arguments, $limit);
        array_push($arguments, $sort);
        array_push($arguments, $condition);

        $token = $this->setToken($tools['api']);
        try {
                
                $results = $tools['api']->$getAllmethod(...$arguments);

        }
        catch (\Exception $apiEx) {
            
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $results = $tools['api']->$getAllmethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $results;
    }

      /**
     * Get items existent for the given collection, filtered as requested.
     * subcollection contains the name of sub-collection if any
     * subcollectionVar contains the value of sub-collection if any
     */
    public function getItem(String $collection, $id, String $subcollection = null, $subcollectionVar = null) {
        $tools = $this->map[$collection];
        $getAllmethod = $this->getMethod($collection, 'getItem', $subcollection);
        $results = [];
        $arguments = array();
        if($subcollectionVar != null) {
            array_push($arguments, $subcollectionVar);
        }
        array_push($arguments, $id);
        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$getAllmethod(...$arguments);
        }
        catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $results = $tools['api']->$getAllmethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $results;
    }

    /**
     * Delete an existent item
     */
    public function deleteItem(String $collection, $id, String $subcollection = null) {
        $tools = $this->map[$collection];
        $deleteMethod = $this->getMethod($collection, 'getDelete', $subcollection);
        $results = [];
        $arguments = array();
        if($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        array_push($arguments, $id);
        $token = $this->setToken($tools['api']);
        try {
            $response = $tools['api']->$deleteMethod(...$arguments);
        }
        catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $response = $tools['api']->$deleteMethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $response;
    }


    /**
     * Create the requested object, using the given body item.
     */
    public function postCreate(String $collection, $body, String $subcollection = null)
    {
        $tools = $this->map[$collection];
        $createMethod = $this->getMethod($collection, 'getCreate', $subcollection);
        $results = [];
        $arguments = array();
        array_push($arguments, $body);
        if($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $token = $this->setToken($tools['api']);        
        try {
            $results = $tools['api']->$createMethod(...$arguments);
        }
        catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $results = $tools['api']->$createMethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $results;
    }


     /**
     * Create the requested object, using the given body item.
     */
    public function postUpdate(String $collection, $body,$id, String $subcollection = null)
    {
        $tools = $this->map[$collection];
        $createMethod = $this->getMethod($collection, 'getUpdateItem', $subcollection);
        $results = [];
        $arguments = array();
        array_push($arguments, $body);
        array_push($arguments, $id);
        if($subcollection != null) {
            array_push($arguments, $subcollection);
        }
        $token = $this->setToken($tools['api']);
        try {
            $results = $tools['api']->$createMethod(...$arguments);         
        }
        catch (\Exception $apiEx) {
            $this->refreshToken($tools['api'], $token);
            if ( $this->isBrokenPipe($apiEx)) {
                try {
                    $results = $tools['api']->$createMethod(...$arguments);
                }
                catch(\Exception $apiEx) {
                    throw $apiEx;    
                }      
            }
            else { 
                throw $apiEx;    
            }
        }
        return $results;
    }

    /**
     * It prepares entity values so that they can be used with types compliant with BiCoreBundle.
     * For example it transforms a date that arrive in string format into a DateTime.
     */
    public function setupApiValues($entityout)
    {
        $fieldMappings = $entityout::swaggerTypes();
        $formatMappings = $entityout::swaggerFormats();
        $setters = $entityout::setters();
        $getters = $entityout::getters();

        foreach ($fieldMappings as $fieldName => $fieldType) {
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
                $newvalue = $this->getValueOfData($fieldType, $formatMappings[$fieldName], $entityout->$getvalue());
                $entityout->$setvalue($newvalue);
        }
        return $entityout;
    }

    /**
     * Try to insert in automatic way the conversion to a BiCore known value
     */
    private function getValueOfData($fieldType, $formatType, $oldvalue)
    {
        $value = $oldvalue;

        switch($fieldType){
            case null: break;
            case 'int': $value = (int)$value; break;
            case 'double': $value = (double)$value; break;
            case 'bool': $value = (bool)$value; break;
            case 'string':
           
                if ($formatType == 'datetime') {
                    if (!empty($oldvalue)) {
                        $oldvalue = str_replace('/', '-', $oldvalue);
                        $time = strtotime($oldvalue);
                        $value = new \DateTime();
                        $value->setTimestamp($time);
                    }
                    else {
                        $value = null;
                    }
                }
             break;
        }
     
        return $value;
    }

    /**
     * Map first object transformed into the second where possible, 
     * attempting to map each field of first into field of the second.
     */
    public function mapData($modelEntity, $controllerItem)
    {
        $setters = $controllerItem::setters();
        $getters = $modelEntity::getters();

        foreach ($setters as $setterKey => $setterMethod) {
            if (isset($getters[$setterKey])) {
                $getMethod = $getters[$setterKey];
                $controllerItem->$setterMethod($modelEntity->$getMethod());
            }
        }

        return $controllerItem;
    }


}