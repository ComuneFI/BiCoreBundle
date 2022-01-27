<?php

/**
 * This service provides methods that help to manage API Rest communications.
 */

namespace Cdf\BiCoreBundle\Service\Api;

use Cdf\BiCoreBundle\Utils\Api\RestClient;

/**
 * It provides services to manage token with WSO2 API services.
 */
class Oauth2TokenService
{

    private $access_token;
    private $endpoint;
    private $clientkey;


    /**
     * Build an EcmTokenService instance
     */
    public function __construct(string $endpoint, string $clientkey)
    {
        $this->access_token = '';
        $this->endpoint = $endpoint;
        $this->clientkey = $clientkey;
    }

    /**
     * Return the ticket if any, otherwise compute it
     */
    public function getToken()
    {
        if ($this->access_token == '') {
            $this->refreshToken();
        }
        return $this->access_token;
    }

    /**
     * Refresh the service ticket
     */
    public function refreshToken()
    {
        $rest = new RestClient($this->endpoint, $this->clientkey);
        $results = $rest->oauth2Principal();
        $this->access_token = $results->access_token;
        $this->expires_in = $results->expires_in;
        $this->token_type = $results->token_type;
    }
}
