<?php namespace Chatwing\IntegrationPlugins\WordPress;

/**
 * Class Widget
 * @package Chatwing\IntegrationPlugins\WordPress
 * @author chatwing
 */

class Widget extends \WP_Widget
{
    function __construct()
    {
        parent::__construct('chatwing_cb', __('Chatwing chatbox', CHATWING_TEXTDOMAIN));
    }

    public function widget($args, $instance)
    {
        $defaultAttributes = array(
            'title' => ''
        );

        $instance = array_merge($defaultAttributes, $instance);
        echo $args['before_widget'];
        echo $args['before_title'] . $instance['title'] . $args['after_title'];
        echo ShortCode::render(array(
            'id' => $instance['chatbox'],
            'width' => !empty($instance['width']) ? $instance['width'] : '',
            'height' => !empty($instance['height']) ? $instance['height'] : '',
            'enable_custom_login' => !empty($instance['enable_custom_login']) ? $instance['enable_custom_login'] : false,
            'custom_login_secret' => !empty($instance['custom_login_secret']) ? $instance['custom_login_secret'] : ''
        ));
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $boxes = DataModel::getInstance()->getBoxList();
        $currentID = !empty($instance['chatbox']) ? $instance['chatbox'] : null;
        ?>
        <p>
            <label
                for="<?php echo $this->get_field_id('title'); ?>"><?php _e("Title", CHATWING_TEXTDOMAIN); ?></label>
            <input type="text" class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php echo !empty($instance['title']) ? $instance['title'] : '' ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('chatbox'); ?>"><?php _e('Chatbox', CHATWING_TEXTDOMAIN); ?></label>
            <select name="<?php echo $this->get_field_name('chatbox'); ?>"
                    id="<?php echo $this->get_field_id('chatbox'); ?>">
                <?php if (!empty($boxes)): foreach ($boxes as $box): ?>
                    <option
                        value="<?php echo $box['id'] ?>" <?php if ($box['id'] == $currentID) echo 'selected="selected"'; ?>><?php echo $box['alias']; ?></option>
                <?php endforeach;endif; ?>
            </select>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', CHATWING_TEXTDOMAIN); ?></label>
            <input type="text"
                   name="<?php echo $this->get_field_name('width'); ?>"
                   id="<?php echo $this->get_field_id('width') ?>"
                   class="widefat"
                   value="<?php echo !empty($instance['width']) ? $instance['width'] : '' ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('height'); ?>"><?php _e('Height', CHATWING_TEXTDOMAIN); ?></label>
            <input type="text"
                   name="<?php echo $this->get_field_name('height'); ?>"
                   id="<?php echo $this->get_field_id('height'); ?>"
                   class="widefat"
                   value="<?php echo !empty($instance['height']) ? $instance['height'] : '' ?>"/>
        </p>
        <p>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->get_field_name('enable_custom_login'); ?>" <?php if (!empty($instance['enable_custom_login'])) echo 'checked'; ?>
                       value="1"/>
                <?php _e('Enable custom login ?', CHATWING_TEXTDOMAIN); ?>
            </label>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('custom_login_secret'); ?>"><?php _e('Custom login secret', CHATWING_TEXTDOMAIN); ?></label>
            <input type="text"
                   name="<?php echo $this->get_field_name('custom_login_secret'); ?>"
                   id="<?php echo $this->get_field_id('custom_login_secret'); ?>"
                   class="widefat"
                   value="<?php echo !empty($instance['custom_login_secret']) ? $instance['custom_login_secret'] : '' ?>"/>
        </p>
    <?php
    }

    public function update($new, $old)
    {
        return $new;
    }
}