<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of panel
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Admin extends Admin_Controller {

	public function __construct() {

		parent::__construct();

		// load libraries
		$this->load->library('admin/admin_views');

		/* Site Values -- temporary -- later use correct libraries / helpers */
		$this->parseData['admin:user'] = $this->load->library('user/auth')->get_username();

		$this->_delete_caches();
	}

	public function _remap($method, $params = array()) {

		/**
		 * @todo if $method starts with underscore '_' then show 404 error
		 */
		/* check if overriding method defined in THIS or PARENT class */
		if (!method_exists($this, $method)) {

			/* function NOT present in admin --> Check for POST values */
			$module = $method;
			$method = isset($params[0]) ? $params[0] : 'index';
			array_shift($params);

			array_unshift($params, "$module/admin/$module/$method");

			$output = call_user_func_array("Modules::run", $params);

			if (!is_null($output)) {

				// add tag {active-module} to allow sidebar to select current module
				$this->parseData['active-module'] = strtolower($module);

				$noWrap = ( $this->input->get('no_wrap') == 1 ) ? true : false;

				// check if Request is AJAX OR no_wrap=1
				if ($this->input->is_ajax_request() OR $noWrap === true) {

					echo $output;

				}
				// wrap content with admin panel, display normally
				else {
					// display entire admin panel
					// direct request from url, display entire admin panel
					echo $this->_wrap_main_content($output);
				}

			} else {

				show_404();
			}
		} else {

			/* method defined in this class go directly to method */
			//$this->$method();
			call_user_func_array(array($this, $method), $params);
		}
	}

	public function index() {
		$this->dashboard();
	}

	public function dashboard() {

		// add tag {active-module} to allow sidebar to select current module
		$this->parseData['active-module'] = 'dashboard';

		$innerContent = $this->parser->parse('admin/dashboard', $this->parseData, true);


		$mainContent = $this->admin_views->get_main_content($this->parseData, "Dashboard", $innerContent);


		echo $this->_wrap_main_content($mainContent);
	}

	private function _wrap_main_content($mainContent) {

		// generate sidebar links

		$head = $this->parser->parse('admin/includes/head', $this->parseData, true);
		$scripts = $this->parser->parse('admin/includes/scripts', $this->parseData, true);

		$header = $this->admin_views->get_header($this->parseData);
		$sidebar = $this->admin_views->get_sidebar($this->parseData);
		$footer = $this->parser->parse('admin/includes/footer', $this->parseData, true);

		$this->parseData['head'] = & $head;
		$this->parseData['scripts'] = & $scripts;

		$this->parseData['header'] = & $header;
		$this->parseData['sidebar'] = & $sidebar;
		$this->parseData['footer'] = & $footer;
		$this->parseData['main'] = & $mainContent;

		return $this->parser->parse('admin/main_template', $this->parseData, true);
	}

	private function _delete_caches() {

		// delete all page caches
		$this->cache->delete_group(WM_CACHE_PAGE_PREFIX);
	}

	public function wm_reset() {

		$postUrl = site_url('admin/wm_reset_do');

		$output = "

		<form action='$postUrl' method='POST'>

		<input type='password' name='password' value=''/>
		<br/>
		<input type='submit' name='submit' value='Submit' />

		</form>
";
		echo $output;
	}

	/**
	 * Temporary reset controller, deletes everything and gets website to original state
	 */
	public function wm_reset_do() {

		/*
		 * Delete files:
		 * 	- uploads/site
		 * 	- uploads/dump
		 *
		 * Delete tables
		 * 	- templates
		 * 	- resources
		 * 	- tags
		 */

		/**
		 * IMPROVEMENTS : To Do
		 *
		 * Scan modules and get folders and tables to delete specific to individual modules
		 *
		 */
		$this->load->helper('file');
		$this->load->model('admin/admin_model');

		if ($this->input->post('password') == "wm_reset") {

			$folders = array(
			    dump_path(),
			    site_resource_path()
			);

			$tables = array('templates', 'resources', 'tags', 'contents');

			$output = null;

			// clear out files
			foreach ($folders as $folder) {

				if (file_exists($folder)) {

					delete_files($folder, TRUE); // true --> delete inner files and directories
				}
			}
			$output .= "Files deleted<br/>";

			foreach ($tables as $table) {

				$this->admin_model->truncate($table);
			}
			$output .= "Tables deleted<br/>";
		} else {
			$output = "Invalid password";
		}

		$backUrl = site_url('admin/wm_reset');

		echo $output . "<br/><br/><a href='$backUrl'>back</a>";
	}

}

?>