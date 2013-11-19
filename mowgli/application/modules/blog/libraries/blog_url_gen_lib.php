<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_url_gen_lib
 *
 * @author Lloyd
 */
class blog_url_gen_lib {

        private $ci;
        private $configFile = 'blog_config';
        private $module = 'blog';
        //------------- Uri Params -----------

        private $blogPrefix = null;
        private $permalink;
        private $allowedPermaSections = null;
//        private $type;  // ( summary / group / post / custom )

        /**
         * @var array list of valid value for $this->type
         * Eg. array('summary', 'group', 'post', 'widget', 'custom');
         */
//        private $allowedTypes = null;
        private $error = null;

        public function __construct() {
                $this->ci = & get_instance();

                // load config files
                $this->ci->config->load($this->configFile, true, true, $this->module);

//                $this->allowedTypes = $this->ci->config->item('allowed_data_types', $this->configFile);

                $this->set_blogPrefix( trim( get_module_prefix( $this->module ), '/' ) . '/' ) ;

                $permalink = get_settings($this->module, $this->ci->config->item('setting_permalink', $this->configFile));
                $this->set_permalink($permalink);

                // get valid permalink sections
                $this->allowedPermaSections = $this->ci->config->item('allowed_perma_parts', $this->configFile);

                // view type constants
                !defined('BLOG_DATA_TYPE_SUMMARY') ? define('BLOG_DATA_TYPE_SUMMARY', $this->ci->config->item('data_type_summary', $this->configFile)) : null;
                !defined('BLOG_DATA_TYPE_GROUP') ? define('BLOG_DATA_TYPE_GROUP', $this->ci->config->item('data_type_group', $this->configFile)) : null;
                !defined('BLOG_DATA_TYPE_POST') ? define('BLOG_DATA_TYPE_POST', $this->ci->config->item('data_type_post', $this->configFile)) : null;
                !defined('BLOG_DATA_TYPE_WIDGET') ? define('BLOG_DATA_TYPE_WIDGET', $this->ci->config->item('data_type_widget', $this->configFile)) : null;
                !defined('BLOG_DATA_TYPE_CUSTOM') ? define('BLOG_DATA_TYPE_CUSTOM', $this->ci->config->item('data_type_custom', $this->configFile)) : null;

                // group type constants
                !defined('BLOG_GROUP_TYPE_AUTHOR') ? define('BLOG_GROUP_TYPE_AUTHOR', $this->ci->config->item('data_group_type_author', $this->configFile)) : null;
                !defined('BLOG_GROUP_TYPE_CATEGORY') ? define('BLOG_GROUP_TYPE_CATEGORY', $this->ci->config->item('data_group_type_category', $this->configFile)) : null;
                !defined('BLOG_GROUP_TYPE_TAG') ? define('BLOG_GROUP_TYPE_TAG', $this->ci->config->item('data_group_type_tag', $this->configFile)) : null;
                !defined('BLOG_GROUP_TYPE_DATE') ? define('BLOG_GROUP_TYPE_DATE', $this->ci->config->item('data_group_type_date', $this->configFile)) : null;

                // view keyword constants
                !defined('BLOG_VIEW_SUMMARY') ? define('BLOG_VIEW_SUMMARY', $this->ci->config->item('view_summary', $this->configFile)) : null;
                !defined('BLOG_VIEW_CATEGORY_POSTS') ? define('BLOG_VIEW_CATEGORY_POSTS', $this->ci->config->item('view_category_posts', $this->configFile)) : null;
                !defined('BLOG_VIEW_TAG_POSTS') ? define('BLOG_VIEW_TAG_POSTS', $this->ci->config->item('view_tag_posts', $this->configFile)) : null;
                !defined('BLOG_VIEW_AUTHOR_POSTS') ? define('BLOG_VIEW_AUTHOR_POSTS', $this->ci->config->item('view_author_posts', $this->configFile)) : null;
                !defined('BLOG_VIEW_DATE_POSTS') ? define('BLOG_VIEW_DATE_POSTS', $this->ci->config->item('view_date_posts', $this->configFile)) : null;
                !defined('BLOG_VIEW_SINGLE_POST') ? define('BLOG_VIEW_SINGLE_POST', $this->ci->config->item('view_single_post', $this->configFile)) : null;

                // post_id, post_slug keys for permaParts
                !defined('BLOG_PERMA_KEY_POST_ID') ? define('BLOG_PERMA_KEY_POST_ID', 'post_id') : null;
                !defined('BLOG_PERMA_KEY_POST_SLUG') ? define('BLOG_PERMA_KEY_POST_SLUG', 'post') : null;

                // separators
                !defined('BLOG_SEPARATOR_CATEGS') ? define('BLOG_SEPARATOR_CATEGS', $this->ci->config->item('separator_categs', $this->configFile)) : null;
                !defined('BLOG_SEPARATOR_TAGS') ? define('BLOG_SEPARATOR_TAGS', $this->ci->config->item('separator_tags', $this->configFile)) : null;
        }

        public function get_blogPrefix() {
                return $this->blogPrefix;
        }

        public function set_blogPrefix($blogPrefix) {
                $this->blogPrefix = $blogPrefix;
        }


        /**
         * set permalink and set permalink parts ( OR conditions )
         */
        public function set_permalink($permalink) {

                // remove beginning and ending slash '/'
                $this->permalink = trim($permalink, '/');
        }

        public function get_permalink() {

                return $this->permalink;
        }

        public function set_type($type) {
                $this->type = $type;
        }

        public function get_type() {
                return $this->type;
        }

        public function get_error() {
                return $this->error;
        }

        public function set_error($error) {
                $this->error = $error;
        }

        //--------------------------------

        /**
         * Generates array of links OR string of links, seperated by a provided separator
         *
         * @param string|array $textArray string OR array of anchor texts
         * @param string|array $uriArray string OR array of URIs ( corresponding to $textArray )
         * @param string|array $titleArray string OR array of titles corresponding to $textArray
         * @param bool $isConvertToString whether output should be in array form or string form separated by separator
         * @param string $separator used if $isConvertToString = true
         *
         * @return array|string
         */
        private function create_links($textArray, $uriArray, $titleArray = null, $isConvertToString = false, $separator = BLOG_SEPARATOR_CATEGS) {

                $links = null;

                if (is_string($textArray) AND is_string($uriArray)) {

                        // convert to array
                        $textArray = array($textArray);
                        $uriArray = array($uriArray);
                        $titleArray = array($titleArray);
                }

                if (is_array($textArray) AND is_array($uriArray)) {

                        foreach ($textArray as $count => $text) {

                                $uri = isset($uriArray[$count]) ? $uriArray[$count] : "#";
                                $attributes = array(
                                    'title' => isset($titleArray[$count]) ? $titleArray[$count] : ''
                                );

//                                $prefix = isset($this->blogPrefix) ? $this->blogPrefix . "/" : null;

                                $links[] = anchor(site_url($uri), $text, $attributes);
                        }
                }

                // convert to string ( if $isConvertToString = true ) seperated by separator
                if ($isConvertToString) {

                        $links = implode($separator, $links);
                }

                return $links;
        }

        public function get_categ_urls($slugArray, $textArray = null, $titleArray = null, $isConvertToString = true, $separator = BLOG_SEPARATOR_CATEGS) {

                $uriArray = null;
                $return = null;

                if (!is_null($slugArray) AND is_array($slugArray) AND count($slugArray) > 0) {

                        foreach ($slugArray as $slug) {
                                $uriArray[] = $this->get_uri_categ($slug);
                        }

                        // if text and title are not set, use slug to generate values
                        $textArray = is_null($textArray) ? $slugArray : $textArray;
                        $titleArray = is_null($titleArray) ? $slugArray : $titleArray;

                        $return = $this->create_links($textArray, $uriArray, $titleArray, $isConvertToString, $separator);
                }

//                $uriArray = null;
//                foreach ($slugArray as $slug) {
//                        $uriArray[] = $this->get_uri_categ($slug);
//                }

                return $return;
        }

        public function get_tag_urls($slugArray, $textArray = null, $titleArray = null, $isConvertToString = true, $separator = BLOG_SEPARATOR_CATEGS) {

                $return = null;

                if (!is_null($slugArray) AND is_array($slugArray) AND count($slugArray) > 0) {

                        $uriArray = null;
                        foreach ($slugArray as $slug) {
                                $uriArray[] = $this->get_uri_tag($slug);
                        }

                        // if text and title are not set, use slug to generate values
                        $textArray = is_null($textArray) ? $slugArray : $textArray;
                        $titleArray = is_null($titleArray) ? $slugArray : $titleArray;

                        $return = $this->create_links($textArray, $uriArray, $titleArray, $isConvertToString, $separator);
                }




                return $return;
        }

        public function get_uri_tag($slug, $page = null ) {

                $uriStructure = "tag/%tag%";
                $pageString =  ! is_null($page) ? "/page/$page" : null;
                $uri = $this->blogPrefix . str_replace("%tag%", trim($slug), $uriStructure) . $pageString;
                return $uri;
        }

        public function get_uri_categ($slug, $page = null ) {

                $uriStructure = "category/%category%";
                $pageString =  ! is_null($page) ? "/page/$page" : null;
                $uri = $this->blogPrefix . str_replace("%category%", trim($slug), $uriStructure) . $pageString;
                return $uri;
        }

        public function get_uri_author($slug, $page = null ) {

                $uriStructure = "author/%author%";
                $pageString =  ! is_null($page) ? "/page/$page" : null;
                $uri = $this->blogPrefix . str_replace("%author%", trim($slug), $uriStructure) . $pageString;
                return $uri;
        }

        public function get_uri_date($year, $month = null, $day = null, $page = null ) {

                $uriStructure = "%year%/%month%/%day%";

                $uri = $uriStructure;
                $uri = str_replace("%year%", trim($year), $uri);
                $uri = str_replace("%month%", ltrim(trim($month), 0), $uri);    // remove white spaces ( from both ends ) and remove 0 from start of number
                $uri = str_replace("%day%", ltrim(trim($day), 0), $uri);        // remove white spaces ( from both ends ) and remove 0 from start of number

                $pageString =  ! is_null($page) ? "/page/$page" : null;

                $uri = $this->blogPrefix . trim($uri . $pageString, '/');
                return $uri;
        }

        public function get_uri_post($conditions) {

                $permaParts = explode('/', $this->permalink);

                $uri = $this->permalink;

                if (is_array($permaParts)) {

                        // run thorugh each section of saved permalink structure
                        foreach ($permaParts as $section) {

                                // get keyword, remove % ... % from start and end of section
                                // Eg. get 'category' from '%category%'
                                $keyword = trim($section, "%");

                                if (in_array($section, $this->allowedPermaSections)) {

                                        $replace = isset($conditions[$keyword]) ? $conditions[$keyword] : '';
                                        $uri = str_replace($section, $replace, $uri);
                                }
                        }
                }


                return $this->blogPrefix . trim($uri, '/');
        }

}

?>
