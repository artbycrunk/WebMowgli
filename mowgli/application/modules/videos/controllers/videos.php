<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of videos
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';

class videos extends Site_Controller implements I_Page_Render {

	private $module = 'videos';

	public function __construct() {

		parent::__construct();

		$this->load->library('templates');
		$this->load->library('videos/videos_render');
		$this->load->model('videos_model');

		$this->parseData['module:resource'] = module_resource_url($this->module);

		!defined('VIDEOS_DEFAULT_TEMPLATE') ? define('VIDEOS_DEFAULT_TEMPLATE', "default") : null;
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
			$template = isset($parseTagParts[1]) ? $parseTagParts[1] : VIDEOS_DEFAULT_TEMPLATE;

			$html = $this->_render_view($template);

			/* Set html for current block */
			$block['html'] = $html;
		}

		/* Update blocks with new data */
		$pagebean->setBlocks($moduleBlocks);

		return $pagebean;
	}

	private function _render_view($template) {

		$videos = $this->videos_model->get_videos(true); // onlyVisible = true

		$this->templates->load($this->module, $template);
//		$head = $this->templates->get_head();
		$html = $this->templates->get_html();

		if (!is_null($html)) {

			if (!is_null($videos) AND is_array($videos)) {

				/* 	Available parse tags for video
				 *
				 * {videos}
				 *	video:id
				 *	video:ref_id
				 *	video:title
				 *	video:description
				 *	video:image_url
				 *	video:script
				 *	video:order
				 *	video:is_visible
				 * {/videos}
				 */

				$this->parseData["videos"] = $this->videos_render->prepare_tags($videos);

				$html = $this->parser->parse_string($html, $this->parseData, true);
			} else {
				// no videos added
				$viewFile = $this->module . "/page_templates/no-videos";
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
