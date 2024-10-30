<?php
use Chatwing\IntegrationPlugins\WordPress\DataModel;
use Chatwing\Application as ChatwingContainer;

function customer_register_form() {
  $cw_auth = (isset($_POST['cw_auth'] ) ) ? $_POST['cw_auth'] : '';
  $client_id = (isset($_POST['client_id'] ) ) ? $_POST['client_id'] : '';
  $callback_url = (isset($_POST['callback_url'] ) ) ? $_POST['callback_url'] : '';
  $app_logo = (isset($_POST['app_logo'] ) ) ? $_POST['app_logo'] : '';
  if(empty($cw_auth)){
    $cw_auth = (isset($_GET['cw_auth'] ) ) ? $_GET['cw_auth'] : '';
    $client_id = (isset($_GET['client_id'] ) ) ? $_GET['client_id'] : '';
    $callback_url = (isset($_GET['callback_url'] ) ) ? $_GET['callback_url'] : '';
    $app_logo = (isset($_GET['app_logo'] ) ) ? $_GET['app_logo'] : '';
  }

  ?>
  <label for="password"><?php echo ('Password') ?><br />
  <input type="password" name="password" id="password" class="input" size="25" /></label>
  <input type="hidden" name="cw_auth" id="cw_auth" class="input" value="<?php echo esc_attr(stripslashes($cw_auth)); ?>" size="25" />
  <input type="hidden" name="client_id" id="client_id" class="input" value="<?php echo esc_attr(stripslashes($client_id)); ?>" />
  <input type="hidden" name="callback_url" id="callback_url" class="input" value="<?php echo esc_attr(stripslashes($callback_url)); ?>" />
  <input type="hidden" name="app_logo" id="app_logo" class="input" value="<?php echo esc_attr(stripslashes($app_logo)); ?>" />
    <div style="margin-left:9px; margin-right:8px">
    <div style="float:left">
      <img width="100px" height="auto" src="<?php echo plugins_url('assets/images/cw-logo-128x128.png', __FILE__)?>">
    </div>
    <div style="float:left; margin-top:40px">
      <img width="50px" height="auto" src="<?php echo plugins_url('assets/images/sync-icon.png', __FILE__)?>">
    </div>
    <div style="float:left">
      <img width="100px" height="auto" src="<?php echo $app_logo ?>">
    </div>
  </div>
  <?php
}

function customer_registration_errors($errors, $sanitized_user_login, $user_email) {
  $password = $_POST['password'];
  if (empty($password) || !empty($password) && trim($password) == '') {
    $errors->add('password_error', '<strong>ERROR</strong>: You must include a password.');
  }

  if (!empty($password) &&  5 > strlen($password ) ) {
    $errors->add('password_error', '<strong>ERROR</strong>: Password length must be greater than 5' );
  }

    return $errors;
}

function customer_user_register($user_id) {
  $password = $_POST['password'];
  if (!empty($password) ) {
    wp_set_password(trim($password), $user_id );
  }
  $user = get_userdata($user_id);
  try {
    $client_id = (isset($_POST['client_id'] ) ) ? $_POST['client_id'] : '';
    $callback_url = (isset($_POST['callback_url'] ) ) ? $_POST['callback_url'] : '';
    if(empty($client_id)){
      $client_id = (isset($_GET['client_id'] ) ) ? $_GET['client_id'] : '';
      $callback_url = (isset($_GET['callback_url'] ) ) ? $_GET['callback_url'] : '';
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

$cw_auth = (isset($_POST['cw_auth'] ) ) ? $_POST['cw_auth'] : '';
if(empty($cw_auth)) {
  $cw_auth = (isset($_GET['cw_auth'] ) ) ? $_GET['cw_auth'] : '';
}

if($cw_auth) {
  add_action('register_form', 'customer_register_form' );
  add_filter('registration_errors', 'customer_registration_errors', 10, 3 );
  add_action('user_register', 'customer_user_register' );
}