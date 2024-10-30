<?php

/**
 * @author  chatwing
 * @package Chatwing\SDK
 */

namespace Chatwing;

use Chatwing\Exception\ChatwingException;

class Chatbox extends Object
{
    /**
     * @var Api
     */
    protected $api;
    protected $id = null;
    protected $key = null;
    protected $alias = null;
    protected $params = array();
    protected $secret = null;

    protected $baseUrl = null;
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function getBaseUrl()
    {  

        $params = array('id' => $this->getId());
        $chatboxData =  array();
        if (function_exists( 'wp_get_current_user' ) ) {
          $user = wp_get_current_user();
          $params["wp_user"] = $user->data;
          $avatar = get_avatar_data($user->data->ID);
          $params["wp_user"]->avatar = $avatar["url"];
          $params["plugin"] = "wordpress";
        }
        if (is_null($this->baseUrl)) {
          // get chatbox data
          $response = $this->api->call('chatbox/read',$params);
          if(empty($response)) {
              throw new ChatwingException(__("Invalid chatbox ID", CHATWING_TEXTDOMAIN));
          } else {
              $chatboxData = $response->get('data');
              $this->baseUrl = $chatboxData['urls']['full'];
          }
            
        }
        $this->baseUrl.="?plugin=wordpress";
        if (!empty($chatboxData)) {
          if (!empty($chatboxData["json"]["wordpressSynchronize"]) &&
              ($chatboxData["json"]["wordpressSynchronize"] ==  true || $chatboxData["json"]["wordpressSynchronize"] == "true")) {
              
            $this->baseUrl.="&client_id=wordpress";
            if (!empty($chatboxData["chatuser"]) && !empty($chatboxData["chatuser"]["access_token"])) {
              $this->baseUrl.="&access_token=".$chatboxData["chatuser"]["access_token"];
            }

            /*$this->baseUrl.="&client_id=wordpress&hide_login=true";
            if (!empty($chatboxData["chatuser"]) && !empty($chatboxData["chatuser"]["access_token"])) {
              $this->baseUrl.="&access_token=".$chatboxData["chatuser"]["access_token"];
            } else {
              $this->baseUrl.="&logout=true";
            }*/
          }      
        }
        return  $this->baseUrl;
    }

    /**
     * Return chatbox's url
     *
     * @throws ChatwingException If no alias or chatbox key is set
     * @return string
     */
    public function getChatboxUrl()
    {
        if (!$this->getId()) {
            throw new ChatwingException(__("Chatbox ID is not set!", CHATWING_TEXTDOMAIN));
        }

        $chatboxUrl = $this->getBaseUrl();

        if (!empty($this->params)) {
            if ($this->getSecret()) {
                $this->getEncryptedSession(); // call this method to create encrypted session
            }
            $chatboxUrl .= '&' . http_build_query($this->params);
        }

        return $chatboxUrl;
    }

    /**
     * Return chatbox iframe code
     * @throws ChatwingException If no alias or chatbox key is set
     * @return string
     */
    public function getIframe()
    {
        $url = $this->getChatboxUrl();
        return '<iframe src="'. $url .'" height="'. $this->getData('height') .'" width="'. $this->getData('width') .'" frameborder="0"></iframe>';
    }

    /**
     * Set chatbox ID
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set chatbox key
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * get the current chatbox's key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set chatbox alias
     *
     * @param string $alias
     *
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Get current chatbox's alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set chatbox's parameter
     *
     * @param string|array $key 
     * @param string $value
     *
     * @return $this
     */
    public function setParam($key, $value = '')
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setParam($k, $v);
            }
        } else {
            $this->params[$key] = $value;
        }
        return $this;
    }

    /**
     * Get parameter
     * @param  string $key     
     * @param  null|mixed $default 
     * @return mixed|null
     */
    public function getParam($key = '', $default = null)
    {
        if (empty($key)) {
            return $this->params;
        }
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    /**
     * Get all parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set chatbox secret key
     * @param $s
     *
     * @return $this
     */
    public function setSecret($s)
    {
        $this->secret = $s;
        return $this;
    }

    /**
     * Get secret
     * @return string|null
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get encrypted session
     * @return string
     */
    public function getEncryptedSession()
    {
        if (isset($this->params['custom_session'])) {
            $customSession = $this->params['custom_session'];
            if (is_string($customSession)) {
                return $customSession;
            }

            if (is_array($customSession) && !empty($customSession) && $this->getSecret()) {
                $session = new CustomSession();
                $session->setSecret($this->getSecret());
                $session->setData($customSession);
                $this->setParam('custom_session', $session->toEncryptedSession());

                return $this->getParam('custom_session');
            }

            unset($this->params['custom_session']);
        }

        return false;
    }
} 