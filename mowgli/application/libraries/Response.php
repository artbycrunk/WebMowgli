<?php

/**
 * Description of Response
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
!defined('STATUS_CODE_200') ? define('STATUS_CODE_200', 200) : null;

class Response {

        private $ci;
        private static $instance = null;
        private $code = null;
        private $redirect_url = null;
        private $message = null;

        private function __construct($params = array()) {

                // prevent creation of object externally

                $this->ci = & get_instance();
                $this->reset();
        }

        private function __clone() {
                // do nothing, prevent Copy of object
        }

        /* Get instance for Singleton pattern */

        public static function get_instance($params = array()) {

                if (!isset(self::$instance)) {

                        self::$instance = new Response($params);
                }

                return self::$instance;
        }

        /**
         * Reset status params
         */
        public function reset() {

                $this->code = STATUS_CODE_200;
                $this->redirect_url = null;
                $this->message = null;
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

                return property_exists($this, $key) ? $this->$key : null;
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
                if (property_exists($this, $key) AND strtolower($key) !== 'instance') {

                        $this->$key = $value;
                        $success = true;
                } else {
                        $success = false;
                        $this->set_error("Response variable not defined");
                }

                return $success;
        }

        public function set_response($code, $message, $redirect_url = null) {

                $this->code = $code;
                $this->message = $message;
                $this->redirect_url = $redirect_url;
        }

}

?>
