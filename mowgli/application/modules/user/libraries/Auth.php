<?php 
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of Auth
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

define('SESSION_USER', 'user');
define('SESSION_IS_LOGGED_IN', 'isLoggedIn');

class Auth {

        private $ci = null;

        private $userSess = array(
                SESSION_USER => null,
                SESSION_IS_LOGGED_IN => false
        );

        public function  __construct() {
                $this->ci = & get_instance();
                $this->ci->load->library('session');
        }

        public function crypt_password($data) {
                return hash('sha512', $data, false);
        }

        public function create_activation_key($data) {
                return sha1(time().$data.time());
        }

        public function create_reset_password( $username = null ) {

                $username = is_null( $username ) ? mt_rand( 100, 999999 ) : $username ;
                $pass = sha1( $username . time() );
                return substr( $pass, 0, 6 );      // first 6 chars of sha1 encrypt
        }

        public function session_login($username) {
                $this->userSess[SESSION_USER] = $username;
                $this->userSess[SESSION_IS_LOGGED_IN] = true;
                $this->ci->session->set_userdata($this->userSess);
        }

        public function session_logout() {
                $this->ci->session->unset_userdata($this->userSess);
        }

        public function is_logged_in() {
                return $this->ci->session->userdata(SESSION_IS_LOGGED_IN);
        }

        public function get_username() {
                return $this->ci->session->userdata(SESSION_USER);
        }
}
?>
