<?php
use Chatwing\IntegrationPlugins\WordPress\DataModel;
use Chatwing\Application as ChatwingContainer;

function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
        background-image: url("");
        height:0px;
        width:320px;
        background-size: 320px 65px;
        background-repeat: no-repeat;
        }
    </style>

<?php 
}

function register_url() { 
  return esc_url(wp_registration_url());
}

function my_login_footer() { ?>
  <style>  #login #nav,#backtoblog,.oneall_social_login {display:none} </style>

<?php }

function my_login_form() {
  $cw_auth = (isset($_POST['cw_auth'])) ? $_POST['cw_auth'] : '';
  $client_id = (isset($_POST['client_id'])) ? $_POST['client_id'] : '';
  $callback_url = (isset($_POST['callback_url'])) ? $_POST['callback_url'] : '';
  $app_logo = (isset($_POST['app_logo'])) ? $_POST['app_logo'] : '';
  if(empty($cw_auth)){
    $cw_auth = (isset($_GET['cw_auth'])) ? $_GET['cw_auth'] : '';
    $client_id = (isset($_GET['client_id'])) ? $_GET['client_id'] : '';
    $callback_url = (isset($_GET['callback_url'])) ? $_GET['callback_url'] : '';
    $app_logo = (isset($_GET['app_logo'])) ? $_GET['app_logo'] : '';
  }
  $register_url = register_url()
                 . "&cw_auth=" . $cw_auth
                 . "&client_id=" . $client_id
                 . "&callback_url=" . $callback_url
                 . "&app_logo=" . $app_logo
  ?>
  <div style="margin-left:9px; margin-right:8px">
    <div style="margin-left:70px">
      <img width="100px" height="auto" src="<?php echo $app_logo ?>">
    </div>
    <?php 
    if (get_option('users_can_register')) {
    ?>
      <div style="align-text:center; width:100%; margin:15px">
        Don't have account yet? <a href="<?php echo $register_url ?>">SIGN UP</a>
      </div>
    <?php } ?>
  </div>
  <input type="hidden" name="cw_auth" id="cw_auth" class="input" value="<?php echo esc_attr(stripslashes($cw_auth)); ?>" size="25" />
  <input type="hidden" name="client_id" id="client_id" class="input" value="<?php echo esc_attr(stripslashes($client_id)); ?>" />
  <input type="hidden" name="callback_url" id="callback_url" class="input" value="<?php echo esc_attr(stripslashes($callback_url)); ?>" />
  <input type="hidden" name="app_logo" id="app_logo" class="input" value="<?php echo esc_attr(stripslashes($app_logo)); ?>" />
  <?php
}

function my_authenticate($user_login, $user)
{
  try {
    $client_id = (isset($_POST['client_id'])) ? $_POST['client_id'] : '';
    $callback_url = (isset($_POST['callback_url'])) ? $_POST['callback_url'] : '';
    if(empty($client_id)){
      $client_id = (isset($_GET['client_id'])) ? $_GET['client_id'] : '';
      $callback_url = (isset($_GET['callback_url'])) ? $_GET['callback_url'] : '';
    }
    $model = DataModel::getInstance();
    $params = array();
    $params["wp_user"] = $user->data;
    $avatar = get_avatar_data($user->data->ID);
    $params["wp_user"]->avatar = $avatar["url"];
    $params["req_client_id"] = $client_id;
    $params["plugin"] = "wordpress";

    $response = $model->syncAccount($params);
    if ($response->success) {
      $access_token = $response->data["access_token"];
      $client_id = $response->data["client_id"];
      wp_redirect($callback_url."/success?access_token=".$access_token."&client_id=".$client_id);
      exit;  
    } else {
      die(wp_json_encode($response->error));
    }
  } catch(Exception $e) {
    die($e->getMessage());
  }
  
}

$cw_auth = (isset($_POST['cw_auth'])) ? $_POST['cw_auth'] : '';
if(empty($cw_auth)){
  $cw_auth = (isset($_GET['cw_auth'])) ? $_GET['cw_auth'] : '';
}
if (!empty($cw_auth)) {
  add_action('login_enqueue_scripts', 'my_login_logo' );
  add_action('login_footer', 'my_login_footer' );
  add_action('login_form', 'my_login_form');
  add_action('wp_login','my_authenticate', 10, 2);
}
