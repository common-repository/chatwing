<?php namespace Chatwing\IntegrationPlugins\WordPress;

/**
 * @package Chatwing\IntegrationPlugins\Wordpress
 * @author chatwing
 */
use Chatwing\Encryption\DataEncryptionHelper;
use Chatwing\Application as Chatwing;

class Application extends PluginBase
{
    protected function init()
    {
        if (!defined('CHATWING_ENCRYPTION_KEY')) {
            $this->onPluginActivation();
            $this->getModel()->saveAccessToken('');
            return;
        }

        DataEncryptionHelper::setEncryptionKey(CHATWING_ENCRYPTION_KEY);
        Chatwing::getInstance()->bind('access_token', $this->getModel()->getAccessToken());
        add_shortcode('chatwing', array('Chatwing\\IntegrationPlugins\\WordPress\\ShortCode', 'render'));
    }

    protected function registerHooks()
    {
        register_activation_hook(CHATWING_PLG_MAIN_FILE, array($this, 'onPluginActivation'));
        if ($this->getModel()->hasAccessToken()) {
            add_action('widgets_init', function(){
                register_widget('Chatwing\\IntegrationPlugins\\WordPress\\Widget');
            });
      add_action( 'rest_api_init', array($this, 'add_authenticate_api') );
        }

    }
  function add_authenticate_api(){
    register_rest_route( 'chatwing/v1', '/oauth/authenticate', array(
      'methods' => 'POST',
      'callback' => array($this, 'authenticate_api_callback'),
      'args' => array(
          'id' => array(
          'validate_callback' => function($param, $request, $key) {
              return is_numeric( $param );
          }
        )
      )
    ));
  }
  function authenticate_api_callback($data) {
    $params = ["app_id", "key_secret", "username", "password", "chatbox_id"];
    $ressult = array('error' => '','data' => null, 'code' => false);
    for($i=0;$i<count($params);$i++) {
      if (empty($data[$params[$i]])) {
        $ressult["error"] = $params[$i]." is required";
        return $ressult;
      }
    }
    if ($this->getModel()->getOption("keysecret") != $data["key_secret"]) {
      $ressult["error"] = "key_secret not found";
      return $ressult;
    }
    if ($this->getModel()->getOption("app_id") != $data["app_id"]) {
      $ressult["error"] = "app_id not found";
      return $ressult;
    }
    $app_id = $data["app_id"];
    $key_secret = $data["key_secret"];
    $user_login = $data["username"];
    $user_password = $data["password"];

    //$boxId = $this->idChatBoxApp($app_id);
    $params = array('id' => $data["chatbox_id"]);
    $creds = array(
      'user_login'    => $user_login,
      'user_password' => $user_password,
      'remember'      => false
    );
    $user = wp_signon( $creds, false );
    if (!empty($user->data)) {
      $params["wp_user"] = $user->data;
      $params["plugin"] = "wordpress";
      $avatar = get_avatar_data($user->data->ID);
      $params["wp_user"]->avatar = $avatar["url"];
      //$params["access_token"] = Chatwing::getInstance()->get('api')->getAccessToken();
      //$params["client_id"] = "wordpress"; 
      //return $params;
      $response = Chatwing::getInstance()->get('api')->call('chatbox/read', $params);

      if ($response->isSuccess()) {
        $dataChatBox = $response->get('data');
        if (!empty($dataChatBox["chatuser"])) {
          $ressult["data"] = array('access_token' => $dataChatBox["chatuser"]["access_token"],'client_id' => 'wordpress');
          $ressult["code"] = true;
          return $ressult;
        } else {
          $ressult["error"] = "Fail";
          return $ressult;
        }   
      }
    } else {
      $ressult["error"] = $user->errors;
      return $ressult;
    }
  }
  function idChatBoxApp($app_id) {
    $boxList = $this->getModel()->getBoxList();
    foreach ($boxList as $box) {
      if ($box['app_id'] == $app_id) {
        return $box['id'];
      }
    }
    return null;
  }
    protected function registerFilters()
    {
    add_filter('login_redirect', array($this, 'handleUserLogin'), 10, 3);
    }

    public function onPluginActivation()
    {
        // check if we have encryption key
        $filePath = CHATWING_PATH . '/key.php';
        if (!file_exists($filePath)) {
            $encryptionKey = DataEncryptionHelper::generateKey();
            $n = file_put_contents($filePath, "<?php define('CHATWING_ENCRYPTION_KEY', '{$encryptionKey}');?>");
            if ($n) {
                require $filePath;
            } else {
                die("Cannot create encryption key.");
            }
        }
    }

    public function run()
    {
        parent::run();

        if (is_admin()) {
            $admin = new Admin($this->getModel());
            $admin->run();
        }
    }


    /**
     * @param $redirectUrl
     * @param string $requestedRedirectUrl
     * @param \WP_Error|\WP_User $user
     * @return string
     */
    public function handleUserLogin($redirectUrl, $requestedRedirectUrl = '', $user = null)
    {
        $targetURL = $redirectUrl;

        if ($user instanceof \WP_User && $user->ID) {
            // login successfully
            if (!empty($requestedRedirectUrl)) {
                $targetURL = $requestedRedirectUrl;
            }

            $targetURL = urldecode($targetURL);
            $parsedData = parse_url($targetURL);
            if (!empty($parsedData['host'])
                && in_array($parsedData['host'], array('chatwing.com', 'staging.chatwing.com'))
            ) {

                // try to get the chatbox alias
                // then determine if we have custom redirection URL
                $parts = isset($parsedData['path']) ? array_filter(explode('/', $parsedData['path'])) : array();
                if (count($parts) > 1) {
                    $chatboxKey = $parts[2];
                    $boxId = null;
                    $boxList = $this->getModel()->getBoxList();
                    foreach ($boxList as $box) {
                        if ($box['key'] == $chatboxKey) {
                            $boxId = $box['id'];
                            break;
                        }
                    }

                    if ($boxId) {
                        $response = Chatwing::getInstance()->get('api')->call('chatbox/read', array('id' => $boxId));
                        if ($response->isSuccess()){
                            $chatboxData = $response->get('data');
                            $secret = $chatboxData['custom_login']['secret'];
                            $customSession = Helper::prepareUserInformationForCustomLogin($user);

                            $box = Chatwing::getInstance()->get('chatbox');
                            $box->setId($boxId);
                            $box->setParam('custom_session', $customSession);
                            $box->setSecret($secret);

                            $targetURL = $box->getChatboxUrl();

                            ?>
                            <script>
                                window.opener.location = '<?php echo $targetURL;?>';
                                self.close();
                            </script>
                            <?php
                            die;
                        }
                    }

                }
            }
        } else {
            switch (true) {
                case !empty($_GET['redirect_url']):
                    $targetURL = $_GET['redirect_url'];
                    break;

                case !empty($requestedRedirectUrl):
                    $targetURL = $requestedRedirectUrl;
                    break;

                default:
                    break;
            }

        }

        return urldecode($targetURL);
    }

    protected function redirectUser($url, WP_User $user)
    {

    }

}