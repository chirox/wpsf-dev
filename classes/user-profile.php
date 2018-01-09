<?php
/**
 * Created by PhpStorm.
 * User: varun
 * Date: 08-01-2018
 * Time: 09:25 PM
 */

class WPSFramework_User_Profile extends WPSFramework_Abstract {
    private static $_instance = NULL;
    public $options = array();
    public function __construct($options = array()) {
        $this->init($options);
    }

    public function init($options){
        $this->options = $options;
        add_action('load-profile.php',array(&$this,'map_user_info'));
        add_action('load-user-edit.php',array(&$this,'map_user_info'));
        add_action('show_user_profile', array(&$this,'custom_user_profile_fields'), 10, 1);
        add_action('edit_user_profile',array(&$this,'custom_user_profile_fields'), 10, 1);

        add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields' ) ,10,2);
        add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) ,10,2);

        $this->addAction("admin_enqueue_scripts",'load_style_script');
    }

    public function map_user_info(){
        foreach ($this->options as $optionid => $option){
            $this->options[$optionid] = $this->map_error_id($option,$option['id']);
        }
    }

    public function load_style_script() {
        global $pagenow;
        if ( $pagenow === 'profile.php' || $pagenow === 'user-edit.php' ) {
            wpsf_load_fields_styles();
        }
    }

    public function custom_user_profile_fields($user_id = null){
        global $wpsf_errors;
        $user_id = (is_object($user_id)) ? $user_id->ID : $user_id;
        foreach($this->options as $option_id => $option){
            $wpsf_errors = get_transient('_wpsf_umeta_'.$option['id']);
            $wpsf_errors = $wpsf_errors['errors'];
            $values = get_user_meta($user_id,$option['id'],true);
            $values = (!is_array($values)) ? array() : $values;
            $title = (isset($option['title']) && !empty($option['title'])) ? '<h2>'.$option['title'].'</h2>' : '';
            echo $title;
            echo '<div class="wpsf-framework wpsf-user-profile">';
            if(isset($option['style']) && $option['style'] === 'modern'){
                echo '<div class="wpsf-body">';
            }

            foreach($option['fields'] as $field){
                $default = isset($field['default']) ? $field['default'] : '';
                $value = isset($values[$field['id']]) ? $values[$field['id']] : $default;

                echo wpsf_add_element($field,$value,$option['id']);
            }

            if(isset($option['style']) && $option['style'] === 'modern'){
                echo '</div>';
            }
            echo '</div>';
        }
    }

    public function save_customer_meta_fields($user_id){
        $save_handler = new WPSFramework_Fields_Save_Sanitize;
        foreach ($this->options as $options){
            $posted_data = wpsf_get_var($options['id']);

            if(isset($options['fields'])){
                $posted_data = $save_handler->general_save_handler($posted_data,$options);
            }

            update_user_meta($user_id,$options['id'],$posted_data);
            set_transient('_wpsf_umeta_'.$options['id'],array('errors' => $save_handler->get_errors()),10);
        }
    }
}