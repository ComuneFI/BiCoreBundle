<?php

/**
 * This is the class utility responsible to wrap REST API request management and
 * interact with needed services.
 */

namespace Cdf\BiCoreBundle\Utils\Api;


class RestClient {


    //parameters
    private $endpoint;
    private $clientkey;

    public function __construct(string $endpoint, string $clientkey) {
        $this->endpoint = $endpoint;
        $this->clientkey = $clientkey;
    }

    private function tokenize() {

        $body = array();
        $body['grant_type'] = 'client_credentials';

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', $this->endpoint, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => '*/*',
                'Authorization' => 'Basic '.$this->clientkey,
            ],
            'form_params' => ['grant_type' => 'client_credentials'],
        ]);
        return $res;
    }

    /**
     * Perform a call to the principal method of token acquisition
     */
    public function oauth2Principal()
    {
        $response = $this->tokenize();
        $content = $response->getBody();
        $data = json_decode($content);
        return $data;
    }

}
