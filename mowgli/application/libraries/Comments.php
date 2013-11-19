<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of Comments
 *
 * @author Lloyd Saldanha
 */

!defined("COMMENTS_COUNT_STRING") ? define('COMMENTS_COUNT_STRING', "#disqus_thread") : null;

class Comments {

	private static $instance = null;
	private $ci = null;
	private $username = null;
	private $is_comments = null;
	private $is_count = null;

	private function __construct() {

		$this->ci = & get_instance();

		$this->_initialize();
	}

	private function __clone() {
		// do nothing, prevent Copy of object
	}

	/** Get instance for Singleton pattern */
	public static function get_instance() {

		if (!isset(self::$instance)) {

			self::$instance = new Comments();
		}

		return self::$instance;
	}

	private function _initialize() {

		$this->set_username(get_settings(WM_SET_CATEG_GENERAL, 'comments_username'));
		$this->set_is_comments(get_settings(WM_SET_CATEG_GENERAL, 'enable_comments'));
		$this->set_is_count(false);
	}

	private function _reset() {

		$this->set_username(null);
		$this->set_is_comments(false);
		$this->set_is_count(false);
	}

	private function get_username() {
		return $this->username;
	}

	private function set_username($username) {
		$this->username = $username;
	}

	private function get_is_comments() {
		return $this->is_comments;
	}

	private function set_is_comments($is_comments) {
		$this->is_comments = $is_comments;
	}

	private function _allow_comments($is_comments = true) {

		return ( $this->get_is_comments() AND $is_comments );
	}

	private function get_is_count() {
		return $this->is_count;
	}

	private function set_is_count($is_count) {
		$this->is_count = $is_count;
	}

	/**
	 * Instruct Class to allow comments script to be loaded during page render
	 * Sets $this->is_comments = value provided
	 * note: value set based on settings from site comments settings
	 *
	 * @param bool $is_comments Instruct class ot allow comments to be loaded or NOT
	 */
	public function load_comments($is_comments = true) {

		// $this->is_comments is ANDed with given variable
		$this->set_is_comments($this->_allow_comments($is_comments));
	}

	/**
	 * Instruct Class to allow comments Count script to be loaded during page render
	 * Sets $this->is_count = true
	 *
	 * @param bool $is_comments Instruct class ot allow comments to be loaded or NOT
	 */
	public function load_count() {

		$this->set_is_count(true);
	}



	/**
	 * Generates the href required by DISQUS to generate count of comments
	 *
	 * @param string $url url of page to get comment count for
	 *
	 * @return string href to be used for anchor tag to display comment count
	 */
	public function get_count_url($url) {

//		$data = array(
//		    'username' => $this->get_username()
//		);
//		$script = $this->ci->load->view('comments/comments_view.php', $data, true);


//		$pageTemplate = Template_positions::get_instance();
////		$pageTemplate = new Template_positions();
//		$pageTemplate->add('body', 'before-end', $script);


		return trim($url, '/') . COMMENTS_COUNT_STRING;
	}

	/**
	 * return script for comments, based on $this->is_comments
	 *
	 * @return string|null returns null if is_comments = false
	 */
	public function get_comment_script() {

		$return = null;

		if ($this->_allow_comments()) {

			$data = array(
			    'username' => $this->get_username()
			);

			$return = $this->ci->load->view('comments/comments_view.php', $data, true);
		}

		return $return;
	}

	/**
	 * Loads the script for DISQUS comment count ONLY if $this->is_count() was set to true
	 * note : to display count first call $comments->load_count();
	 *
	 * @return string script to run before </body> for counts to display
	 */
	public function get_count_script() {

		$return = null;

		if ($this->get_is_count()) {

			$data = array(
			    'username' => $this->get_username()
			);

			$return = $this->ci->load->view('comments/comments_script_count.php', $data, true);
		}

		return $return;
	}

}

?>
