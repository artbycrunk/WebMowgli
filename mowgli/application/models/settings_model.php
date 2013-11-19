<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of settings
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Settings_Model extends Site_Model {

        protected $tables = array();

        public function  __construct() {
                parent::__construct();

                $this->config->load('database_tables');
                $this->tables["settings"] = $this->config->item( 'TBL_SETTINGS' );

        }

        /**
         * Gets ONLY key-value pair for particular category
         * Get list of settings for 'all' or specific category or list of categories
         * returns null if no result found
         * @param string|array
         * @return array|null returns null if no result found
         */
        public function get_settings( $categories = 'all' ) {

                $settings = null;

                $select = "
                    set_category AS category,
                    set_key AS `key`,
                    set_value AS value,
                    set_data_type AS data_type
                ";

                $this->db->select( $select );
                $this->db->from( $this->tables["settings"] );

                /* Apply appropriate where conditions if category NOT 'all' */
                if( $categories != 'all' ) {

                        if( is_array( $categories ) AND count( $categories ) > 0 ) {
                                $this->db->where_in( 'set_category', $categories ); // get only those present in array
                        }
                        elseif( is_string( $categories ) ) {
                                $this->db->where( "set_category = '$categories'" ); // get for particular category
                        }
                }

                $query = $this->db->get();

                if( $query->num_rows() > 0 ) {

                        $settings = $query->result_array();

                        // strip slashes
                        foreach( $settings as & $row ) {

                                foreach ( $row as $key => $value ) {

                                        $row[$key] = stripslashes( $value );

                                }
                        }
                }

                return $settings;
        }

        /**
         * Gets ONLY key-value pair for particular category
         * Get list of settings for 'all' or specific category or list of categories
         * returns null if no result found
         * @param string|array
         * @return array|null returns null if no result found
         */
        public function get_settings_data( $category ) {

                $settings = null;

                $category = $this->addslashes( $category );

                $select = "
                    set_id AS id,
                    set_category AS category,
                    set_name AS name,
                    set_key AS `key`,
                    set_value AS value,
                    set_options AS options,
                    set_description AS description,
                    set_data_type AS data_type
                ";

                $this->db->select( $select );
                $this->db->from( $this->tables["settings"] );
                $this->db->where( "set_category", $category ); // get for particular category

                $query = $this->db->get();

                if( $query->num_rows() > 0 ) {

                        $settings = $query->result_array();

                        // strip slashes
                        foreach( $settings as & $row ) {

                                foreach ( $row as $key => $value ) {

                                        $row[$key] = stripslashes( $value );

                                }
                        }
                }

                return $settings;
        }


        /**
         * edit a single setting value
         *
         * @param $category string
         * @param $key string
         * @param $value string
         *
         * @return bool
         */
        public function edit_setting( $category, $key, $value ) {

                $category = $this->addslashes( $category );
                $key = $this->addslashes( $key );
                $value = $this->addslashes( $value );

                $this->db->set( 'set_value', $value );
                $this->db->where( 'set_category', $category );
                $this->db->where( 'set_key', $key );
                $this->db->update( $this->tables["settings"] );

                        // mysql_affected_rows returns -1 on failure
//                return $this->db->affected_rows() != -1 ? true : false;
                return $this->check_update_success();
        }
}
?>
