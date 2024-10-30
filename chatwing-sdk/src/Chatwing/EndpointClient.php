<?php
/**
 * @author chatwing
 * @package Chatwing\SDK
 */

namespace Chatwing;

use Chatwing\Api\Action;
use Chatwing\Api\Response;
use Exception;

class EndpointClient extends Object
{
    private $endpoint = null;

    /**
     * @var Action
     */
    private $action = null;

    /**
     * Curl handler
     * @var resource
     */
    private $ch = null;

    /**
     * @var API|null
     */
    private $api = null;

    public function __construct($endpointUrl = '', $params = array())
    {
        if ($endpointUrl) {
            $this->setEndpoint($endpointUrl);
        }

        $this->setData($params);
    }

    /**
     * @param API $api
     * @return $this
     */
    public function setApiInstance($api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * @return API|null
     */
    public function getApiInstance()
    {
        return $this->api;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setEndpoint($url)
    {
        $this->endpoint = rtrim($url, '/');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param Action $action
     * @return $this
     */
    public function setAction(Action $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return null
     * @throws \Exception
     */
    protected function prepareConnection()
    {
        if (is_null($this->ch)) {
            $this->ch = curl_init();
            $action = $this->getAction();

            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

            if ($this->hasData('user_agent')) {
                curl_setopt($this->ch, CURLOPT_USERAGENT, $this->getData('user_agent'));
            }

            $queryUri = $action->getActionUri();
            $this->setData('client_id', $this->getApiInstance()->getClientId());
             $data = array_merge(
                 $this->getData(),
                 $action->getParams()
             ); // merge action data and current data

            if ($action->isAuthenticationRequired()) {
                $data = array_merge(
                    $data,
                    array('access_token' => $this->getApiInstance()->getAccessToken())
                );
            }
            $data = http_build_query($data);

            switch ($action->getType()) {
                case 'get':
                    $queryUri .= '?' . $data;
                    break;

                case 'post':
                    curl_setopt($this->ch, CURLOPT_POST, true);
                    curl_setopt(
                        $this->ch,
                        CURLOPT_POSTFIELDS,
                        $data
                    );
                    break;

                default:
                    throw new \Exception(__("Invalid HTTP Method", CHATWING_TEXTDOMAIN), 1);
                    break;
            }

            $queryUrl = $this->getFullEndpointApiUrl() . $queryUri;
            curl_setopt($this->ch, CURLOPT_URL, $queryUrl);
        }

        return $this->ch;
    }

    /**
     * Call the endpoint with set action, return a Response object
     * @return Response
     */
    public function call()
    {
        if (is_null($this->ch)) {
            $this->prepareConnection();
        }

        try {

            $result = curl_exec($this->ch);
            $responseStatus = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            curl_close($this->ch);
            $result = json_decode($result, true);
            if (!$result) {
                throw new \Exception("Invalid response", $responseStatus);
            }
            $result['http_code'] = $responseStatus;
        } catch (\Exception $e) {
            $result = array(
                'success' => false,
                'http_code' => $e->getCode(),
                'error' => array(
                    'message' => $e->getMessage()
                )
            );
        }

        $response = new Response($result);

        return $response;
    }

    /**
     * Reset handler
     * @return  void
     */
    public function reset()
    {
        $this->ch = null;
        $this->prepareConnection();
    }

    protected function getFullEndpointApiUrl()
    {
        return $this->getEndpoint() . '/api/' . $this->getApiInstance()->getAPIVersion() . '/';
    }
} 