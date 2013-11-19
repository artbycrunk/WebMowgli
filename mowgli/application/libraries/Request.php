<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Request
 * This class is a single instance class ( only one instance of this class is possible )
 * this class holds the request details, namely
 * - is_logged
 * - page id
 * - template id
 * - uri
 * -
 *
 * @author Lloyd
 */
class Request {

        private $ci;
        private static $instance = null;
        private $is_logged = false;
        private $uri = null;
        private $uri_relative = null;
        private $page_id = null;
        private $temp_id = null;
        private $error = null;

        private function __construct($params = array()) {

                // prevent creation of object externally

                $this->ci = & get_instance();
//                $this->_reset();
                $this->initialize();

                // load libraries
                $this->ci->load->library('user/auth');
        }

        private function __clone() {
                // do nothing, prevent Copy of object
        }

        /** Get instance for Singleton pattern */
	public static function get_instance($params = array()) {

		if (!isset(self::$instance)) {

			self::$instance = new Request($params);
		}

		return self::$instance;
	}

        public function initialize() {

                $this->_reset();

                $this->is_logged = $this->ci->auth->is_logged_in();
                $this->uri = trim( uri_string(), '/');
                $this->page_id = null;
                $this->temp_id = null;

                $this->error = null;
        }

        /**
         * Reset status params
         */
        private function _reset() {

                $this->is_logged = false;
                $this->uri = null;
                $this->page_id = null;
                $this->temp_id = null;

                $this->error = null;
        }

//        public function set_uri($uri) {
//                $this->uri = $uri;
//        }
//
//        public function set_page_id($page_id) {
//                $this->page_id = $page_id;
//        }
//
//        public function set_temp_id($temp_id) {
//                $this->temp_id = $temp_id;
//        }


        public function get_error() {
                return $this->error;
        }

        private function set_error($error) {
                $this->error = $error;
        }

        /**
         * Get any property value based n property name
         * returns property value only if valid property name provided
         * else returns null
         *
         * @param string $key property name
         *
         * @return mixed|null if p
         */
        public function get($key) {

                return property_exists( $this, $key ) ? $this->$key : null;
        }

        /**
         * Sets a property of this class based on key provided
         * note: key should be a valid property defined in the class,
         * note: CANNOT set $instance property
         *
         * @param string $key property name ( Eg. page_id, temp_id, uri )
         * @param string $value value for the property
         *
         * @return bool
         */
        public function set($key, $value) {

                $success = false;

                // set this class property value only if it is a valid property
                if ( property_exists( $this, $key ) AND strtolower($key) !== 'instance' ) {

                        $this->$key = $value;
                        $success = true;
                } else {
                        $success = false;
                        $this->set_error("Request variable not defined");
                }

                return $success;
        }

}

?>
