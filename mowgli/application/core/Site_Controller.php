<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Site_Controller
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Site_Controller extends My_Controller {

        protected $pageObj;

//    private static $instance;

        public function __construct() {
                parent::__construct();

//        self::$instance =& $this;
        }

        final public function init($objects = array()) {

                // assign objects passed by Page Renderering function to object variables
                foreach ($objects as $key => $obj) {

                        if (property_exists($this, $key)) {

                                $this->$key = $obj;
                        }
                }
        }

        final public function reset() {

                $this->page = null;

        }

//    public static function &get_instance()
//    {
//        return self::$instance;
//    }
        //public function view( $pageObj );
}

?>