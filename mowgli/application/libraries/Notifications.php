<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of notifications
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

define( 'NOTIFICATIONS_SESSION_NAME', 'notifications');

class notifications {

    private $ci;

    private $messages = array(
        'success' => array(),
        'error' => array(),
        'warning' => array(),
        'info' => array()
    );

    public function  __construct() {

        $this->ci = & get_instance();

        $this->ci->load->library('session');
        $this->set_messages_from_session();
    }

    // Add message from $type = [ success, warning, error, info ], returns true on success, false on failure
    public function add( $type, $message ){

        $isSuccess = true;
        
        switch ( $type ) {
            case "success":

                $this->messages[ "success" ][] = $message;
                break;

            case 'error':

                $this->messages[ "error" ][] = $message;
                break;

            case 'warning':

                $this->messages[ "warning" ][] = $message;
                break;

            case 'info':

                $this->messages[ "info" ][] = $message;
                break;

            default:

                $isSuccess = false;
                break;
        }

        return  $isSuccess;
    }

    public function save(){
        $this->ci->session->set_userdata( NOTIFICATIONS_SESSION_NAME, serialize( $this->messages ) ); // serialize to allow backslash '\'
    }

    public function clear(){
        $this->ci->session->unset_userdata( NOTIFICATIONS_SESSION_NAME );
    }

    public function get( $type = 'all', $clear = true ){
        
        $return = null;

        switch ( $type ) {

            case "all":

                $return = $this->messages;
                break;
            
            case "success":

                $return = $this->messages[ "success" ];
                break;

            case 'error':

                $return = $this->messages[ "error" ];
                break;

            case 'warning':

                $return = $this->messages[ "warning" ];
                break;

            case 'info':

                $return = $this->messages[ "info" ];
                break;

            default:

                $return = null;
                break;
        }

        $clear == true ? $this->clear() : null;

        return $return;
    }

    // get messages from session, if no data in session, do nothing
    private function set_messages_from_session(){

        //          unserialize to get back original data
        $messages = unserialize( $this->ci->session->userdata( NOTIFICATIONS_SESSION_NAME ) );

        if( $messages != false ){

            $this->messages = $messages;

        }
    }
}
?>
