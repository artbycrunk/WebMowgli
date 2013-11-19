<?php

/**
 * Description of Template_positions
 * uses singleton pattern
 * can be used to insert text at predefined positions on an html document
 * can insert parsetags into the specific sections
 * render parsetags
 * static values can hold data from multiple modules
 * text of a particular position is scanned to remove duplicate entries
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
defined("POSITION_BEFORE") ? null : define("POSITION_BEFORE", 'before');
defined("POSITION_ATTRIB") ? null : define("POSITION_ATTRIB", 'attrib');
defined("POSITION_START") ? null : define("POSITION_START", 'start');
defined("POSITION_END") ? null : define("POSITION_END", 'end');
defined("POSITION_AFTER") ? null : define("POSITION_AFTER", 'after');

defined("ENTRY_SEPARATOR_ATTRIB") ? null : define("ENTRY_SEPARATOR_ATTRIB", " "); // space
defined("ENTRY_SEPARATOR_GENERAL") ? null : define("ENTRY_SEPARATOR_GENERAL", "\n"); // newline

class Template_positions {

        private static $instance = null;
        private $ci;

        /** sample positions
          'html-before', 'html-in', 'html-start', 'html-end', 'html-after' ),
          'head-before', 'head-in', 'head-start', 'head-end', 'head-after',
          'body-before', 'body-in', 'body-start', 'body-end', 'body-after'
         */
        private $parseTags = array();   // eg. 'prefix-html-start'
        private $tagPrefix = 'position';
        private $htmlTags = array('head', 'body', 'html');    // note: keep html last, does not work if parent is at beginning
        private $positions = array(
            POSITION_BEFORE,
            POSITION_ATTRIB,
            POSITION_START,
            POSITION_END,
            POSITION_AFTER
        );
        // will hold individual meta values like title, keywords, description . . . etc
        private $meta = array();
        private $errors = array();

        private function __construct($params = array()) {

                // prevent creation of object externally

                $this->ci = & get_instance();

                // initialize final position tags
                $this->_set_parse_tags();

                $this->ci->load->library('domparser');
        }

        private function __clone() {
                // do nothing, prevent Copy of object
        }

        /* Get instance for Singleton pattern */

        public static function get_instance() {

                if (!isset(self::$instance)) {

                        self::$instance = new Template_positions();
                }

                return self::$instance;
        }

        /**
         * Inserts parsetags ( with curly braces ) into given html,
         * if particular tag is not available or corrupt, adds error message
         *
         * @param string $templateHtml html of template to be modified
         *
         * @return string modified html of given template.
         */
        private function _insert_tags($templateHtml) {

                $html = null;

                $dom = $this->ci->domparser->str_get_html($templateHtml);

                // run thru html tags, insert positional parse tags
                foreach ($this->htmlTags as $section) {

                        $tag = $dom->find($section, 0);       // search for 'html' Or 'head' . . .etc

                        if (isset($tag)) {


                                // setting attribute parse tags
                                $tag->setAttribute($this->_get_parse_tag($section, POSITION_ATTRIB), true);

                                // setting start, end parse tags
                                $tag->innertext = ENTRY_SEPARATOR_GENERAL . $this->_get_parse_tag($section, POSITION_START)
                                        . ENTRY_SEPARATOR_GENERAL . $tag->innertext
                                        . ENTRY_SEPARATOR_GENERAL . $this->_get_parse_tag($section, POSITION_END) . ENTRY_SEPARATOR_GENERAL;

                                // setting before, after parse tags
                                $tag->outertext = $this->_get_parse_tag($section, POSITION_BEFORE) . ENTRY_SEPARATOR_GENERAL
                                        . $tag->outertext . ENTRY_SEPARATOR_GENERAL
                                        . $this->_get_parse_tag($section, POSITION_AFTER);
                        } else {

                                $this->_add_error("Html element ( $section ) not found or corrupt");
                        }
                }


                $html = $dom->save();
                $dom->clear();

                return $html;
        }

        public function render($templateHtml, $parseTags = array()) {

                $positionTags = $this->get_all();

                $templateHtml = $this->_insert_tags($templateHtml);

                if (is_array($parseTags)) {
                        $positionTags = array_merge($positionTags, $parseTags);
                }

                return $this->ci->parser->parse_string($templateHtml, $positionTags, true);
        }

        private function _add($positionKey, $strings) {

                $success = false;

                if ($this->_position_exists($positionKey)) {

                        // convert to array
                        $strings = !is_array($strings) ? array($strings) : $strings;

                        foreach ($strings as $string) {

                                $this->parseTags[$positionKey][] = trim($string);
                        }

//                        // add string one by one if array provided
//                        if (is_array($strings)) {
//
//                                foreach ($strings as $string) {
//
//                                        $this->parseTags[$positionKey][] = trim(strtolower($string));
//                                }
//                        }
//                        // add single string
//                        elseif ($strings != '' AND !is_null($strings)) {
//
//                                // add single string
//                                $this->parseTags[$positionKey][] = trim(strtolower($string));
//                        }

                        $success = true;
                } else {
                        $this->_add_error("Attempting to add invalid position", $positionKey);
                        $success = false;
                }
                return $success;
        }

        /* Adds string to parseTags array,
         * if position does not exist in list --> sets error message
         *
         * @params string $section Section in html where string needs to be injected ( Eg. head, body )
         * @params string $position position to inject for a particular section ( Eg. before, after, in, start, end )
         * @params array|string $strings list of strings to inject OR single string
         *
         * @return bool
         */

        public function add($section, $position, $strings) {

                $positionKey = $this->_get_tag($section, $position);

                return $this->_add($positionKey, $strings);
        }

        /**
         * resets all position values to blank ( '' )
         */
        public function clear() {

                if (is_array($this->parseTags)) {

                        foreach ($this->parseTags as $key => $strings) {

                                $this->parseTags[$key] = array();
                        }
                }

                unset($this->meta);
        }

        /**
         *  adds meta to $this->meta array
         *  Common keys ( title, keywords, description )
         */
        public function add_meta($key, $value) {

                $this->meta[$key] = $value;
        }

        // returns null if meta not set
        public function get_meta($key) {

                return isset($this->meta[$key]) ? $this->meta[$key] : null;
        }

        /**
         * Returns text for specific parse tag section
         *
         * @return string|null
         */
        public function get($section, $position) {

                $return = null;

                if ($this->_position_exists($this->_get_tag($section, $position))) {

                        $return = $this->_get_position_string($section, $position);
                }

                return $return;
        }

        /** returns all parsetag and text
         *
         * @return array|null
         */
        public function get_all() {

                $return = null;

                // process each position
//                foreach ( $this->parseTags as $parseTag => $list ) {
//
//                        $return[ $parseTag ] = $this->_get_position_string( $parseTag );
//                }

                foreach ($this->htmlTags as $section) {

                        foreach ($this->positions as $position) {

                                $parseTag = $this->_get_tag($section, $position);
                                $return[$parseTag] = $this->_get_position_string($section, $position);
                        }
                }

                return $return;
        }

        /** returns errors array,
         * if no errors found --> returns null
         *
         * @return array|null
         */
        public function get_errors() {
                return ( is_array($this->errors) AND count($this->errors) ) > 0 ? $this->errors : null;
        }

        /*         * *************** Private methods ************************* */

        /**
         * Initializes parse tags ( without brackets '{' , '}' )
         */
        private function _set_parse_tags() {

                // run through all html tags
                foreach ($this->htmlTags as $section) {

                        // run through all relative positions
                        foreach ($this->positions as $position) {

                                $keyword = $this->_get_tag($section, $position);
                                $this->parseTags[$keyword] = array();
                        }
                }
        }

        /* Checks if given position exists in list,
         * if yes --> returns true
         * if not --> returns false
         *
         * @param string $position string position keyword
         *
         * @return bool
         */

        private function _position_exists($parseTag) {
                return isset($this->parseTags[$parseTag]);
        }

        /**
         * remove duplicate entires in given position
         *
         * @param string $position
         *
         * @return void
         */
        private function _remove_duplicates($parseTag) {

                if ($this->_position_exists($parseTag)) {

                        // remove duplicate entires from parseTags array for particular posiiton
                        $this->parseTags[$parseTag] = array_unique($this->parseTags[$parseTag]);
                } else {
                        $this->_add_error("unable to remove duplicate entries, position does not exist", $parseTag);
                }
        }

        /**
         * processes position list ( remove duplicates, converts to string ),
         * for given position.
         * if items present --> returns concatinated string seperated by 'space' OR '\n'
         * if no items found --> return null string ''
         * if $position invalid --> returns null
         *
         * @param string $section eg. 'html' OR 'head' OR 'body'
         * @param string $position eg. 'before' Or 'after' OR 'attrib'
         *
         * @return string|null
         */
        private function _get_position_string($section, $position) {

                $return = null;

                $parseTag = $this->_get_tag($section, $position);

                if ($this->_position_exists($parseTag)) {

                        // remove duplicate entires from posiitons array for particular posiiton
                        $this->_remove_duplicates($parseTag);
                        $positionList = $this->parseTags[$parseTag];

                        if (is_array($positionList) AND count($positionList) > 0) {

                                // if position is attribute --> seperate with space, else separate with "\n"
                                if ($position == POSITION_ATTRIB) {

                                        // strip html tags, separate using 'space' instead of 'newline'
                                        $return = strip_tags(implode(ENTRY_SEPARATOR_ATTRIB, $positionList));
                                } else {

                                        $return = implode(ENTRY_SEPARATOR_GENERAL, $positionList);
                                }
                        } else {
                                $return = '';
                        }
                } else {
                        $this->_add_error("Request for invalid position", $parseTag);
                        $return = null;
                }

                return $return;
        }

        private function _add_error($error, $parseTag = null) {
                $this->errors[] = is_null($parseTag) ? $error : "$error - ( error at $parseTag )";
        }

        /**
         * get parse tag ( without braces )
         */
        private function _get_tag($section, $position) {
                return $this->tagPrefix . "-" . $section . "-" . $position;
        }

        /**
         * Wrap with curly braces '{' and '}'
         */
        private function _get_parse_tag($section, $position) {
                return "{" . $this->_get_tag($section, $position) . "}";
        }

}

/* End of file Injection.php */
/* Location: ./application/libraries/Template_positions.php */
?>
