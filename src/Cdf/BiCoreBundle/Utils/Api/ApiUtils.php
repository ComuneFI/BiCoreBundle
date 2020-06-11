<?php

namespace Cdf\BiCoreBundle\Utils\Api;


class ApiUtils
{
    private $getAll;
    private $getCount;
    private $apiCollection;

    public function __construct($apiCollection)
    {
        $this->apiCollection = $apiCollection;
        $this->getAll = "ControllerReadAll";
        $this->getCount = "ControllerCount";
    }

    /**
     * Return the method string to retrieve all elements / or filtering on them
     */
    public function getAll(): String 
    {
        return $this->apiCollection.$this->getAll;
    }

    /**
     * Return the method string to count all elemements inside a collection
     */
    public function getCount(): String 
    {
        return $this->apiCollection.$this->getCount;
    }

}
