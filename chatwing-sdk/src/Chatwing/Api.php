<?php
/**
 * @author  chatwing
 * @package Chatwing\SDK
 */

namespace Chatwing;

use Chatwing\Api\Action;
use Chatwing\Api\Response;
use Chatwing\Exception\ChatwingException;

class Api extends Object
{
    /**
     * API version
     *
     * @var int
     */
    private $apiVersion = 3;

    // private information
    private $accessToken = null;
    private $clientId = null;
    private $apiDomains = array(
        'development' => 'staging.chatwing.com',
        'production' => 'http://chatwing.com/'
    );

    /**
     * Indicate current environment
     *
     * @var string
     */
    private $environment = null;
    private $apiUrl = null;

    public function __construct($clientId, $accessToken = '', $apiVersion = 2)
    {
        $this->setClientId($clientId);
        $this->setAPIVersion($apiVersion);
        $this->setAccessToken($accessToken);
        $currentEnv = getenv('HTTP_CHATWING_ENV') ? getenv('HTTP_CHATWING_ENV') : CW_ENV_PRODUCTION;
        $this->setEnv($currentEnv);
        $this->setAgent(
            "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:30.0) Gecko/20100101 Firefox/30.0"
        ); // default user-agent
    }

    /**
     * Call the API action
     *
     * @param string $actionName
     * @param array $params
     *
     * @throws Exception\ChatwingException
     * @return \Chatwing\Api\Response
     */
    public function call($actionName, $params = array())
    {
        try {
            $action = new Action($actionName, $params);
            $client = new EndpointClient($this->getAPIServer());
            $client->setAction($action)->setApiInstance($this);
            $response = $client->call();
        } catch (\Exception $e) {
            $responseData = array(
                'success' => false,
                'error' => array(
                    'message' => $e->getMessage()
                )
            );

            $response = new Response($responseData);
        }
        return $response;
    }

    /**
     * Set application environment
     *
     * @param string $env
     *
     * @return $this
     * @throws ChatwingException
     */
    public function setEnv($env)
    {
        $this->environment = $env;
        $this->onEnvChange();
        return $this;
    }

    /**
     * Update settings after changing environment
     *
     * @return void
     */
    protected function onEnvChange()
    {
        $this->apiUrl = $this->apiDomains[$this->getEnv()];
    }

    /**
     * Get environment
     *
     * @return string
     */
    public function getEnv()
    {
        return $this->environment;
    }

    /**
     * Helper method to check if current environment is Development
     *
     * @return boolean
     */
    public function isDevelopment()
    {
        return $this->getEnv() == CW_ENV_DEVELOPMENT;
    }

    /**
     * Helper method to check if current environment is Production
     *
     * @return boolean
     */
    public function isProduction()
    {
        return $this->getEnv() == CW_ENV_PRODUCTION;
    }

    public function getAPIServer()
    {
        return isset($this->apiDomains[$this->getEnv()]) ? $this->apiDomains[$this->getEnv()] : '';
    }

    /**
     * Set the API access token
     *
     * @param $token
     *
     * @return $this
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Get current API access token
     *
     * @return array|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set API version
     *
     * @param int $version
     *
     * @return $this
     */
    public function setAPIVersion($version = 1)
    {
        $this->apiVersion = $version;
        return $this;
    }

    /**
     * Get API version
     *
     * @return int
     */
    public function getAPIVersion()
    {
        return $this->apiVersion;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }
}
