<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of gallery_render
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class gallery_render {

	private $ci;
	private $module = 'gallery';
	private $configFile = 'gallery_config';
	private $config_database = 'gallery_config_database';
	private $theme;
	private $use_theme;
	private $type;
	private $template;
	private $ids = array();
	/* DELETE
	private $tags = array(
	    'theme:resource' => '', // set later in 'render()'

	    'categories' => array(
		array(
		    'categ:id' => null,
		    'categ:parent_id' => null,
		    'categ:cover_id' => null,
		    'categ:type' => null,
		    'categ:name' => null,
		    'categ:name_url' => null,
		    'categ:count' => null,
		    'categ:description' => null,
		    'categ:alt' => null,
		    'categ:uri' => null,
		    'categ:uri_thumb' => null,
		    'categ:order' => null,
		    'categ:created' => null,
		    'categ:modified' => null,
		    'categ:visible' => null,
		    'categ:images' => array(
			array(
			    'image:id' => null,
			    'image:parent_id' => null,
			    'image:cover_id' => null,
			    'image:type' => null,
			    'image:name' => null,
			    'image:name_url' => null,
			    'image:description' => null,
			    'image:alt' => null,
			    'image:uri' => null,
			    'image:uri_thumb' => null,
			    'image:order' => null,
			    'image:created' => null,
			    'image:modified' => null,
			    'image:visible' => null
			)
		    )
		)
	    ),
	    'images' => array(
		array(
		    'image:id' => null,
		    'image:parent_id' => null,
		    'image:cover_id' => null,
		    'image:type' => null,
		    'image:name' => null,
		    'image:name_url' => null,
		    'image:description' => null,
		    'image:alt' => null,
		    'image:uri' => null,
		    'image:uri_thumb' => null,
		    'image:order' => null,
		    'image:created' => null,
		    'image:modified' => null,
		    'image:visible' => null
		)
	    )
	);
	*/
	private $html = array(
	    'head' => null,
	    'body' => null
	);
	private $error;

	public function __construct($params = null) {

		$this->ci = & get_instance();

		/* Set initial params if provided */
		if (!is_null($params))
			$this->initialize($params);

		// Load libraries
		$this->ci->load->library('templates');
		$this->ci->load->library('gallery/gallery_tag_lib');

		// Load models
		$this->ci->load->model('gallery/gallery_model');

		// Load settings
		$this->ci->config->load($this->configFile, true, true, $this->module);

		// set constants
		!defined('GALLERY_TAG_SEPARATOR') ? define('GALLERY_TAG_SEPARATOR', ':') : null;
	}

	public function initialize($params) {

//                $this->module   = isset ( $params['module'] ) ? $params['module'] : null;;
		$this->theme = isset($params['theme']) ? $params['theme'] : null;
		$this->type = isset($params['type']) ? $params['type'] : null;
		$this->template = isset($params['template']) ? $params['template'] : null;
		$this->ids = isset($params['ids']) ? $params['ids'] : null;
//		$this->tags = isset($params['tags']) ? $params['tags'] : null;
		$this->error = null;
	}

//	public function get_tags() {
//		return $this->tags;
//	}

	public function get_type() {
		return $this->type;
	}

	public function get_template() {
		return $this->template;
	}

	public function get_html_head() {
		return $this->html['head'];
	}

	public function get_html_body() {
		return $this->html['body'];
	}

	public function get_error() {
		return $this->error;
	}

	public function set_error($error) {
		$this->error = $error;
	}

	public function encode_block_tag($type, $template = null, $ids = null, $use_theme = null, $theme = null) {

		$parseTag = null;

		// convert array of IDs to comma separated string
		$ids = is_array($ids) ? implode(',', $ids) : $ids;

		$sections = array(
		    'type' => $type,
		    'template' => $template,
		    'use_theme' => $theme,
		    'theme' => $theme,
		    'ids' => $ids
		);

		// remove sections if value = null or empty string
		foreach ($sections as $key => $value) {

			if (is_null($value) OR $value == '') {

				unset($sections[$key]);
			}
		}

		// tag = gallery:
		$parseTag = $this->module . GALLERY_TAG_SEPARATOR . implode(GALLERY_TAG_SEPARATOR, $sections);

		return $parseTag;
	}

	/**
	 * Splits render parse tags for block by separator ':'
	 * creates an array of key value pairs from tagstring
	 *
	 * Sets global type, template, ids
	 *
	 * @param string $tagString ( Eg. Tag Eg. {gallery:type=value:template=value:ids=id1,id2} )
	 *
	 * @retuns array|null
	 */
	public function decode_block_tag($tagString) {

		// remove '{' and '}' from start and end of string respectively
		$tagString = str_replace(array('{', '}'), '', $tagString);

		// seperate by ':'
		$tagParts = explode(GALLERY_TAG_SEPARATOR, $tagString);

		$tags = null;

		// run through each tag part ( separated by ':' ) and assign to $tags as key-value pair
		foreach ($tagParts as $part) {

			// check if equal( = ) sign in between any text
			if (preg_match("/^(.*)=(.*)$/", $part, $matches, PREG_OFFSET_CAPTURE)) {

				$key = isset($matches[1][0]) ? strtolower($matches[1][0]) : null;
				$value = isset($matches[2][0]) ? strtolower($matches[2][0]) : null;

				$tags[trim($key)] = trim($value);
			}
		}

		/* convert ids ( comma separated ) into array */
		if (isset($tags['ids']) AND ( $tags['ids'] != '' )) {

			$tags['ids'] = explode(',', $tags['ids']);
		}

		// class values
		$this->type = isset($tags['type']) ? $tags['type'] : null;
		$this->template = isset($tags['template']) ? $tags['template'] : null;
		$this->theme = isset($tags['theme']) ? $tags['theme'] : null;
		$this->use_theme = isset($tags['use_theme']) ? string_to_bool($tags['use_theme']) : true; // by default if use_theme NOT provided, consider use_theme = true
		$this->ids = isset($tags['ids']) ? $tags['ids'] : null;

		return $tags;
	}

	/**
	 * Performs below list of actions
	 * - Sets current theme, if not present sets to default
	 * - set html[head], html[body]
	 * - gets appropriate gallery/image data from 'type'
	 * - sets tags for template
	 * - parses tags through html[head] and html[body]
	 * - sets final html[head] and html[body]
	 *
	 * @param string $parseString parse tag to analyze ( note: full parse tag needed )
	 * @param array $tags <optional> tags to be included in parsing
	 *
	 * @return bool
	 */
	public function render($parseString, $tags = null) {

		$success = false;

		/* Decode block tag and sets template, theme, type, ids, use_theme with values from block tag
		 * sets defaults where necessary */
		$this->decode_block_tag($parseString);

		/* Get template data from db, parse database tags, set html[head], html[body] */
		if ($this->set_template_data()) {

			$success = false;

			/* check type, get data, set tags, returns true/false
			 * true --> data found, all Ok
			 * false --> if no data found */
			if ($this->ci->gallery_tag_lib->load_tags($this->type, $this->ids)) {

				/* Parse tags through head and body html */
				$galleryTags = $this->ci->gallery_tag_lib->get_tags();

				// merge tags provided with gallery tags
				$galleryTags = ( is_array( $tags ) AND is_array($galleryTags) ) ? array_merge( $tags, $galleryTags ) : $galleryTags;

				$this->html['head'] = $this->ci->parser->parse_string($this->html['head'], $galleryTags, true); // true => return value
				$this->html['body'] = $this->ci->parser->parse_string($this->html['body'], $galleryTags, true); // true => return value
//
				$success = true;
			} else {
				// no data found
				$success = false;
				$this->set_error("Gallery data not found");
//				$this->set_error($this->gallery_tag_lib->get_error());
			}
		} else {
			// no templates found, error already SET
			$success = false;
//			$this->set_error("Gallery template not found");
		}

		return $success;
	}

	/**
	 * Loads html head, body for gallery display
	 * either loads from current selected theme OR
	 * 	loads from original template provided during extract
	 *
	 * note: important to call $this->decode_block_tag() before calling set_template_data
	 *
	 * @return bool Returns true if templates loaded, false if no templates were loaded
	 */
	public function set_template_data() {

		$success = false;

		$setting_use_theme = get_settings($this->module, 'use_theme');

		// if block use_them = true AND theme set in settings, then use theme and set head, body from theme
		// else use html from original extract template
		if ($this->use_theme === true AND string_to_bool($setting_use_theme) === true) {

			// use theme ( override template from extract )
			// load current theme from DB if exists, set $this->html['head'] and $this->html['body']
			if ($this->_load_current_theme()) {

				// theme loaded
				// head and body set in $this->_load_current_theme()
				$success = true;
//
			} else {

				// them NOT found in db
				// unlikely, unless theme deleted from database
				$success = false;

				/*
				 * IMPROVEMENT : To Do
				 * - load default gallery scripts, used for admin panel
				 */
				// use template from html extract
				$success = $this->_load_extract_template($this->template);
			}
		} else {

			// use template from html extract
			$success = $this->_load_extract_template($this->template);
		}

		return $success;
	}

	/**
	 * Loads original gallery template that was extracted from html while importing
	 * If template found --> Sets $this->html['head'] AND $this->html['body']
	 * else returns false
	 *
	 * @param string $template name of template from block tag
	 *
	 * @return bool true if template found in db, false if NOT found
	 */
	private function _load_extract_template($template) {

		$success = false;

		// template provided, use given template
		if ($this->ci->templates->load($this->module, $template)) {

			$this->html['head'] = $this->ci->templates->get_head();
			$this->html['body'] = $this->ci->templates->get_html();
			$success = true;
		} else {
			$success = false;
			$this->set_error("Gallery template not found");
		}

		return $success;
	}

	/**
	 * Sets $this->theme to the current theme and attempts to load theme templates in head and body
	 * if NOT found returns false
	 *
	 *
	 * @return bool true if theme found, false if theme NOT found in DB
	 */
	private function _load_current_theme() {

		$success = false;

		/* Get settings key for 'current_theme' */
		$this->theme = get_settings($this->module, 'current_theme');

		$themeTempData = $this->ci->gallery_model->get_theme_template($this->theme, $this->type, null); // $theme = null assuming theme is only used for general templates, no custom templates in theme
		// Check if settings theme available in DB
		if (!is_null($themeTempData)) {

//			$this->tags["theme:resource"] = site_url($themeTempData['theme_resource_uri']);
			$tags["theme:resource"] = site_url($themeTempData['theme_resource_uri']);

			/* set scripts in <head> tag for current theme */
			$this->html['head'] = $this->ci->parser->parse( $themeTempData['theme_scripts'], $tags, true );

			/* save template specific scripts above template html */
			$this->html['body'] = $themeTempData['temp_scripts'] . "\n" . $themeTempData['temp_html'];
			$this->html['body'] = $this->ci->parser->parse( $this->html['body'], $tags, true );

			$success = true;
		}
		// if theme NOT found, set theme to default theme : @PENDING
		else {
			// set theme NOT found in DB
//			$this->theme = $this->ci->config->item('settings_default_theme', $this->config_database);
			$success = false;
			$this->set_error("Gallery theme not found");
		}

		return $success;
	}

}

?>
