<?php

/**
 * This is the class utility responsible to wrap REST API request management and
 * interact with needed services.
 */

namespace Cdf\BiCoreBundle\Utils\Api;

use GuzzleHttp\Client;

class RestClient
{


    public function __construct(private readonly string $endpoint, private readonly string $clientkey)
    {
    }

    private function tokenize(): mixed
    {

        $body = [];
        $body['grant_type'] = 'client_credentials';

        $client = new Client();
        return $client->request('POST', $this->endpoint, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => '*/*',
                'Authorization' => 'Basic '.$this->clientkey,
            ],
            'form_params' => ['grant_type' => 'client_credentials'],
        ]);
    }

    /**
     * Perform a call to the principal method of token acquisition
     */
    public function oauth2Principal(): mixed
    {
        $response = $this->tokenize();
        $content = $response->getBody();
        return json_decode($content);
    }
}
