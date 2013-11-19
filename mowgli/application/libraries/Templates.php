<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of Templates
 *
 * @author Lloyd
 */
!defined('TEMPLATE_TYPES') ? define('TEMPLATE_TYPES', 'page|includes|module') : null;

class Templates {

	private $ci;
	private $db;
	private $tempTypes = array();
	private $tempId = null;
	private $head = null;
	private $html = null;
	private $tempModule = null;
	private $tempName = null;

	public function __construct() {

		$this->ci = & get_instance();

		$this->ci->config->load('template_config', true, true, 'template');

		$this->ci->load->library('date_time');

		// load template model ( templates module )
		$this->ci->load->model('template/template_model');

		// set template allowed types
		$this->tempTypes = $this->ci->config->item('template_types', 'template_config');
	}

	public function reset() {

		$this->set_tempId(null);
		$this->set_head(null);
		$this->set_html(null);
		$this->set_tempModule(null);
		$this->set_tempName(null);
	}

	/**
	 * @deprecated use load() instead
	 *
	 * Loads template by module, name, type
	 * loads id, head, html as properties of class
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $type
	 * @param bool $allowHidden
	 *
	 * @return null|$this
	 */
	public function get($module = null, $tempName = null, $type = null, $allowHidden = false) {

		return $this->load($module, $tempName, $type, $allowHidden);
	}

	/**
	 * Loads template by module, name, type
	 * loads id, head, html as properties of class
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $type
	 * @param bool $allowHidden
	 *
	 * @return bool Returns true if template found, false if template does not exist
	 */
	public function load($module = null, $tempName = null, $type = null, $allowHidden = false) {

		return $this->_load_template(null, $module, $tempName, $type, $allowHidden);
	}

	/**
	 * Loads template by id
	 * loads id, head, html as properties of class
	 *
	 * @param int $tempId if id provided, will use id instead of module, type, name
	 * @param bool $allowHidden
	 *
	 * @return $this|null
	 */
	public function get_by_id($tempId, $allowHidden = false) {

		$this->_load_template($tempId, null, null, null, $allowHidden);

		return $this;
	}

	/**
	 * Shortcut to add templates to templates table ( will mostly be used for type=module )
	 *
	 * @pending checks to make the funciton call foolproof ( no clash with other template names etc )
	 *
	 * @param array $data array of values ( column names to be provided )
	 *
	 * @return int template id of newly created template
	 */
	public function add($data) {

		if (!is_null($this->db)) {
			$this->ci->template_model->set_db($this->db);
		}


		$tempId = $this->ci->template_model->create_template($data);

		return $tempId;
	}

	/**
	 * Adds a (module) tempalte to the templates table, returns Template Id
	 * If tempalte with similar features exist then that templates Id is returned
	 *
	 * @param string $module module name
	 * @param string $tempName name of template
	 * @param string $html main body of template
	 * @param string $head dependancies for the current tempalte that should be loaded in the <head> tags of the rendering page
	 * @param string $description optional description or comments
	 * @param bool $isVisible if tempalte should be enabled OR disabled
	 *
	 * @return int|null Returns id of newly created template OR id of existing template
	 */
	public function add_module_template($module, $tempName, $html, $head = null, $description = null, $isVisible = true) {

		$tempId = null;

		$this->get($module, $tempName, WM_TEMPLATE_TYPE_MODULE, true);

		// if Template does not exist --> add new template, Else return existing template id
		if (is_null($this->tempId)) {

			$data = array(
//                    'temp_id' => null,
			    'temp_module_name' => $module,
			    'temp_name' => $tempName,
			    'temp_type' => WM_TEMPLATE_TYPE_MODULE,
			    'temp_head' => $head,
			    'temp_html' => $html,
			    'temp_created' => $this->ci->date_time->now(),
//                    'temp_modified' => null,
			    'temp_description' => $description,
			    'temp_is_visible' => $isVisible,
//                    'temp_site_id' => null
			);

			$tempId = $this->add($data);
		} else {

			$tempId = $this->get_id();
		}

		return $tempId;
	}

	public function get_id() {
		return $this->tempId;
	}

	public function get_head() {
		return $this->head;
	}

	public function get_html() {
		return $this->html;
	}

	public function get_module() {
		return $this->tempModule;
	}

	public function get_name() {
		return $this->tempName;
	}

	/**
	 * Gets template from database
	 * loads id, head, html as properties of class if template found.
	 *
	 * @param int $tempId if id provided, will use id instead of module, type, name
	 * @param string $module
	 * @param string $name
	 * @param string $type
	 * @param bool $allowHidden
	 *
	 * @return bool Returns true if template found, false if template does not exist
	 */
	private function _load_template($tempId = null, $module = null, $name = null, $type = null, $allowHidden = false) {

		$tempDetails = null;
		$success = null;

		if (!is_null($this->db)) {
			$this->ci->template_model->set_db($this->db);
		}

		// if id provided, simply get template by id, ignore module, name, type
		if (is_null($tempId)) {

			// id NOT provided, get template by module, type, name
			// if module provided BUT type NOT provided, --> assume type = module
			$type = (!is_null($module) AND is_null($type)) ? WM_TEMPLATE_TYPE_MODULE : $type;

			$tempDetails = $this->ci->template_model->get_template_by_values($module, $name, $type, $allowHidden);
		} else {
			// id provided, ignore module, name, type

			$tempDetails = $this->ci->template_model->get_template_details($tempId, $allowHidden);
		}


		// check if template exists
		if (!is_null($tempDetails)) {

			// template exists, load template data in class variables

			$id = isset($tempDetails['id']) ? $tempDetails['id'] : null;
			$module = isset($tempDetails['module_name']) ? $tempDetails['module_name'] : null;
			$name = isset($tempDetails['name']) ? $tempDetails['name'] : null;
			$head = isset($tempDetails['head']) ? $tempDetails['head'] : null;
			$html = isset($tempDetails['html']) ? $tempDetails['html'] : null;

			$this->set_tempId($id);
			$this->set_tempModule($module);
			$this->set_tempName($name);
			$this->set_head($head);
			$this->set_html($html);

			$success = true;
		} else {

			// template Does NOT exist, reset variables

			$this->reset();

			$success = false;
		}

		return $success;
	}

	private function set_tempId($tempId) {
		$this->tempId = $tempId;
	}

	private function set_head($head) {
		$this->head = $head;
	}

	private function set_tempName($tempName) {
		$this->tempName = $tempName;
	}

	private function set_tempModule($module) {
		$this->tempModule = $module;
	}

	private function set_html($html) {
		$this->html = $html;
	}

	public function set_db(& $db) {

		$this->db = & $db;
	}

}

?>
