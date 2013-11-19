<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of blog_render_lib
 *
 * @author Lloyd
 */
!defined('BLOG_TAG_SEPARATOR') ? define('BLOG_TAG_SEPARATOR', ':') : null;

class blog_render_lib {

	private $ci;
	private $configFile = 'blog_config';
	private $module = 'blog';
//        private $allowedConditions = null;      // holds allowed key values for url AND tag params | array('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part' );
//----------- Url Params ( main section params ) -------------------

	private $PostId = null;	 // holds value of post_id obtained from url/block tag ( block tag overrides url value if not null )
	private $PostSlug = null;       // holds value of post_slug obtained from url/block tag ( block tag overrides url value if not null )
	private $urlViewKeyword = null;    // holds keyword derived from url ( from blog_url_lib )
	private $urlConditions = null;  // holds array of conditions ( from url ) Eg. post=post_slug, year=2012, month=03, category=categ_slug . . etc
	private $allowedUrlConditions = null; // holds list of allowed urlCondition keys | array('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part' );
//----------- Block Params ------------------
	private $tagString = null;      // holds tagstring for each block ( this is obtained from database tags table ( Eg. blog:view=summary:template=temp_name )
	private $tagParams = null;      // will hold key value pair of all tags ( E.g. template='temp_name', post='post_slug' )
	private $blockViewKeyword = null;    // holds viewKeyword from block Eg. 'main' OR 'summary' OR 'single_post' OR 'featured_posts'
	private $finalViewKeyword = null;       // calculated as merge of urlViewKeyword AND blockViewKeyword ( preference to block, unless blockKeyword = main )
//        private $blockConditions = null;  // holds array of conditions ( from block ) Eg. post=post_slug, year=2012, month=03, category=categ_slug . . etc
	private $template = null;       // holds template name ( note by default template = keyword, if template specified then mentioned template is used )
//----------- Output params ------------------
	private $head = null;
	private $html = null;
	private $error = null;

	public function __construct() {
		$this->ci = & get_instance();

		// load config files
		$this->ci->config->load($this->configFile, true, true, $this->module);

		// load libraries
		$this->ci->load->library('blog/blog_template_lib');
		$this->ci->load->library('blog/blog_tag_lib');

		$this->allowedUrlConditions = $this->ci->config->item('allowed_conditions', $this->configFile);

		!defined('BLOG_VIEW_MAIN') ? define('BLOG_VIEW_MAIN', $this->ci->config->item('view_main', $this->configFile)) : null;

		!defined('BLOG_TAG_KEY_VIEW') ? define('BLOG_TAG_KEY_VIEW', $this->ci->config->item('tag_key_view', $this->configFile)) : null;
		!defined('BLOG_TAG_KEY_POST') ? define('BLOG_TAG_KEY_POST', $this->ci->config->item('tag_key_post', $this->configFile)) : null;
		!defined('BLOG_TAG_KEY_POST_ID') ? define('BLOG_TAG_KEY_POST_ID', $this->ci->config->item('tag_key_post_id', $this->configFile)) : null;
	}

	public function reset() {

		$this->viewKeyword = null;
		$this->postId = null;
		$this->postSlug = null;
		$this->urlConditions = null;
		$this->tagString = null;
		$this->tagParams = null;
		$this->template = null;
		$this->head = null;
		$this->html = null;
		$this->error = null;
	}

	public function get_PostId() {
		return $this->PostId;
	}

	public function set_PostId($PostId) {

// set post id ONLY if provided value is not null or empty string
		if (!is_null($PostId) AND $PostId != '') {
			$this->PostId = $PostId;
		}
	}

	public function get_PostSlug() {
		return $this->PostSlug;
	}

	public function set_PostSlug($PostSlug) {
// set post slug ONLY if provided value is not null or empty string
		if (!is_null($PostSlug) AND $PostSlug != '') {
			$this->PostSlug = $PostSlug;
		}
	}

	public function get_urlViewKeyword() {
		return $this->urlViewKeyword;
	}

	public function set_urlViewKeyword($urlViewKeyword) {
		$this->urlViewKeyword = $urlViewKeyword;
	}

	public function get_blockViewKeyword() {
//                return $this->blockViewKeyword;
		$keyword = null;

		// provided keyword = 'main' OR null OR ''
		// --> then blockViewKeyword = urlViewKeyword
		if ($this->blockViewKeyword == BLOG_VIEW_MAIN OR is_null($this->blockViewKeyword) OR $this->blockViewKeyword == '') {
			$keyword = $this->get_urlViewKeyword();
		} else {
			$keyword = $this->blockViewKeyword;
		}

		return $keyword;
	}

	/**
	 * Set blockViewKeyword ( depends on below conditions ) . .
	 * if blockKeyword = 'main' OR null OR '' --> then --> set blockKeyword = urlKeyword
	 * else --> set blockKeyword = <provided_keyword>
	 */
	public function set_blockViewKeyword($blockViewKeyword) {

//                // provided keyword = 'main' OR null OR ''
//                // --> then blockViewKeyword = urlViewKeyword
//                if ($blockViewKeyword == BLOG_VIEW_MAIN OR is_null($blockViewKeyword) OR $blockViewKeyword == '') {
//                        $this->blockViewKeyword = $this->get_urlViewKeyword();
//                } else {
//                        $this->blockViewKeyword = $blockViewKeyword;
//                }

		$this->blockViewKeyword = $blockViewKeyword;
	}

	public function get_urlConditions() {
		return $this->urlConditions;
	}

	public function set_urlConditions($urlConditions) {
		$this->urlConditions = $urlConditions;
	}

	/**
	 * add a key value to Url Conditions
	 * Checks if key is valid, only then adds
	 */
	private function _add_url_condition($key, $value) {

		if (in_array($key, $this->allowedUrlConditions)) {

			$this->urlConditions[$key] = $value;
		}
	}

	public function get_tagString() {
		return $this->tagString;
	}

	public function set_tagString($tagString) {
		$this->tagString = $tagString;
	}

	public function get_template() {
		return $this->template;
	}

	public function set_template($template) {
		$this->template = $template;
	}

	public function get_head() {
		return $this->head;
	}

	public function set_head($head) {
		$this->head = $head;
	}

	public function get_html() {
		return $this->html;
	}

	public function set_html($html) {
		$this->html = $html;
	}

	public function get_error() {
		return $this->error;
	}

	public function set_error($error) {
		$this->error = $error;
	}

	/**
	 * Splits render parse tags for block by separator ':'
	 * creates an array of key value pairs from tagstring
	 *
	 * @param string $tagString ( Eg. Tag Eg. {blog:template=temp_name:type=group:page=1} )
	 *
	 * @retuns array|null
	 */
	public function decode_block_tag($tagString) {

		// remove '{' and '}' from start and end of string respectively
		$tagString = str_replace(array('{', '}'), '', $tagString);

		// seperate by ':'
		$tagParts = explode(BLOG_TAG_SEPARATOR, $tagString);

		$tags = null;

		foreach ($tagParts as $tag) {

			// check if equal( = ) sign inbetween any text
			if (preg_match("/^(.*)=(.*)$/", $tag, $matches, PREG_OFFSET_CAPTURE)) {

				$key = isset($matches[1][0]) ? strtolower($matches[1][0]) : null;
				$value = isset($matches[2][0]) ? strtolower($matches[2][0]) : null;

				$tags[trim($key)] = trim($value);
			}
		}

		return $tags;
	}

	/**
	 * returns specific value of block tag
	 * Eg. if $key=template --> returns template_name OR null
	 *
	 * @param string $key
	 * @return string|null
	 */
	private function get_tag_value($key) {

		$value = null;

		if (isset($this->tagParams[$key])) {

			$value = $this->tagParams[$key];
		}

		return $value;
	}

	public function check_main_block_exists($tagStrings) {

		$success = false;

		if (is_array($tagStrings)) {

			foreach ($tagStrings as $tagString) {

				$tags = $this->decode_block_tag($tagString);

				// check if current tag array contains view='main',
				// if yes --> set success, abort
				if (isset($tags[BLOG_TAG_KEY_VIEW]) AND $tags[BLOG_TAG_KEY_VIEW] == BLOG_VIEW_MAIN) {

					$success = true;
					break;
				}
			}
		}

		return $success;
	}

	public function render($tagString, $parseData = array()) {

		$success = false;

		$this->set_tagString($tagString);

		// decode tag string, store key-value pair in $this->tagParams
		$this->tagParams = $this->decode_block_tag($this->tagString);

//                // override post_id and postSlug values ( note if values are null OR '' --> values will not be overridden )
//                $this->set_PostId($this->get_tag_value(BLOG_TAG_KEY_POST_ID));
//                $this->set_PostSlug($this->get_tag_value(BLOG_TAG_KEY_POST));
		// override post_id and postSlug values ( note if values are null OR '' --> values will not be overridden )
		$this->set_PostId($this->get_tag_value(BLOG_TAG_KEY_POST_ID));
		$this->set_PostSlug($this->get_tag_value(BLOG_TAG_KEY_POST));
		$this->set_blockViewKeyword($this->get_tag_value(BLOG_TAG_KEY_VIEW));

		// replace urlConditions with values from block tags ( $this->tagParams )
		$this->_override_url_conditions();

		// set appropriate template depending on block tag data and url data
		$this->_identify_template();

		$this->ci->blog_template_lib->load_template($this->get_template(), $parseData);

		// get,set -> head,html values for template
		$this->set_head($this->ci->blog_template_lib->get_head());
		$this->set_html($this->ci->blog_template_lib->get_html());


		// set tag parameters for processing
		$this->ci->blog_tag_lib->set_viewKeyword($this->get_blockViewKeyword());
		$this->ci->blog_tag_lib->set_postId($this->get_PostId());
		$this->ci->blog_tag_lib->set_postSlug($this->get_PostSlug());
		$this->ci->blog_tag_lib->set_conditions($this->get_urlConditions());

		$parseTags = array();

		if ($this->ci->blog_tag_lib->load_tags()) {

			$parseTags = $this->ci->blog_tag_lib->get_tags();
			$renderedHtml = $this->ci->parser->parse_string($this->get_html(), $parseTags, true);
			$this->set_html($renderedHtml);
			$success = true;
		} else {
			$success = false;
			$this->set_error($this->ci->blog_tag_lib->get_error());
		}


		return $success;
	}

	/**
	 * Identifies correct template for view based on below conditions,
	 * if template provided in block tags string then --> template=<provided_template>
	 * if template NOT provided then . .
	 *      * template = urlViewKeyword ( if blockViewKeyword = main )
	 *      * template = blockViewKeyword ( if blockViewKeyword NOT = main )
	 */
	private function _identify_template() {

		$blockTemplate = $this->get_tag_value('template');

		// check if template defined in block tag
		// if NOT defined, set template as viewKeyword ( either from URL or block )
		// else --> use template in block tag ( i.e. override template with block template )
		if (is_null($blockTemplate) OR $blockTemplate == '') {

			// check if block view = 'main'
			// if YES -- > template = urlViewKeyword
			// if NO --> template = blockViewKeyword
			if ($this->get_blockViewKeyword() == BLOG_VIEW_MAIN) {

				// template = url viewKeyword
				$this->set_template($this->get_urlViewKeyword());
			} else {
				// template = blockViewKeyword
				$this->set_template($this->get_blockViewKeyword());
			}
		} else {
			// if template provided in block tagstring --> set it
			$this->set_template($blockTemplate);
		}
	}

	/**
	 * replaces values for urlConditions, by values from block Tags ( $this->tagParts )
	 * note: values are only replaced if NOT NULL and NOt ''
	 */
	private function _override_url_conditions() {

		// run through all allowed urlCondition keys
		foreach ($this->allowedUrlConditions as $key) {

			// add/replace url Condition with value only if set and NOT ''
			if (isset($this->tagParams[$key]) AND !is_null($this->tagParams[$key]) AND $this->tagParams[$key] != '') {

				$this->_add_url_condition($key, $this->tagParams[$key]);
			}
		}
	}

}

?>
