<?php

if( ! defined("ABSPATH") ) {
    exit;
}

if( ! class_exists("WPSFramework") ) {
    class WPSFramework {

        private function _defaults() {
            return array(
                'settings'   => FALSE,
                'customizer' => FALSE,
                'metabox'    => FALSE,
                'shortcode'  => FALSE,
                'taxonomy'   => FALSE,
            );
        }

        public function __construct($options = array()) {
            $this->init($options);
        }

        public function init($options = array()) {
            $final = wp_parse_args($options, $this->_defaults());

            if( $final['settings'] !== FALSE && ( is_admin() || is_ajax() ) ) {
                $this->init_settings($final['settings']);
            }

            if( $final['metabox'] !== FALSE && is_admin() ) {
                $this->init_metabox($final['metabox']);
            }

            if( $final['customizer'] !== FALSE ) {
                $this->init_customizer($final['customizer']);
            }

            if( $final['taxonomy'] !== FALSE && ( is_admin() || is_ajax() ) ) {
                $this->init_taxonomy($final['taxonomy']);
            }

            if( $final['shortcode'] !== FALSE ) {
                $this->init_shortcode($final['shortcode']);
            }
        }

        public function init_taxonomy($options) {
            $this->taxonomy = new WPSFramework_Taxonomy($options);
        }

        public function init_customizer($options) {
            $this->customizer = new WPSFramework_Customize($options);
        }

        public function init_metabox($options) {
            $this->metabox = new WPSFramework_Metabox($options);
        }

        public function init_settings($options) {
            $this->settings = new WPSFramework_Settings($options['config'], $options['options']);
        }

        public function init_shortcode($options) {
            $this->shortcodes = new WPSFramework_Shortcode_Manager($options);
        }
    }
}