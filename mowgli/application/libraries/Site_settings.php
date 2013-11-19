<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of settings
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//// Data types for settings
//! defined( "SETTING_TYPE_BOOL" ) ? define( "SETTING_TYPE_BOOL", "bool" ) : null;
//! defined( "SETTING_TYPE_ARRAY" ) ? define( "SETTING_TYPE_LIST", "array" ) : null;
//! defined( "SETTING_TYPE_STRING" ) ? define( "SETTING_TYPE_STRING", "string" ) : null;
//
//// Delimiter for settings of 'array' type ( temporary, will not be needed once serialize/unserialize is used )
//! defined( "SETTING_DELIMITER" ) ? define( "SETTING_DELIMITER", "|" ) : null;

class Site_settings {

        private $ci;
        private $settings;      //      $settings[category] = array( 'key1' = 'value1', 'key2' = 'value2' )

        public function __construct() {

                $this->ci = & get_instance();

                $this->ci->load->model('settings_model');
        }

        /**
         * Loads list of settings for 'all' OR specific category OR array of categories
         * settings are loaded from database ONLY if NOT already loaded. OR if all
         * Settings for each category can be loaded with multiple load statements,
         *   Eg. load('categ_1'), load('categ_2')
         *   the lists will keep adding to the main settings list
         * Final List is arranged by category. Eg $settings[category][array]
         * Settings are loaded into main settings array $this->settings according to category,
         * Eg. $this->settings[ category ]
         *
         * returns true/false if no result found
         *
         * @param string|array list of setting categories to retreive
         * @return bool
         */
        public function load($categories = 'all') {

                $success = false;

                // save single category in $category
                $category = !is_array($categories) ? $categories : null;

                // remove categories from list that are already loaded. note: null if all categs loaded
                $categories = $this->_get_categs_not_loaded($categories);

                // Check if settings already loaded, if loaded --> do not load again from database
                if (!isset($this->settings[$category]) OR !is_null($categories)) {

                        // get settings frm db
                        $set = $this->ci->settings_model->get_settings($categories); // note: param = 'all' OR 'category_name' OR categories[ mod1, mod2, mod3 ]

                        $settings = null;

                        // check if settings available, if available process further, else set success = false
                        if (!is_null($set)) {

                                //arrange settings by categories
                                foreach ($set as $row) {

                                        // convert value to appropriate data type
                                        $row['value'] = $this->_typecast_value($row['value'], $row['data_type']);
                                        $settings[$row['category']][$row['key']] = $row['value'];
                                }

                                /* Load OR Reload settings into main settings array $this->settings[ category ] */
                                foreach ($settings as $category => $array) {

                                        /* Unset settings[category] if already present & reload new values from db */
                                        if (isset($this->settings[$category]))
                                                unset($this->settings[$category]);

                                        /* Load db settings into main settings array */
                                        $this->settings[$category] = $array;
                                }

                                $success = true;
                        }
                        else {
                                // settings not found in db or some db error
                                $success = false;
                        }
                } else {
                        // settings already loaded previously
                        $success = true;
                }



                return $success;
        }

        private function _get_categs_not_loaded($categories) {

                $categs_not_loaded = null;

                if (is_array($categories)) {

                        foreach ($categories as $categ) {

                                // save category name in list, if not loaded
                                if (!isset($this->settings[$categ]))
                                        $categs_not_loaded[] = $categ;
                        }
                }

                return $categs_not_loaded;
        }

        /**
         * Returns settings for all categories OR specific category OR specific key of a category
         * value is returns from the $this->settings array
         *
         * if category and key NOT provided
         *      return settings = array(
         *                      categ_1 = array ( key1 = val1, key2 = val2 ),
         *                      categ_2 = array ( key1 = val1, key2 = val2 ) . . . .
         *              )
         *
         * if category provided BUT key NOT provided
         *      return array ( key1 = val1, key2 = val2 )
         *
         * if category AND key provided
         *      returns single string value
         *
         * Returns null if key does not exist
         *
         * @param string $category category name
         * @param string $key key name
         *
         * @return array|string|null returns value OR null if invalid key
         */
        public function get($category = 'all', $key = null) {

                $return = null;

                // call load category ( incase if not previously loaded )
                $this->load($category);

                if ($category == 'all') {

                        $return = isset($this->settings) ? $this->settings : $return;
                }
                /* If key not provided, return entire category */ elseif (is_null($key)) {

                        if (isset($this->settings[$category])) {
                                $return = $this->settings[$category];
                        }
                } elseif (isset($this->settings[$category][$key])) {

                        $return = $this->settings[$category][$key];
                }

                return $return;
        }

        /**
         * Returns all data for mentioned setting category
         *
         * @param $category string
         *
         * @return array|null returns null if nothing found
         */
        public function get_data($category) {

                /**
                 * PENDING ( to be worked on later )
                 *
                 * Settings should be serialized and stored in db, remove the 'set_data_type' column from db
                 */
                $settings = $this->ci->settings_model->get_settings_data($category);

                foreach ($settings as & $setting) {

                        $setting['value'] = $this->_typecast_value($setting['value'], $setting['data_type']);
                        $setting['options'] = $this->_typecast_options($setting['options'], $setting['data_type']);
                }

                return $settings;
        }

        private function _typecast_value($value, $dataType) {

                return $this->_decode_setting($value);

//                switch ( $dataType ) {
//
//                        case SETTING_TYPE_BOOL:
//
//                                $value = ( bool ) $value;
//                                break;
///*
//                        case SETTING_TYPE_ARRAY:
//
//                                // PENDING --> later change to unserialize
//                                $value = explode( SETTING_DELIMITER, $value );
//                                break;
//								*/
//
//                        case SETTING_TYPE_STRING:
//
//                                //$value = ( string ) $value;
//                                break;
//
//                        default:
//
//                                // should not reach here, since db field for data_type is an 'enum'
//                                break;
//                }
//
//                return $value;
        }

        private function _typecast_options($options, $dataType) {

                switch ($dataType) {

                        case SETTING_TYPE_BOOL:

                                $options = array(true, false);
                                break;

                        case SETTING_TYPE_ARRAY:

                                // PENDING --> later change to unserialize
                                $options = explode(SETTING_DELIMITER, $options);
                                break;

                        case SETTING_TYPE_STRING:

                                $options = null;
                                break;

                        default:

                                // should not reach here, since db field for data_type is an 'enum'
                                break;
                }

                return $options;
        }

        /**
         * Edits a single value of a given category, key pair.
         * clears category from $this->settings if update successfull
         *
         * @param $category string
         * @param $key string
         * @param $value string
         *
         * @return bool
         */
        public function set($category, $key, $value) {

                $success = $this->ci->settings_model->edit_setting($category, $key, $this->_encode_setting($value));

                // clear entire category if edit was successfull.
                if ($success)
                        $this->clear($category);

                return $success;
        }

        /**
         * Edits a list of key-value pair for a given category.
         * clears category from $this->settings if update successfull
         *
         * @param $category string
         * @param $settings array $settings[key] = value
         *
         * @return bool
         */
        public function set_by_category($category, $settings) {

                /**
                 * verify if valid setting ( in case of list type )
                 */
                $this->ci->settings_model->transaction_strict();
                $this->ci->settings_model->transaction_begin();

                $success = true;

                // run through each key value pair, edit in db for each, break if key-value pair not found
                foreach ($settings as $key => $value) {

                        // edit value for given key and category
                        $success = $this->ci->settings_model->edit_setting($category, $key, $this->_encode_setting($value));

                        if ($success == false) {

                                break;
                        }
                }

                // commit or rollback
                if ($success) {

                        // clear current category, so that it reloads if required, since values have changed
                        $this->clear($category);
                        $this->ci->settings_model->transaction_commit();
                }
                else
                        $this->ci->settings_model->transaction_rollback();

                return $success;
        }

        /**
         * Serializes all value for settings
         */
        private function _encode_setting($value) {
                return serialize($value);
        }

        /**
         * Unserializes all values to get it back to original format
         */
        private function _decode_setting($value) {

//                $data = null;
//
//                try {
//                        $data = @unserialize($value);
//                } catch (Exception $exc) {
//                        $data = $value;
//                }
//
//                return $data;

                // unserialize data, suppress errors
                $data = @unserialize($value);

                // note: unserialize returns FALSE on 2 occasions
                // - the object is not serialized OR
                // - the serialized object is actually a boolean FALSE
                //
                // if unserialized data is ERROR, return original string, else return unserialized data
                return ( $data !== false || $value === serialize(false) ) ? $data : $value;
        }

        /**
         * Clears all category values ( if no param provided or 'all' provided )
         * clears individual category if single category provided
         *
         * @param $category string
         *
         * @return void
         */
        public function clear($category = 'all') {

                if ($category == 'all') {
                        unset($this->settings);
                } elseif (isset($this->settings[$category])) {
                        unset($this->settings[$category]);
                }
        }

}

?>
