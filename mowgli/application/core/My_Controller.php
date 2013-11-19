<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// note: loading interfaces not required, since it is loaded automatically by CI in application/core
//                require_once APPPATH . "core/I_Page.php";
//                require_once APPPATH . "core/I_Admin.php";

/**
 * Description of My_Controller
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class My_Controller extends MX_Controller {

	protected $parseData = array();
	protected $paths = array();
	protected $software = array();

//    private $success = array();
//    private $errors = array();
//    private $warnings = array();
//    private $infos = array();

	public function __construct() {

		parent::__construct();

		// enable profiling in case of development environment
		ENVIRONMENT == 'testing' ? $this->output->enable_profiler(TRUE) : null;

		$this->config->load('config_software');
		$this->config->load('site_config');
		$this->config->load('site_settings');

		/* Software details */
//        $this->software['name'] = $this->config->item('software_name');

		/* Relative paths */
		$this->paths['site_resource'] = $this->config->item('site_resource_path');
		$this->paths['module_resource'] = $this->config->item('module_resource_root_path');
		$this->paths['admin_resource'] = $this->config->item('admin_resource_path');

		/* Parse Tags */
		$this->parseData['site:root'] = substr(site_url(), 0, -1); // remove trailing slash '/'
		$this->parseData['site:resource'] = site_url($this->config->item('site_resource_uri'));

		$this->parseData['admin:root'] = site_url($this->config->item('admin_uri'));
		$this->parseData['admin:resource'] = site_url($this->config->item('admin_resource_uri'));
		$this->parseData['admin:resource:front'] = site_url($this->config->item('admin_resource_uri') . "/front");
		$this->parseData['admin:title'] = $this->config->item('software_name');

		$this->parseData['sidebar:logo'] = $this->config->item('software_name');
		$this->parseData['sidebar:logo_url'] = $this->config->item('software_url');


		$this->parseData['environment'] = ENVIRONMENT;

		//////////////Arrays //////////////

//		$this->parseData['site'] = array(
//		    'root' => rtrim(site_url(), '/'),
//		    'resource' => site_url($this->config->item('site_resource_uri'))
//		);
//		$this->parseData['admin'] = array(
//		    'root' => site_url($this->config->item('admin_uri')),
//		    'resource' => site_url($this->config->item('admin_resource_uri')),
//		    'resource_front' => site_url($this->config->item('admin_resource_uri') . "/front"),
//		    'title' => $this->config->item('software_name')
//		);
//
//		$this->parseData['sidebar'] = array(
//		    'logo' => $this->config->item('software_name'),
//		    'logo_url' => $this->config->item('software_url')
//		);
	}

}

?>