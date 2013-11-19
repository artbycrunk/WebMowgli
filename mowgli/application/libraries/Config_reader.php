<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of config
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

defined( 'CONFIG_TRUE' ) ? null : define( 'CONFIG_TRUE', "true|yes|YES|Yes" ) ;
defined( 'CONFIG_FALSE' ) ? null : define( 'CONFIG_FALSE', "false|no|NO|No" ) ;
defined( 'CONFIG_NULL' ) ? null : define( 'CONFIG_NULL', "null|NULL|Null" ) ;

class Config_reader {

        private $ci;
        private $config;
        private $notifications = array(

                'error' => null,
                'warning' => null,
                'success' => null,
                'info' => null

                );

        public function  __construct() {

                $this->ci = & get_instance();
                $this->config = null;
                $this->errors = null;
        }

        /**
         * Reads provided config.ini file and returns array of configuration
         *
         * @param string full file path
         *
         * @return array|null returns array of config items, returns null if error
         */
        public function read( $configFile ) {

                $config = null;

                /* Get config constants for config.ini file */
                $consts = array(
                        'true'  => explode( '|', CONFIG_TRUE ),
                        'false' => explode( '|', CONFIG_FALSE ),
                        'null'  => explode( '|', CONFIG_NULL )
                );

                /* get path of them config file */
                $file = realpath( $configFile );

                /* Check if config file exists */
                if( file_exists( $file ) ) {

                        /* read config.ini file, suppress errors or warnings */
                        $config = parse_ini_file( $file, false );

                        /* Check if config file is valid */
                        if( $config != false AND count( $config ) > 0 ) {

                                /* If value is of type -- true, false of null convert sring to respective data type */
                                foreach ( $config as $key => $value ) {

                                        if( in_array( $value, $consts['true'] ) )  $config[ $key ] = true;
                                        if( in_array( $value, $consts['false'] ) )  $config[ $key ] = false;
                                        if( in_array( $value, $consts['null'] ) )  $config[ $key ] = null;
                                }

                                $this->config = $config;
                                $this->notifications['success'][] = 'Config file successfully read';

                        }
                        else {

                                // invalid config file
                                $config = null;
                                $this->notifications['error'][] = 'Invalid config file provided';

                        }

                }
                else {

                        // config file NOT found
                        $config = null;
                        $this->notifications['error'][] = 'Config file not found';

                }


                return $config;
        }

        /**
         * provides the value for particular config item (key),
         * returns null if value not found
         *
         * @return mixed|null returns value for given item, retunrs null if NOT found
         */
        public function get( $key ){

                return isset ( $this->config[ $key ] ) ? $this->config[ $key ] : null;

        }

        public function get_notifications( $type = 'all' ){

                $return = null;

                if( $type == 'all' ){

                        $return = $this->notifications;

                }
                else{
                        $return = isset ( $this->notifications[ $type ] ) ? $this->notifications[ $type ] : $return;
                }

                return $return;
        }


}


/* End of file config.php */
/* Location: ./application/.... config.php */
?>
