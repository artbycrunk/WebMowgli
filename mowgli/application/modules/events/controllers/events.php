<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of events
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';

class Events extends Site_Controller implements I_Page_Render {

	private $module = 'events';

	public function __construct() {

		parent::__construct();

		$this->load->library('templates');
		$this->load->library('events/events_render');

		$this->load->model('events_model');

		!defined('EVENTS_DEFAULT_TEMPLATE') ? define('EVENTS_DEFAULT_TEMPLATE', "default") : null;

//                $modulePath = site_url( $this->config->item('module_resource_root_uri') );
//                $this->parseData['module:resource'] = "$modulePath/" . $this->module;
		$this->parseData['module:resource'] = module_resource_url($this->module);
	}

	public function view($pageObj) {

		$pagebean = new Pagebean();
		$pagebean = $pageObj;

		$moduleBlocks = $pagebean->getBlocks();

		/* run through each block */
		foreach ($moduleBlocks as & $block) {

			/* Get parse tag mentioned in id attribute of extract tag */
			$parseTag = $block['tag'];
			$parseTagParts = explode(":", $parseTag);
			$template = isset($parseTagParts[1]) ? $parseTagParts[1] : EVENTS_DEFAULT_TEMPLATE;

			/* Set html for current block */
			$block['html'] = $this->_render_view($template);
		}

		/* Update blocks with new data */
		$pagebean->setBlocks($moduleBlocks);

		return $pagebean;
	}

	private function _render_view($template) {

		$events = $this->events_model->get_events();

		// get template from db
		$this->templates->load($this->module, $template);
//		$head = $this->templates->get_head();
		$html = $this->templates->get_html();

		// check if tempalte exists
		if (!is_null($html)) {

			if (!is_null($events) AND is_array($events)) {

				/*
				 * Event render tags
				 *
				  {events}

					'event:id'
					'event:name'
					'event:slug'
					'event:venue'

					'event:description'
					'event:excerpt'

					'event:start'
				 	'event:start:date'
					'event:start:month'
					'event:start:year'
					'event:start:time'

					'event:end'
				 	'event:end:date'
					'event:end:month'
					'event:end:year'
					'event:end:time'

				  {/events}
				 */


				$tags = $this->events_render->prepare_tags($events);
				$parsedata = array("events" => $tags);
				$this->parseData = array_merge($this->parseData, $parsedata);

				$html = $this->parser->parse_string($html, $this->parseData, true);
			} else {
				$viewFile = $this->module . "/page_templates/no-events";
				$html = $this->parser->parse($viewFile, $this->parseData, true);
			}
		} else {
			// template NOT found, load defaule template
			$html = "<p>Template NOT found</p>";
		}

		return $html;
	}

}

?>
