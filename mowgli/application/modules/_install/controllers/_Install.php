<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
!defined("SETTINGS_KEY_SITE_INSTALLED") ? define("SETTINGS_KEY_SITE_INSTALLED", 'site_installed') : null;

/**
 * Description of _Install
 *
 * @author Lloyd
 */
class _Install extends Site_Controller {

        private $module = "_install";
        private $settingCategory = "installation";

        public function __construct() {
                parent::__construct();

                // load libraries
                $this->load->library('site_settings');
        }

        public function index() {

                $isSiteInstalled = $this->site_settings->get($this->settingCategory, SETTINGS_KEY_SITE_INSTALLED);

                // check if site installed
                // yes --> redirect to home page
                // no --> display installation page
                if ($isSiteInstalled === true) {

                        redirect(site_url(), '', 301);

                } else {
                        // load install page
                        $this->parser->parse( $this->module . '/install_site.php', $this->parseData);

                        // set site as installed in settings
                        $this->site_settings->set($this->settingCategory, SETTINGS_KEY_SITE_INSTALLED, true);
                }
        }

        private function _install(){

                // Load all installation scripts here


//                $this->_load_initial_settings();

        }

        private function _load_initial_settings(){




        }

}

?>
