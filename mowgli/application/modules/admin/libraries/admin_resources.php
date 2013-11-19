<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Admin_Resources
 * This library will enable users to load certain resources ( js / css ) by using their keywords
 * resources will only be loaded once, repetitive resources will NOT reloaded if the methods are used from this class
 * with this help resources will be concurrent throughout the system
 *
 *
 * @author Lloyd Saldanha
 * @copyright Encube Web Solutions
 */
class Admin_Resources {

        private static $instance = null;
        private $resources = null;
        private $queue = null;

        private function __construct($params = array()) {

                // reset queue
                $this->_reset();

                // load resources one time
                $this->_load_resources();
        }

        private function __clone() {
                // do nothing, prevent Copy of object
        }

        /**
         * Get instance of class, since singleton pattern
         */
        public static function get_instance($params = array()) {

                if (!isset(self::$instance)) {

                        self::$instance = new Admin_Resources($params);
                }

                return self::$instance;
        }

        /**
         * gets the resource string for a particular type of resource.
         * a single recource can be loaded or a list of resources one after the other ( if provided by array )
         * note: all resources will be seperated by "\n" to display neatly on new line
         *
         * @params string $type 'css' OR 'js'
         * @params string|array $keywords single keyword ( eg. 'jquery' ) OR a list of keywords ( e.g. array( 'jquery', 'tinymce' ) )
         * @param bool $strict if strict=false --> will load current resource even if already loaded
         *
         * @return string|null
         */
        public function load_resources($type, $keywords, $strict = true) {

                $output = null;

                // convert to array
                $keywords = !is_array($keywords) ? array($keywords) : $keywords;

                foreach ($keywords as $key) {

                        $uniqueKey = $this->_get_unique_key($type, $key);

                        // check if a particular resource is loaded
                        // if not loaded -> load, add to $this->queue
                        if (!$this->_is_loaded($uniqueKey) OR !$strict) {

                                if (isset($this->resources[$key][$type]) AND $this->resources[$key][$type] != "") {

                                        $strings = $this->resources[$key][$type];

                                        $string = is_array($strings) ? implode("\n", $strings) : $strings;

                                        $this->_queue_resource($uniqueKey, $string);

                                        $output .= $string . "\n";
                                } else {
                                        $output = "<!-- resource NOT loaded ( $uniqueKey )-->\n";
                                }
                        } else {
                                // already loaded, ignore
                                $output = "<!-- ignored resource loading ( $uniqueKey )-->\n";
                        }
                }

                return $output;
        }

        private function _reset() {

                $this->resources = null;
                $this->queue = null;
        }

        /**
         * Loads a resource in a queue
         * note: this queue is only for records purposes, so as to ignore duplicate loading of same resource
         * this queue is checked, if resources are present in queue, then resources is not loaded
         */
        private function _queue_resource($uniqueKey, $string) {

                $this->queue[$uniqueKey] = $string;
        }

        /**
         * Check if resource is already loaded in queue
         */
        private function _is_loaded($uniqueKey) {

                return isset($this->queue[$uniqueKey]);
        }

        /**
         * Generate unique key based on $type and $keyword for Queue
         * Eg. css-tinymce, js-tinymce
         */
        private function _get_unique_key($type, $keyword) {

                return "$type-$keyword";
        }

        /**
         * Add a resource to $this->resources
         *
         * @params string $keyword Eg. jquery, fancybox, etc
         * @params string $type values = css OR js
         * @params string|array $strings single resource string OR array of strings
         *
         * @return void
         */
        private function _add($keyword, $type, $string) {

                // if similar type resource already present
                $this->resources[$keyword][$type][] = $string;
        }

        /**
         * Load resources into $this->resources
         * note: to add a resource to System, add resources here
         *
         * list of keywords available
         * // jquery
          // bootstrap
          // admin
          // fancybox
          // tinymce
          // uploadify
          // swfobject
          // form
         */
        private function _load_resources() {

                // jquery
                $keyword = 'jquery';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>");
                $this->_add($keyword, 'js', "<script type='text/javascript'> window.jQuery || document.write('<script src=\"{admin:resource}/scripts/jquery-1.7.2.min.js\"><\/script>')</script>");
                $this->_add($keyword, 'css', null);

		// jquery
                $keyword = 'jquery-ui';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js'></script>");
                $this->_add($keyword, 'js', "<script type='text/javascript'> window.jQuery || document.write('<script src=\"{admin:resource}/scripts/jquery-ui-1.8.17.custom.min.js\"><\/script>')</script>");
                $this->_add($keyword, 'css', "<link href='{admin:resource}/css/ui-lightness/jquery-ui-1.8.17.custom.css' rel='stylesheet' type='text/css'/>");

                // bootstrap
                $keyword = 'bootstrap';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/bootstrap.min.js'></script>");
                $this->_add($keyword, 'css', "<link href='{admin:resource}/css/bootstrap.min.css' rel='stylesheet'>");
                $this->_add($keyword, 'css', "<link href='{admin:resource}/css/bootstrap-responsive.min.css' rel='stylesheet'>");

                // admin
                $keyword = 'admin';
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/wm.js'></script>");
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/admin.wm.js'></script>");
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/utility.admin.wm.js'></script>");
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/forms.admin.wm.js'></script>");

                $this->_add($keyword, 'css', "<link rel='stylesheet' href='{admin:resource}/css/style.css' type='text/css' media='screen' />");
		$this->_add($keyword, 'css', "<!--[if lte IE 7]>\n<link rel='stylesheet' href='{admin:resource}/css/ie.css' type='text/css' media='screen' />\n<![endif]-->");

		// site
		$keyword = 'site';
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/site.wm.js'></script>");
		$this->_add($keyword, 'css', null);

		// tables
                $keyword = 'tables';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/tables.admin.wm.js'></script>");
                $this->_add($keyword, 'css', null);

		// manage
                $keyword = 'manage';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/manage.jquery.js'></script>");
                $this->_add($keyword, 'css', null);

                // fancybox
                $keyword = 'fancybox';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/fancybox/jquery.fancybox-1.3.4.pack.js'></script>");
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/fancybox/jquery.easing-1.3.pack.js'></script>");
                $this->_add($keyword, 'css', "<link rel='stylesheet' type='text/css' href='{admin:resource}/scripts/fancybox/jquery.fancybox-1.3.4.css' media='screen' />");

                // tinymce
                $keyword = 'tinymce';
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/tiny_mce/tiny_mce.js'></script>");
		$this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/custom/editor.wm.js'></script>");
                $this->_add($keyword, 'css', null);

                // uploadify
                $keyword = 'uploadify';
                $this->_add($keyword, 'js', "<script type='text/javascript' language='javascript' src='{admin:resource}/scripts/uploadify_ci/jquery.uploadify.v2.1.0.min.js'></script>");
                $this->_add($keyword, 'css', "<link rel='stylesheet' type='text/css' href='{admin:resource}/scripts/uploadify_ci/uploadify.css' />");

                // swfobject
                $keyword = 'swfobject';
                // $this->_add( $keyword, 'js', "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js'></script>" );
                $this->_add($keyword, 'js', "<script type='text/javascript' src='{admin:resource}/scripts/swfobject.js'></script>");
                $this->_add($keyword, 'css', null);

                // form
                $keyword = 'form';
                $this->_add($keyword, 'css', "<link rel='stylesheet' href='{admin:resource}/css/simple-form.adminconsole.css' type='text/css' media='screen' />");
        }

}
?>

