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

    private string $access_token;
    private string $endpoint;
    private string $clientkey;
    private string $expires_in;
    private string $token_type;


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
    public function getToken(): string
    {
        if ($this->access_token == '') {
            $this->refreshToken();
        }
        return $this->access_token;
    }

    public function getExpiresIn(): string
    {
        return $this->expires_in;
    }

    public function getTokenType(): string
    {
        return $this->token_type;
    }

    /**
     * Refresh the service ticket
     */
    public function refreshToken(): void
    {
        $rest = new RestClient($this->endpoint, $this->clientkey);
        $results = $rest->oauth2Principal();
        $this->access_token = $results->access_token;
        $this->expires_in = $results->expires_in;
        $this->token_type = $results->token_type;
    }
}
