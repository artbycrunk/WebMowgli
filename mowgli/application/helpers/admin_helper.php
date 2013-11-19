<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('get_admin_main_content')) {

        /**
         * wraps provided content with admin panel, Main content
         * Calls admin_views->get_main_content(.....)
         * 
         * ... below docmentation from actual function...
         * 
         * Wraps the given html with the main content wrapper html
         * if $tabListHtml = null --> then do not create tabs
         *
         * @param array $parseData expects $this->parseData from calling class
         * @param string $title title of main content
         * @param string $innerHtml html for current tab to be displayed
         * @param string $tabId id attribute for current tab ( required only in case of specific css for tab )
         * @param string $tabListHtml html for list of tabs ( this has to be generated using 'get_main_tab_list' function )
         *
         * @return string final html of full main content area
         */
        function get_admin_main_content( $parseData, $title, $innerHtml, $tabId = null, $tabListHtml = null ) {

                $ci = & get_instance();
                
                $ci->load->library('admin/admin_views' );
                return $ci->admin_views->get_main_content( $parseData, $title, $innerHtml, $tabId, $tabListHtml );

        }

        
        
}


if(!function_exists('get_username')) {
        
        /**
         * Gets currently logged in username from user module
         * 
         * @return string
         */
        function get_username(){
                
                $ci = & get_instance();
                $ci->load->library('user/auth');
                return $ci->auth->get_username();
                
        }
        
}


?>
