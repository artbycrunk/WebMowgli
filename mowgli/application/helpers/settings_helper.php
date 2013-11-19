<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');


if(!function_exists('get_settings')) {

        /**
         * Gets settings from database helper function for site_settings->get() function of library Site_Settings
         *
         * //---------- excerpt from actual function documentation ----------
         *
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
         *
         */
        function get_settings( $category = 'all', $key = null ){

                $ci = & get_instance();

                $ci->load->library('site_settings');

                return $ci->site_settings->get( $category, $key );

        }

}

if(!function_exists('set_settings')) {

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
        function set_settings( $category, $key, $value ){

                $ci = & get_instance();

                $ci->load->library('site_settings');

                return $ci->site_settings->set($category, $key, $value);

        }

}

?>
