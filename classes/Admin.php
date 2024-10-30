<?php namespace Chatwing\IntegrationPlugins\WordPress;

/**
 * @package Chatwing\IntegrationPlugins\Wordpress
 * @author chatwing
 */

use InvalidArgumentException;
use Chatwing\Application as App;

class Admin extends PluginBase
{
    protected function init()
    {
        parent::init();
    }

    protected function registerHooks()
    {   

        add_action('admin_menu', array($this, 'registerAdminMenu'));
        add_action('admin_action_chatwing_save_token', array($this, 'handleTokenSaving'));
        add_action('admin_action_chatwing_save_settings', array($this, 'handleSettingsSave'));
    }
   
    protected function registerFilters()
    {

    }

    public function registerAdminMenu()
    {
      add_menu_page(__('Chatwing plugin settings', CHATWING_TEXTDOMAIN), 'Chatwing', 'manage_options', 'chatwing', array($this, 'showSettingsPage'));
    }

    /**
     * Show chatwing settings page
     */
    public function showSettingsPage()
    {
      try {
            if ($this->getModel()->hasAccessToken()) {
                $boxes = $this->getModel()->getBoxList();
                $this->loadTemplate('settings', array('boxes' => $boxes));
            } else {
                $this->loadTemplate('new_token');
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * Hanle token update/remove
     */
    public function handleTokenSaving($skipNonce = false)
    {
        if (!$skipNonce) {
          $nonce = !empty($_POST['nonce']) ? $_POST['nonce'] : '';
          if (!wp_verify_nonce($nonce, 'token_save')) {
            die('Oops .... Authentication failed!');
          }
        }

        if (empty($_POST['token'])) {
          if (isset($_POST['remove_token']) && $_POST['remove_token'] == 1) {
              $this->getModel()->deleteAccessToken();
          } else {
            die('Unknown action!');
          }
        } else {
            $token = $_POST['token'];
            $this->getModel()->saveAccessToken($token);
        }

        wp_redirect('admin.php?page=chatwing');
        die;
    }

    public function handleSettingsSave()
    {
        $nonce = !empty($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'settings_save')) {
            die('Oops .... Authentication failed!');
        }

        $fieldsToUpdate = array('width', 'height');

        foreach($fieldsToUpdate as $field) {
          if (!empty($_POST[$field]) && is_numeric($_POST[$field])) {
            update_option('chatwing_default_' . $field, $_POST[$field]);
          }
        }
        if (!empty($_POST['keysecret'])) {
          update_option('chatwing_default_keysecret', $_POST['keysecret']);
        }
        if (!empty($_POST['app_id'])) {
          update_option('chatwing_default_app_id', $_POST['app_id']);
        }
        if (!empty($_POST['token']) || !empty($_POST['remove_token'])) {
          $this->handleTokenSaving(true);
        } else {
          wp_redirect('admin.php?page=chatwing');
          die;
        }
    }

    /**
     * Load admin template
     * @param  string $templateName
     * @param  array $data
     * @throws InvalidArgumentException
     */
    public function loadTemplate($templateName, $data = array())
    {
      if (strpos($templateName, '.php') === false) {
        $templateName .= '.php';
      }

        $file = CHATWING_TPL_PATH . '/' . $templateName;
        if (file_exists($file)) {
          ob_start();
          if (!empty($data)) {
            extract($data);
          }
          require $file;
          $content = ob_get_clean();

          echo $content;
        } else {
          throw new InvalidArgumentException("Tempalte {$templateName} doesn't exist");
        }
    }

}
