<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of Json_response
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Json_response {

        private $ci;

        private $json = array();
        private $message = null;
        private $status = null;
        private $statusValues = array( 'success', 'error', 'info', 'warning' );
        private $data = null;

        private $errors = array();

        public function  __construct() {

                $this->ci = & get_instance();

                // sets all data to default values
                $this->clear();

        }

        public function get_errors() {
                return $this->errors;
        }

        /**
         * Sets status and message
         *
         * @param string $status
         * @param string $message
         *
         * @return object|null
         */
        public function set_message( $status = null, $message = null ) {

                $return = null;

                /* set status, message ONLY if status is among ( success, info, warning, error ) OR null
                 * return $this else return null
                */
                if( is_null( $status ) OR in_array( $status, $this->statusValues ) ) {

                        $this->status = $status;
                        $this->message = $message;

                        $return = $this;
                }
                else {
                        $return = null;
                }

                return $return;
        }

        public function set_data( $data = null ) {

                $this->data = $data;

                return $this;
        }

        public function clear() {


                $this->set_message();   // use default values --> reset message and status
                $this->set_data();      // set to default, reset data
                $this->_prepare_json();

                return $this;
        }

        /**
         * Sets content type to 'application/json',
         * sends output json output to browser
         */
        public function send() {

                if( $this->ci->input->is_ajax_request() ) {

                        // turn off profiler if turned on, since response will be json.
                        $this->ci->output->enable_profiler( false );
                        
                        $this->_prepare_json();

                        $this->ci->output
                                ->set_content_type('application/json')
                                ->set_output( json_encode( $this->json ) );
                        
                }

        }

        private function _prepare_json() {

                $this->json = array(
                        'status' => $this->status,
                        'message' => $this->message,
                        'data' => $this->data
                );
        }

        private function _add_error( $msg ) {
                $this->errors[] = $msg;
        }

}


/* End of file json_response.php */
/* Location: ./application/libraries/Json_response.php */
?>
