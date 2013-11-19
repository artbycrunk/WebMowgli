<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of admin_library
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class admin_views {

        private $ci;

        public function __construct() {

                $this->ci = & get_instance();
        }

        public function get_site($uri, $parseData) {

                $output = null;


                return $output;
        }

        public function get_tab($uri, $parseData) {

                $output = null;


                return $output;
        }

        /**
         * Generates sidebar menu
         * - TEMPORARY METHOD
         *      - get modules from db
         *      - run through all modules
         *      - load respective module
         *      - call get_menu() --> returns menu structure in form of array
         *      - join to existing structure, parse into menu template
         */
        //loads sidebar from respective modules config file i.e. module_name/config/module_menu
        public function get_sidebar($parseData) {

                $sidebarMenuData = array();

                $this->ci->load->model('admin/admin_model');

                /* Get modules list from db ( temporarily load from modules folder ) */
                //$modules = $this->ci->admin_model->get_modules_for_menu();
                $modules = $this->_get_module_folder_list();
                $moduleResourceRoot = $this->ci->config->item('module_resource_root_uri');

                foreach ($modules as $module) {

                        $menu_config = $module . "_menu"; // Eg. module_menu

                        if ($this->ci->config->load($menu_config, true, true, $module)) {

                                $menuVisible = $this->ci->config->item('menu_visible', $menu_config);

                                // display menu only if menu_visbile = true in individual menu_config files
                                if ($menuVisible) {

                                        $menuName = $this->ci->config->item('menu_name', $menu_config);
                                        $menuUri = $this->ci->config->item('menu_uri', $menu_config);
                                        $menuDesc = $this->ci->config->item('menu_description', $menu_config);
                                        $subMenuArray = $this->ci->config->item('sub_menu', $menu_config);
                                        $moduleIcon = $this->ci->config->item('menu_icon', $menu_config); // relative to modules/module_name
                                        // format main menu url to get full url ( no matter what is sent from menu_config )
//                                        $menuUri = ( is_null($menuUri) OR $menuUri == '' ) ? "#" : site_url( str_replace( site_url(), "", $menuUri ) );
                                        $menuUri = '#';
                                        $moduleIcon = site_url("$moduleResourceRoot/$module/$moduleIcon");

                                        $subMenuData = array();

                                        foreach ($subMenuArray as & $subMenuItem) {

                                                // format sub-menu url to get full url ( no matter what is sent from menu_config )
                                                $subMenuItem['uri'] = site_url(str_replace(site_url(), "", $subMenuItem['uri']));

                                                $subMenuData[] = array(
                                                    'sidebar:menu:sub:name' => strtolower($subMenuItem['name']),
                                                    'sidebar:menu:sub:uri' => $subMenuItem['uri'],
                                                    'sidebar:menu:sub:description' => strtolower($subMenuItem['description'])
                                                );
                                        }

                                        $sidebarMenuData[] = array(
                                            'module' => strtolower($module),
                                            'sidebar:menu:name' => $menuName,
                                            'sidebar:menu:uri' => $menuUri,
                                            'sidebar:menu:description' => $menuDesc,
                                            'sidebar:menu:sub' => $subMenuData,
                                            'module:icon' => $moduleIcon
                                        );
                                } else {
                                        // do not display this menu item
                                }
                        }
                }



                /**
                 * @todo Final method to get menu items
                 * - FINAL METHOD
                 *      - at each module import
                 *      - run current method of geting menu items
                 *      - insert into admin menus table
                 *      - read only from admin menus table
                 */
                $parseData['sidebar:menu'] = $sidebarMenuData;
                return $this->ci->parser->parse('admin/includes/sidebar', $parseData, true);
        }

        public function get_head_resources($parseData) {

                $output = $this->ci->parser->parse('admin/includes/head', $parseData, true);

                return $output;
        }

        public function get_header($parseData) {

//                $output = $this->ci->parser->parse('admin/includes/header', $parseData, true);
                // temporarily removed top bar as it is not required.

                $output = null;

                return $output;
        }

        public function get_footer($parseData) {

                $output = null;


                return $output;
        }

        /**
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
        public function get_main_content($parseData, $title, $innerHtml, $tabId = null, $tabListHtml = null) {

                $parseData['main:page_name'] = $title;
                $parseData['main:tab_list'] = is_null($tabListHtml) ? '' : $tabListHtml;
                $parseData['main:tab_id'] = is_null($tabId) ? '' : $tabId;
                $parseData['main:tab_content'] = $innerHtml;

                return $this->ci->parser->parse('admin/includes/main', $parseData, true);
        }

        /**
         * Generates html for Tab list of main content area
         * if incorrect tab data provided --> returns empty string
         *
         * Arrays required
         *
         * $tabListData = array(
         *           array( 'tab_name' => value, 'tab_link' => value),
         *           array( 'tab_name' => value, 'tab_link' => value),
         *           array( 'tab_name' => value, 'tab_link' => value)
         *           );
         * -----------------------------
         *
         * @param array $parseData $this->parseData from calling controller
         * @param string $selectedTabLink link for current tab Eg. admin/template/manage/tab1
         * @param array $tabListData
         *
         * @return string Returns processed html for main content area
         */
        public function get_main_tab_list($parseData, $selectedTabLink, $tabListData) {

                $return = null;

                // identifier to distinguish selected tab from others
                $selectedTabIdent = 'active';

                //      if tab data NOT provided . . DO NOT display and tabs
                if (!is_null($tabListData) AND is_array($tabListData) AND count($tabListData) > 0) {

                        // process parse tags for tabs, links and selected tab
                        foreach ($tabListData as $tab) {

                                $parseData['main:tab_list'][] = array(
                                    // mark current tab as selected
                                    'main:tab_list:selected_tab' => ( $tab['tab_link'] == $selectedTabLink ? $selectedTabIdent : ''),
                                    'main:tab_list:tab_name' => $tab['tab_name'],
                                    'main:tab_list:tab_link' => $tab['tab_link']
                                );
                        }
                } else {

                        $return = '';
                }

                return $this->ci->parser->parse('admin/includes/main_tab_list', $parseData, true);
        }

        private function _get_module_folder_list() {

//                $modulesDir = module_path();
//                $directory_depth = 1;   // get files only from root dir
//
//                $this->ci->load->helper('directory');
//                $modules = directory_map( $modulesDir, $directory_depth );
//
//                return $modules;
                $this->ci->load->helper('directory');
                return get_module_dir_list();
        }

}

?>
