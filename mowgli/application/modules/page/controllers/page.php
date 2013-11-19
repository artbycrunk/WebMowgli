<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Module_Import
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';
require_once APPPATH . 'libraries/Request.php';
require_once APPPATH . 'libraries/Comments.php';

!defined('VIEW_METHOD') ? define('VIEW_METHOD', 'view') : null;

class Page extends Site_Controller {

        private $url = null;
        private $safeUrl = null;
        private $is404 = false;
        private $isCaching = true; // this variable is only for testing, for live production use true

        public function __construct() {

                parent::__construct();

//                require_once APPPATH . "core/I_Page.php";
//                require_once APPPATH . "core/I_Admin.php";
                // set caching as true in Production environment
                if (defined('ENVIRONMENT') AND ENVIRONMENT == 'production')
                        $this->isCaching = true;

                $this->url = current_url();
                $this->safeUrl = url_title(uri_string());

                // Load libraries
                $this->load->library('user/auth');
                $this->load->library('page/page_library');
                $this->load->library('site_settings');
                $this->load->library('form_response');
                $this->load->library('form_validation');
                // required for form validation to work with hmvc
                // $this->form_validation->CI = & $this;;

                // Load helpers
                $this->load->helper('config');
                $this->load->helper('directory');
                //
                // Load models
                $this->load->model('page/page_model');
                $this->load->model('page/page_edit_model');
        }

        public function _remap($page, $params = array()) {

                /* check if overriding method defined in THIS or PARENT class */
                if (!method_exists($this, $page)) {

                        // get cache copy
                        $output = $this->_get_cache($page);

                        // if cached copy not available, generate page normally, also save cache if required
                        if (is_null($output) OR $output === false) {

                                $output = $this->_generate_page($page, $params);
                        }

                        echo $output;
                } else {

                        /* method defined in this class go directly to method */
                        call_user_func_array(array($this, $page), $params);
                }
        }

        private function _get_cache() {

                $output = null;

                // $request = new Request();
                $request = Request::get_instance();

                //      Check if logged in, set variable for future use.
                $isLoggedIn = $request->get('is_logged');

                $allowCaching = (!$this->is404 AND !$isLoggedIn AND $this->isCaching);

                if ($allowCaching) {

                        // get cached copy
                        // do not implement caching if 404 OR logged in OR caching turned OFF
                        $this->cache->allow_caching($allowCaching);
                        $output = $this->cache->get(WM_CACHE_PAGE_PREFIX . $this->safeUrl);

                        $output = ( $output === false ) ? null : $output;
                } else {

                        $output = null;
                }

                return $output;
        }

        /**
         * Generates full page
         * caches page when not logged
         */
        private function _generate_page($page, $params = array()) {

                $output = null;

                // $request = new Request();
                $request = Request::get_instance();

                $uri = $request->get('uri');

                $relativeUri = substr($uri, strlen($page));
                $relativeUri = ( $relativeUri !== false ) ? $relativeUri : null;

                $request->set('uri_relative', $relativeUri);

                $isLoggedIn = $request->get('is_logged');

//                $isSpecialPage = $this->_is_special_page($page);
                // cache expired OR not available
                // process normally
                // Home page / landing page
                // if ( $page = index) AND uristring is null --> call home page from settings
                $page = ( $page == "index" AND strlen(uri_string()) == 0 ) ? $this->site_settings->get(WM_SET_CATEG_GENERAL, 'home_page') : $page;

                // check if page in uri begins with underscore '_' ( i.e. special page )
                // if begins with _ --> 404 error
                if (!(bool) preg_match("/^_(.*)/i", $page)) {

                        // get alternate page name from modules if defined as unique urls
                        $page = $this->_get_module_special_page($page);

                        // Get page details from database
                        // $pageDetails = array( id, temp_id, title, description, keywords, html )
                        $pageDetails = $this->page_model->get_page_details($page);


                        $request->set('page_id', $pageDetails['id']);
                        $request->set('temp_id', $pageDetails['temp_id']);

                        //      Check if page exists in database and process, if NOT show 404 error
                        if ($pageDetails !== false) {

                                // page exists
                                //
                        // $position = new Template_positions($params);
                                $position = Template_positions::get_instance();
                                $position->clear();

                                // insert base position to make all links relative to site_url()
                                $position->add('head', 'start', "<base href='" . site_url() . "'/>");

                                // modify page if logged in admin widget to front end if logged in
                                if ($isLoggedIn) {

                                        $jsSiteUrl = "<script type='text/javascript'>
								var site_url = '" . site_url() . "';
								var wm_environment = '" . ENVIRONMENT . "';
							</script>";
                                        $position->add('head', 'start', $jsSiteUrl);
                                        unset($jsSiteUrl);

					//$this->load->library('user/auth')

                                        $this->parseData['page:id'] = $pageDetails['id'];
					$this->parseData['admin:user'] = $this->auth->get_username();

                                        $frontAdminHead = $this->parser->parse('admin/front/head', $this->parseData, true);
                                        $frontAdminBody = $this->parser->parse('admin/front/body', $this->parseData, true);

                                        // inject admin bar at top left portion of page when logged in.
                                        $position->add('head', 'start', $frontAdminHead);
                                        $position->add('body', 'start', $frontAdminBody);
                                } else {

                                        // certain things to do when not logged in
                                        // insert analytics code
                                        $position->add('head', 'end', $this->site_settings->get(WM_SET_CATEG_GENERAL, 'analytics'));
                                }


                                // --------- call page_lib->render() ----------- here
                                $pageDetails = $this->page_library->render($pageDetails, $params);

                                //---------- change made here ------------
                                // set meta data from block modules, if set.
                                $pageDetails['title'] = is_null($position->get_meta('title')) ? $pageDetails['title'] : $position->get_meta('title');
                                $pageDetails['description'] = is_null($position->get_meta('description')) ? $pageDetails['description'] : $position->get_meta('description');
                                $pageDetails['keywords'] = is_null($position->get_meta('keywords')) ? $pageDetails['keywords'] : $position->get_meta('keywords');

                                // add 'site name' at the end of title
                                $pageDetails['title'] .= " " . $this->site_settings->get(WM_SET_CATEG_GENERAL, 'site_name');

                                $this->parseData["page:title"] = $pageDetails['title'];
                                $this->parseData["page:description"] = $pageDetails['description'];
                                $this->parseData["page:keywords"] = $pageDetails['keywords'];

				// load comments
//				$comments = new Comments();
				$comments = Comments::get_instance();
				$this->parseData["comments"] = $comments->get_comment_script();
				$position->add('body', 'end', $comments->get_count_script() );

                                $output = $position->render($pageDetails['html'], $this->parseData);

                                // cache current page
                                $this->_cache_page($output);
                        } else {

                                // page NOT found in database
                                // 404

                                $this->is404 = true;

                                $this->show_404();

                                // note: scrpt execution terminated after 404 is called
                        }
                } else {

                        // page begins with underscore, special page being called directly
                        // 404

                        $this->is404 = true;

                        $this->show_404();

                        // note: scrpt execution terminated after 404 is called
                }



                return $output;
        }

        private function _cache_page(& $output) {

                $filename = WM_CACHE_PAGE_PREFIX . $this->safeUrl;
                $expires = null;
                $dependencies = null;

                $request = Request::get_instance();

                // Check if logged in, set variable for future use.
                $isLoggedIn = $request->get('is_logged');

                $allowCaching = (!$this->is404 AND !$isLoggedIn AND $this->isCaching);

                // save current page output to Cache ONLY if --> not 404, not logged in, caching enabled
                $this->cache->allow_caching($allowCaching);
                $this->cache->write($output, $filename, $expires, $dependencies);
        }

        public function show_404($errorMessage = null) {

                /**
                 * @todo use $errorMessage for logs OR for some useful display
                 */
                $page404 = $this->site_settings->get(WM_SET_CATEG_GENERAL, 'page_404');

                // Check if 404 page available in db
                if ($this->page_model->check_page_exists($page404)) {

                        $this->output->set_output('');
                        $this->output->set_status_header("404", 'Page NOT Found');

                        $this->_remap($page404, null);
                } else {
                        // 404 page not available in database, display CI 404 page, or custom 404 page.
                        show_404();
                }

                exit;
        }

        private function _get_module_special_page($page) {

                $module = null;

                // load config files, get all modules from folder structure, return value is arranged module wise
                $moduleConfigs = get_config_array(WM_MODULE_HAS_UNIQUE_URLS);

                // get settings data from database
                $settingPrefixes = $this->site_settings->get(WM_SET_CATEG_URL, 'module_url_prefixes');


                // check if valid arrays
                if (is_array($settingPrefixes) AND is_array($moduleConfigs)) {

                        // calculate pending modules
                        // ( this array has a list of all module that DO NOT support unique URLs )
                        // It may also have modules that SUPPORT unique URLs, but their key-value pair is NOT yet set in settings
                        // get all modules ( keys ) from $moduleConfigs that are NOT present as keys in $settingPrefixes
                        $excludedModules = array_diff_key($moduleConfigs, $settingPrefixes);

                        // check if current page is defined in module Prefixes
                        if (in_array($page, $settingPrefixes)) {

                                // get module name for current page found ( find array key )
                                $module = array_search($page, $settingPrefixes);

                                // note: array_search return false if key NOT found
                                $module = ( $module !== false ) ? $module : null;
                        }
                        // check if current page matches with a module name
                        elseif (in_array($page, $excludedModules)) {

                                // no module associated with current page name for unique URLS
                                // HOWEVER
                                // - module is newly installed and url_prefix is NOT set in settings for these new modules
                                // Hence assuming page name matches module name
                                // ---------------------------------------
                                //
                        //
                        // get module name for current page found ( find array key )
                                $module = array_search($page, $excludedModules);

                                // note: array_search return false if key NOT found
                                $module = ( $module !== false ) ? $module : null;
                        }

                        // load module controller, call module function, get modified page name
                        if (!is_null($module) AND isset($module)) {

                                if (!is_null($this->load->module("$module/$module"))) {

                                        $method = "_get_special_page";

                                        $pageNew = $this->$module->$method($page);

                                        if (!is_null($pageNew) AND $pageNew !== false) {

                                                // if new page DOES NOT start with underscore, prefix underscore to page
                                                if (!(bool) preg_match("/^_(.*)/", $pageNew)) {

                                                        $page = "_" . $pageNew;
                                                }
                                        }
                                }
                        }
                }




                return $page;
        }

//        private function _is_special_page($page) {
//
//                $isSpecialPage = false;
//
//                // load config files, get all modules, config from folder structure, return value is arranged module wise
//                $moduleConfigs = get_config_array(array(WM_MODULE_HAS_UNIQUE_URLS, WM_MODULE_SPECIAL_PAGES));
//
//                foreach ($moduleConfigs as $module => $config) {
//
//                        if ($config[WM_MODULE_HAS_UNIQUE_URLS] === true) {
//
//                                $specialPagesList = $config[WM_MODULE_SPECIAL_PAGES];
//
//                                if (in_array($page, $specialPagesList)) {
//
//                                        $isSpecialPage = true;
//                                        break;
//                                }
//                        }
//                }
//
//                return $isSpecialPage;
//        }
}

?>
