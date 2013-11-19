<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_url_lib
 *
 * @author Lloyd
 */
!defined('BLOG_PERMA_TYPE_STRING') ? define('BLOG_PERMA_TYPE_STRING', 'string') : null;
!defined('BLOG_PERMA_TYPE_NUMERIC') ? define('BLOG_PERMA_TYPE_NUMERIC', 'numeric') : null;
!defined('BLOG_PERMA_TYPE_OPTIONAL') ? define('BLOG_PERMA_TYPE_OPTIONAL', 'optional') : null;

class blog_url_lib {

        private $ci;
        private $configFile = 'blog_config';
        private $module = 'blog';
        //------------- Uri Params -----------

        /**
         * @var string Contains the blog uri of current request ( Eg. category_name/post_slug )
         */
        private $uri;

        /**
         * @var string Contains specific keyword for main area ( depending on url provided ).
         * Eg. 'summary' OR 'category_posts' OR 'tag_posts' OR 'date_posts' OR 'author_posts' OR 'single_post'
         */
        private $viewKeyword = null;

        /**
         * @var string
         * Contains permalink structure ( Eg. %category%/%post% OR %year%/%month%/%post% ),
         * this variable is mostly set from the database value provided
         */
        private $permalink;
        private $type;  // ( summary / group / post / custom )

        /**
         * @var array list of valid value for $this->type
         * Eg. array('summary', 'group', 'post', 'widget', 'custom');
         */
        private $allowedTypes = null;

        /**
         * @var array Will hold the key value pair for the group section
         *
         * Eg.
         * $group = array(
         *      'type' => 'category',
         *      'value' => 'uncategorized'
         *  );
         *
         * $group = array(
         *      'type' => 'date',
         *      'value' => array(
         *                      'year' => 2012,
         *                      'month' => 2,
         *                      'day' => 12
         *                      )
         *  );
         */
        private $group;
        private $page;

        /**
         * @var array Contains the conditions for the request ( extracted from uri )
         */
        private $permalinkParts = array(
            'author' => null,
            'category' => null,
            'tag' => null,
            'year' => null,
            'month' => null,
            'day' => null,
            'post' => null,
            'post_id' => null,
            'part' => null
        );
        private $conditions = null; // contains condition valu based on uri | array('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part' );
        private $permalinkTypes = array(
            BLOG_PERMA_TYPE_STRING => array('author', 'category', 'tag', 'post'),
            BLOG_PERMA_TYPE_NUMERIC => array('year', 'month', 'day', 'post_id', 'part'),
            BLOG_PERMA_TYPE_OPTIONAL => array('part')
        );
        private $error = null;

        public function __construct() {
                $this->ci = & get_instance();

                // load config files
                $this->ci->config->load($this->configFile, true, true, $this->module);

                $this->allowedTypes = $this->ci->config->item('allowed_data_types', $this->configFile);

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
        }

        /**
         * Convert view keyword ( mostly provided in blog settings 'default_view' ) to appropriate data type ( Eg. summary, group, widget etc )
         * get mapping between view Keyword and Data Type from config */
        private function _get_type_from_view_keyword($viewKeyword) {

                // get mapping between view Keyword and Data Type from config
                $view_type_map = $this->ci->config->item('map_default_viewkeyword_type', $this->configFile);

                return isset($view_type_map[$viewKeyword]) ? $view_type_map[$viewKeyword] : null;
        }

        /**
         * Decodes blog uri, sets various params required for blog rendering
         * params set are ( type, group, viewKeyword, conditions )
         *
         * should set
         *      - type = summary/group/post
         *      - group = date/author/category/tag ( ONLY for type=group )
         *      - viewKeyword = category_posts, tag_posts, single_post . . . etc
         *      - conditions -- mainly based on group or post id or post slug provided in url         *
         *
         * conditions for . . .
         * - tempType = summary ( nothing required )
         * - tempType = group ( groupType, groupSlug ), in case of date --> year,month,day
         * - tempType = post ( postId OR postSlug )
         */
        public function decode_uri($uri = null, $permalink = null) {

                $success = false;

                // if uri, permalnk not provided use class param values
                $uri = is_null($uri) ? $this->uri : $uri;
                $permalink = is_null($permalink) ? $this->permalink : $permalink;

                // check uri and set type, group, viewKeyword, conditions
                if ($uri == '') {

                        // decode uri with default view settings
                        $success = $this->_decode_uri_default();
                } else {

                        // check if uri is of group type, also set variables if it is
                        if ($this->_set_type_group($uri)) {

                                $this->set_type(BLOG_DATA_TYPE_GROUP);
                                $success = true;
                        }
                        // check if permalink structure matches, also set if it does
                        elseif ($this->_set_permalink_parts($uri, $permalink)) {

                                $this->set_type(BLOG_DATA_TYPE_POST);
                                $success = true;
                        } else {
                                $success = false;
                                $this->set_error('Url does not match any blog datatype, 404 error');
                        }

                        // use url params that are set to identify appropriate viewKeyword
                        $this->_identify_view_keyword();

                        // use class variables and generate appropriate conditions for further processing
                        // key values of conditions can be any of the following
                        // ('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part' )
                        $this->_generate_conditions();
                }

                return $success;
        }

        /**
         * Decodes uri when no uri is set ( uri = '' )
         * - gets view keyword from blog settings ( 'default_view )
         * - sets type
         * - sets viewKeyword
         * - sets conditions
         *
         * @return bool returns TRUE on success, FALSE if keyword set in settings is Invalid
         */
        private function _decode_uri_default() {

                $success = false;

                // get default_view from database
                $defaultViewKeyword = get_settings($this->module, $this->ci->config->item('setting_default_view', $this->configFile));

                // load list of valid viewKeywords for checking
                $allowedViewKeywords = $this->ci->config->item('view_keyword_list', $this->configFile);

                // check if default_view provided in blog settings
                if (in_array($defaultViewKeyword, $allowedViewKeywords)) {

                        // set type
                        $this->set_type($this->_get_type_from_view_keyword($defaultViewKeyword));

                        // ---------
                        // @Pending : set group for pagination of latest_posts, featured_posts . . etc
                        // ---------
                        // set view Keyword
                        $this->set_viewKeyword($defaultViewKeyword);

                        // set conditions
                        $this->_generate_conditions();
                        $success = true;
                } else {
                        $success = false;
                        $this->set_type(null);
                        $this->set_viewKeyword(null);
                        $this->set_conditions(null);
                        $this->set_error('Invalid default_view provided in blog settings');
                }

                return $success;
        }

        /**
         * identifies AND sets $this->viewKeyword according to dataType and groupType values
         *
         * @return bool;
         */
        private function _identify_view_keyword() {

                $success = false;

                switch ($this->get_type()) {
                        case BLOG_DATA_TYPE_SUMMARY :

                                $this->set_viewKeyword(BLOG_VIEW_SUMMARY);
                                $success = true;
                                break;

                        case BLOG_DATA_TYPE_GROUP :

                                // check group type
                                switch ($this->get_group('type')) {

                                        case BLOG_GROUP_TYPE_CATEGORY:

                                                $this->set_viewKeyword(BLOG_VIEW_CATEGORY_POSTS);
                                                $success = true;
                                                break;
                                        case BLOG_GROUP_TYPE_TAG:

                                                $this->set_viewKeyword(BLOG_VIEW_TAG_POSTS);
                                                $success = true;
                                                break;
                                        case BLOG_GROUP_TYPE_AUTHOR:

                                                $this->set_viewKeyword(BLOG_VIEW_AUTHOR_POSTS);
                                                $success = true;
                                                break;
                                        case BLOG_GROUP_TYPE_DATE:

                                                $this->set_viewKeyword(BLOG_VIEW_DATE_POSTS);
                                                $success = true;
                                                break;
                                        default:
                                                $success = false;
                                                $this->set_error('invalid view keyword in group type posts selected');
                                                break;
                                }

                                break;
                        case BLOG_DATA_TYPE_POST :

                                $this->set_viewKeyword(BLOG_VIEW_SINGLE_POST);
                                $success = true;
                                break;


                        default:
                                $success = false;
                                $this->set_error('invalid view keyword in main data type posts selected');
                                break;
                }

                return $success;
        }

        /**
         * Sets $this->conditions based on url params set
         */
        private function _generate_conditions() {

                $conditions = null;

                switch ($this->get_type()) {

                        case BLOG_DATA_TYPE_SUMMARY :

                                $conditions = null;
                                break;

                        case BLOG_DATA_TYPE_GROUP :

                                // check if group type = 'date'
                                // YES --> save conditions as ( year = YYYY, month = mm, day = dd )
                                // NO --> save according to groupType Eg. ( category = categ_name ) OR ( tag = tag_name ) OR ( author = author_name )
                                if ($this->get_group('type') == BLOG_GROUP_TYPE_DATE) {

                                        $dateArray = $this->get_group('value');

                                        $conditions = array(
                                            'year' => isset($dateArray['year']) ? $dateArray['year'] : null,
                                            'month' => isset($dateArray['month']) ? $dateArray['month'] : null,
                                            'day' => isset($dateArray['day']) ? $dateArray['day'] : null
                                        );
                                } else {

                                        $conditions = array($this->get_group('type') => $this->get_group('value'));
                                }

                                // add page number to conditions
                                $conditions['part'] = isset($this->page) ? $this->get_page() : null;

                                break;

                        case BLOG_DATA_TYPE_POST :

                                $conditions = $this->get_permalink_parts();
                                break;

                        default:
                                break;
                }

                $this->set_conditions($conditions);
        }

        public function set_uri($uri, $uriPrefix = null) {



                $uri = trim($uri, '/');
                $uriPrefix = trim($uriPrefix, '/');

                $length = strlen($uriPrefix);

                // check if prefix present at beginning of uri
                // if yes --> remove from string
                // if no ignore, do nothing
                if (!is_null($uriPrefix) AND substr($uri, 0, $length) === $uriPrefix) {

                        $uri = substr($uri, strlen($uriPrefix));
                }

                // remove beginning and ending slash '/' from uri
                $this->uri = trim($uri, '/');
        }

        public function get_uri() {
                return $this->uri;
        }

        public function get_conditions() {
                return $this->conditions;
        }

        public function set_conditions($conditions) {
                $this->conditions = $conditions;
        }

        /**
         * set permalink and set permalink parts ( OR conditions )
         */
        public function set_permalink($permalink) {

                // remove beginning and ending slash '/'
                $this->permalink = trim($permalink, '/');

                $this->_set_permalink_parts($this->uri, $this->permalink);
        }

        public function get_permalink() {

                return $this->permalink;
        }

        /**
         * get permalink parts ( if NOT already set --> then set permalink parts and get values )
         * set $this->permalink
         * set permalink parts
         */
        public function get_permalink_parts() {
//
//                if (!isset($this->permalinkParts) OR is_null($this->permalinkParts) OR !is_array($this->permalinkParts)) {
//
//                        $this->_set_permalink_parts();
//                }

                return $this->permalinkParts;
        }

        /**
         * returns value of specific permalink part from $this->permalinkParts
         *
         * @param string $key
         * @return string|null
         */
        private function get_permalink_part_value($key) {


                return isset($this->permalinkParts[$key]) ? $this->permalinkParts[$key] : null;
        }

        /**
         * Sets the permalink sections in $this->permalinkParts
         * Public function for _set_permalink_parts
         * if parameters NOT provided, assumes $this->uri and $this->permalink
         *
         * @param string $uri blog related uri ( NOTE: only requires blog section of URI, this may not be the full uri of the http request )
         * @param string $permalink permalink structure ( mostly from database )
         *
         * @return bool
         */
        public function set_permalink_parts($uri = null, $permalink = null) {

                $uri = is_null($uri) ? $this->uri : $uri;
                $permalink = is_null($permalink) ? $this->permalink : $permalink;

                $success = $this->_set_permalink_parts($uri, $permalink);

                return $success;
        }

        public function set_type($type) {
                $this->type = $type;
        }

        public function get_type() {
                return $this->type;
        }

        public function set_page($page) {
                $this->page = $page;
        }

        public function get_page() {
                return $this->page;
        }

        public function set_group($type, $value) {
                $this->group = array(
                    'type' => $type,
                    'value' => $value
                );
        }

        /**
         * returns $this->group
         * if key provided --> returns particular value
         * if Key NOT provided --> returns entire array ( $this->group )
         *
         * @param string $key
         * @return array|string
         */
        public function get_group($key = null) {

                $return = null;

                if (is_null($key)) {
                        $return = $this->group;
                } else {
                        $return = isset($this->group[$key]) ? $this->group[$key] : null;
                }

                return $return;
        }

        private function set_error($error) {
                $this->error = $error;
        }

        public function get_error() {
                return $this->error;
        }

        private function set_viewKeyword($viewKeyword) {
                $this->viewKeyword = $viewKeyword;
        }

        public function get_viewKeyword() {
                return $this->viewKeyword;
        }

        public function get_post_id() {

                return $this->get_permalink_part_value(BLOG_PERMA_KEY_POST_SLUG);
        }

        public function get_post_slug() {

                return $this->get_permalink_part_value(BLOG_PERMA_KEY_POST_SLUG);
        }

        /**
         * Checks if uri matches permalink structure, Also sets permalink parts as provided in uri
         * Splits uri and permalink Structure by delimiter '/'
         * iterates through permalink structure
         * validates uriString and permalinkPart for various tests
         *      Eg. constants, numeric, alphanumeric, optional
         * Sets $this->permalinkParts for provided parts from uri
         * returns true/false
         *
         * @param string $uri this is the uri for blog obtained from url
         * @param string $permalink this is the permalink structure
         *
         * @return bool
         */
        private function _set_permalink_parts($uri, $permalink) {

                $success = false;

                $uriParts = explode("/", $uri);
                $permaParts = explode("/", $permalink);

                $this->_reset_permalink_parts();

                // check if uri has more sections than mentioned in Permalink
                // yes -> error --> 404
                // no --> continue
                if (count($uriParts) > count($permaParts)) {

                        $success = false;
                        $this->set_error("uri does NOT match permalink, additional sections found in uri");
//                        return $success;
                } else {

                        // iterate through all permalinkSections,
                        // check for constants, and other types
                        // set appropriate permalink values in main array ( $this->permalinkParts )
                        for ($count = 0; $count < count($permaParts); $count++) {

                                $permaSection = $permaParts[$count];

                                // remove % from start and end
                                // note: this $key value will be overriden accordingly later
                                $key = null;

                                // check if uri section provided
                                // if NOT --> check if section is of optional type
                                if (isset($uriParts[$count])) {

                                        $uriSection = $uriParts[$count];

                                        // check if strings match exactly
                                        if ($uriSection == $permaSection) {

                                                // constants, ignore, move to next
                                                $success = true;
                                                continue;
                                        }
                                        // search for string starting with %, ending with %,
                                        // and has numeric/alpha-numeric in between
                                        // also checks if valid permalink key provided
                                        elseif ($this->_check_permalink_section($permaSection, $uriSection, $key)) {

                                                $this->_add_permalink_part($key, $uriSection);

                                                $success = true;
                                                continue;
                                        } else {
                                                // no match
                                                $success = false;
                                                $this->set_error("Uri does NOT match Permalink");
                                                break;
                                        }
                                } else {

                                        $key = trim($permaSection, '%');

                                        // Current uri section NOT provided.
                                        // check if permaSection is of optional type
                                        if (array_key_exists($key, $this->permalinkTypes[BLOG_PERMA_TYPE_OPTIONAL])) {

                                                $success = true;
                                        } else {
                                                $success = false;
                                                $this->set_error("Missing permalink value");
                                        }

                                        break;
                                }
                        }
                }


                return $success;
        }

        /**
         * Checks if the provided key is valid ( exists in $this->permalinkParts )
         * adds the value for that key
         *
         * @param string $key
         * @param string $value
         *
         * @return bool true if key found, false if NOt found
         */
        private function _add_permalink_part($key, $value) {

                $success = false;

                if (array_key_exists($key, $this->permalinkParts)) {

                        $this->permalinkParts[$key] = $value;
                        $success = true;
                } else {
                        // invalid key provided for permalink part
                        $success = false;
                }

                return $success;
        }

        /**
         * Checks a particular section of the permalink and correcponding url
         * - checks for string starting with %, ending with %, and has some text inbetween
         * - checks for numeric permaTypes ( uri section should be numeric )
         * - checks if permalink provided is valid
         *
         * @param string $permaSection permalink section E.g. %category%, %post%
         * @param string $uriSection current part of the uri, matching the perma section
         * @param ref $key string variable will hold the current permalink key ( Eg. category, post )
         *
         * @return bool
         */
        private function _check_permalink_section($permaSection, $uriSection, & $key) {

                $success = false;

                $match = null;
                $alphaNumPermaKeys = $this->permalinkTypes['string'];
                $numPermaKeys = $this->permalinkTypes['numeric'];

                // search for string starting with %, ending with %, and has some text inbetween
                if ((bool) preg_match("/^%(.*)%$/", $permaSection, $match)) {

                        $key = $match[1];       // part found in the 1st (.*) of regex
                        // check is current perma section is of type Numeric
                        if (in_array($key, $numPermaKeys)) {

                                // check if value in uri is of type numeric
                                if (is_numeric($uriSection)) {
                                        $success = true;
                                } else {
                                        $success = false;
                                        $this->set_error("Current permalink section ( $key ) expects a numeric datatype");
                                }
                        }
                        // check if permaSection is of type alpha-numeric
                        elseif (in_array($key, $alphaNumPermaKeys)) {

                                $success = true;
                        } else {
                                // current permalink key is Invalid
                                // it is not among list of allowed permalink keys
                                $success = false;
                                $this->set_error("invalid permalink section ( $key ) found ");
                        }
                } else {

                        // current perma section does not match given condition
                        $success = false;
                        $this->set_error("permalink section ( $key ) is invalid, please check permalink formats");
                }

                return $success;
        }

        /**
         * resets all permalink parts to null
         */
        private function _reset_permalink_parts() {

                foreach ($this->permalinkParts as $key => $value) {
                        $this->permalinkParts[$key] = null;
                }
        }

        public function _set_type_group($uri) {

                $success = false;

                $groupType = null; // possible values ( category / author / tag / date )
                $groupValue = null; // will hold groupSlug OR date array

                $dateType = null; // possible values ( 'year' OR 'year/month' OR 'year/month/day' )
                $date = array(
                    'year' => null,
                    'month' => null,
                    'day' => null
                );
                $page = null; // will hold 'page/03' OR 'page/003'

                $keys = "author|category|tag";

                // allow only starting with 19 Or 20 and remaining 2 have to be digits
                $yearPattern = "((19|20)\d\d)";
                // 1 to 9 OR 01 to 09 OR 10 OR 11 OR 12
                $monthPattern = "([1-9]|0[1-9]|1[012])";
                // 1-9, 01-09, 10-29, 30-31
                $dayPattern = "([1-9]|0[1-9]|[12][0-9]|3[01])";
                // ( slash OR /page/(1 to any number) ) 0 or 1 time
                $endPattern = "(\/|\/page\/([0-9]+\/?))?";

                $regExDate = array(
                    'year' => "/^$yearPattern" . $endPattern . "$/",
                    'year/month' => "/^$yearPattern\/$monthPattern" . $endPattern . "$/",
                    'year/month/day' => "/^$yearPattern\/$monthPattern\/$dayPattern" . $endPattern . "$/",
                );

                $regEx = array(
//                    'group' => "/^($keys){1}(\/.+)/i",
                    'group' => "/^($keys){1}\/([a-zA-Z0-9_\-]+)$endPattern$/i",
                    'date' => $regExDate
                );

                $matches = null;

                // test for group type = 'category' OR 'tag' OR 'author'
                if ((bool) preg_match($regEx['group'], $uri, $matches, PREG_OFFSET_CAPTURE)) {

                        // match found
                        $success = true;
                        $groupType = trim($matches[1][0], '/');

                        // verify if grouptype is valid ( category/tag/author )
                        $groupType = ( $groupType == BLOG_GROUP_TYPE_AUTHOR OR $groupType == BLOG_GROUP_TYPE_CATEGORY OR $groupType == BLOG_GROUP_TYPE_TAG ) ? $groupType : null;

                        // get the group slug value, remove starting and end slashes
                        $groupValue = trim($matches[2][0], '/');
//                        $page = isset($matches[4][0]) ? trim($matches[4][0], '/') : null;
                        $page = $this->_get_page_no_from_uri($uri);
//                        $matches = null;
                } else {

                        // test for date type
                        foreach ($regEx['date'] as $_dateType => $pattern) {

                                if ((bool) preg_match($pattern, $uri, $matches, PREG_OFFSET_CAPTURE)) {

                                        // match found
                                        $success = true;

                                        $dateType = $_dateType;

                                        switch ($_dateType) {
                                                case 'year':
                                                        $date['year'] = isset($matches[1][0]) ? $matches[1][0] : null;
                                                        break;
                                                case 'year/month':
                                                        $date['year'] = isset($matches[1][0]) ? $matches[1][0] : null;
                                                        $date['month'] = isset($matches[3][0]) ? $matches[3][0] : null;
                                                        break;
                                                case 'year/month/day':
                                                        $date['year'] = isset($matches[1][0]) ? $matches[1][0] : null;
                                                        $date['month'] = isset($matches[3][0]) ? $matches[3][0] : null;
                                                        $date['day'] = isset($matches[4][0]) ? $matches[4][0] : null;
                                                        break;
                                                default:
                                                        break;
                                        }

                                        $groupType = BLOG_GROUP_TYPE_DATE;
                                        $groupValue = $date;
                                        $page = $this->_get_page_no_from_uri($uri);
                                        break;
                                } else {
                                        $success = false;
                                }
                        }
                }


                if ($success) {


                        $this->set_group($groupType, $groupValue);
                        $this->set_page($page);

                        // note: $dateType also available ( year Or 'year/month' etc )
                }


                return $success;
        }

        /**
         * Check if the given uri requires redirection
         * returns true on success, false on failure
         * returns the redirected uri as a referance
         *
         * @param string $uri blog specific uri ( Eg. /category/categ_name/page/001 )
         * @param ref $redirect will hold value string|null ( Eg. /category/categ_name/page/1 )
         *
         * @return bool
         * @return ref $redirect string|null
         */
        private function _check_uri_redirect($uri, & $redirect) {

                $success = false;


                $redirectPattern = array(
                    'condition' => "/page\/(0+)([1-9]+)$/", // check if uri ends with 'page/000some_number' ( i.e. leading zeros )
                    'replacement' => "page/$2"                  // replace by removing leading zeros 'page/some_number'
                );

                $condition = $redirectPattern['condition'];
                $replacement = $redirectPattern['replacement'];

                $redirect = preg_filter($condition, $replacement, $uri);

                // check if $redirect is null -->
                if (!is_null($redirect)) {

                        // uri needs to be redirected
                        $success = true;
                } else {
                        $success = false;
                }

                return $success;
        }

        private function _get_page_no_from_uri($uri) {

                $return = null;

                $pattern = "/\/page\/([0-9]+\/?)$/";

                if ((bool) preg_match($pattern, $uri, $matches, PREG_OFFSET_CAPTURE)) {
                        $return = isset($matches[1][0]) ? $matches[1][0] : null;
                } else {

                        $return = null;
                }

                return $return;
        }

}

?>
