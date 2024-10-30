<?php
/**
 * @author chatwing <dev@chatwing.com>
 */

namespace Chatwing\IntegrationPlugins\WordPress;

use Chatwing\Application as App;


class ShortCode
{
    /**
     * @param array $params
     * @return string
     */
    public static function render($params = array())
    {
        $model = DataModel::getInstance();

        $defaultAttributes = array(
            'width' => $model->getOption('width'),
            'height' => $model->getOption('height')
        );

        $params = array_merge($defaultAttributes, $params);

        if (empty($params['id'])) {
            return '';
        }

        /**
         * @var \Chatwing\Chatbox $box
         */
        $box = App::getInstance()->get('chatbox');
        $box->setId($params['id']);


        $box->setData('width', $params['width']);
        $box->setData('height', $params['height']);

        if (!empty($params['enable_custom_login'])
            && $params['enable_custom_login'] == '1'
            && !empty($params['custom_login_secret'])
        ) {
            $user = wp_get_current_user();
            if ($user->ID) {
                $avatar = simplexml_load_string(get_avatar($user->ID));
                if ($avatar) {
                    $attributes = $avatar->attributes();
                    $avatar = (string) $attributes['src'];
                } else {
                    $avatar = 'http://chatwing.com/images/no-avatar.gif';
                }

                $customSession = array(
                    'id' => $user->ID,
                    'name' => $user->user_nicename,
                    'avatar' => $avatar,
                    'expiration' => round(microtime(true) * 1000) + 60 * 60 * 100
                );

                $box->setParam('custom_session', $customSession);
                $box->setSecret($params['custom_login_secret']);
            }
        }

        return $box->getIframe();
    }

    /**
     * Generate shortcode for a chatbox
     * @param  array $params
     * @return string
     */
    public static function generateShortCode($params = array())
    {
        if (empty($params) || (empty($params['id']))) {
            return '';
        }

        $model = DataModel::getInstance();

        $defaultAttributes = array(
            'id' => '',
            'width' => $model->getOption('width'),
            'height' => $model->getOption('height'),
            'enable_custom_login' => '0',
            'custom_login_secret' => ''
        );

        $params = shortcode_atts($defaultAttributes, $params);

        if (!empty($params['key'])) {
            unset($params['alias']);
        } else {
            unset($params['key']);
        }

        $shortCode = '';
        foreach ($params as $key => $value) {
            $shortCode .= "{$key}=\"{$value}\" ";
        }
        $shortCode = "[chatwing {$shortCode} ][/chatwing]";
        return $shortCode;
    }
}