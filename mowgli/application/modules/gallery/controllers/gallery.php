<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gallery
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';

class Gallery extends Site_Controller implements I_Page_Render {

	private $module = 'gallery';

	public function __construct() {

		parent::__construct();

		$this->load->library('gallery/gallery_render');

		$modulePath = site_url($this->config->item('module_resource_root_uri'));
		$this->parseData['module:resource'] = "$modulePath/" . $this->module;
	}

	public function view($pageObj) {

		$pagebean = new Pagebean();
		$pagebean = $pageObj;

		$moduleBlocks = $pagebean->getBlocks();

		$position = Template_positions::get_instance();
//                $position->add( 'head', 'end', $this->gallery_render->get_html_head() );

		/* run through each block */
		foreach ($moduleBlocks as & $block) {

			/* Get parse tag mentioned in id attribute of extract tag */
			$parseTag = $block['tag'];

			$html = null;

			/* render gallery template ( head, body ) section, set variables accordingly */
			if ($this->gallery_render->render($parseTag)) {

				/* add head html to main page data */
//                                $pagebean->add_headHtml( $galleryHtml['head'] );
				$position->add('head', 'end', $this->gallery_render->get_html_head());

				$html = $this->gallery_render->get_html_body();
				$html = $this->parser->parse_string( $html, $this->parseData, true);

//                                $output = $this->parser->parse_string( $galleryHtml['body'], $this->parseData, TRUE );
			} else {
				$html = "<p>" . $this->gallery_render->get_error() . "</p>";
			}

			/* Set html for current block */
			$block['html'] = $html;

//                        $blocks[] = $block;
		}

		/* Update locks with new data */
		$pagebean->setBlocks($moduleBlocks);

		return $pagebean;
	}

}

?>
