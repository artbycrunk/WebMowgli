<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
class Admin_Controller extends My_Controller {

//    private static $instance;

    public function  __construct() {

        parent::__construct();

//        self::$instance =& $this;

        // get query string and add to current url if needed
        $currentUrl = ( isset( $_GET ) AND count( $_GET ) > 0 ) ? current_url() . '?' . $_SERVER['QUERY_STRING'] : current_url();

            // Converts entire admin section into password protected area
        $this->load->module('user')->authorize( $currentUrl );

    }

}
?>
