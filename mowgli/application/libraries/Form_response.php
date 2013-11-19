<?php
/**
 * Description of form_responses

 send AJAX ( json ) form responses in the below format

 array
 --------[status]
 --------[message]
 --------[data]
 ----------------[validation]
 ------------------------[key]
 --------------------------------[status]
 --------------------------------[message]
 ------------------------[key]
 --------------------------------[status]
 --------------------------------[message]
 ------------------------[key]
 --------------------------------[status]
 --------------------------------[message]
 ----------------[redirect]
 ------------------------[url]   = [value]
 ------------------------[delay] = [value]


 *  * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class form_response {

        private $ci;

//        private $json = array();

        private $status = null;
        private $message = null;
        private $redirect = null;
        private $data = null;
        /**
         * Used to send status & message for individual form elements
         *
         * Format for sending ( array structure )
         *
         * $validationMsgs[ form_field_name ] = array(
         *              'status' => 'success/error/info/warning',
         *              'message' => 'message text'
         *      )
         *
         ----------------[validation]
         ------------------------[key]
         --------------------------------[status]
         --------------------------------[message]
         ------------------------[key]
         --------------------------------[status]
         --------------------------------[message]
         *
         */
        private $validationMsgs = array();

        private $statusValues = array( 'success', 'error', 'info', 'warning' );

        public function  __construct() {

                $this->ci = & get_instance();

                // sets all data to default values
                $this->clear();

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

        /**
         * sets a redirect url and delay (miliseconds)
         *
         * @param string $url
         * @param int   $delay (in miliseconds)
         */
        public function set_redirect( $url, $delay = 0 ) {

                $this->redirect = array(
                        'url' => $url,
                        'delay' => $delay
                );

                return $this;
        }

        public function set_data( $data = null ) {

                $this->data = $data;

                return $this;
        }

        /**
         * Adds a message specific to a form element
         * message can be any one of the following ( success, error, info, warning )
         * default status used = 'error'
         *
         * @param string $key key name for form field
         * @param string $message message text for particular form field
         * @param string $status default error ( success / error / info / warning )
         *
         * @return $this
         */
        public function add_validation_msg( $key, $message, $status = "error" ) {

                // do not add error message if message is null or message = ''
                if( is_null( $message ) OR $message == "" ) {

                        // do not add this element to json array

                }
                else {

                        $this->validationMsgs[ $key ] = array(
                                'status' => $status,
                                'message' => $message
                        );

                }

                return $this;
        }

        /**
         * Adds data to the data array
         * data can be any key value pair ( note: only used for future unplanned use )
         *
         * @param string $key
         * @param mixed $data any data type
         *
         * @return $this
         */
        public function add_data( $key, $data ) {

                $this->data[ $key ] = $data;

                return $this;
        }

        public function clear() {

                $this->set_message();   // use default values --> reset message and status
                $this->set_data();      // set to default, reset data

                return $this;
        }

        /**
         * sends output json output to browser
         */
        public function send() {

                $this->_prepare_data();

                $this->ci->load->library( 'json_response' );
                $this->ci->json_response
                        ->set_message( $this->status, $this->message )
                        ->set_data( $this->data )
                        ->send();
        }

        private function _prepare_data() {

                // add validations array to data

                $this->add_data( 'validations', $this->validationMsgs );
                $this->add_data( 'redirect', $this->redirect );

        }

}


/* End of file json_response.php */
/* Location: ./application/libraries/form_response.php */
?>
