<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of discography
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';

// positiong of slug in url fo songs.html
!defined("DISCOGRAPHY_SLUG_POSITION") ? define("DISCOGRAPHY_SLUG_POSITION", 2) : null;

class discography extends Site_Controller implements I_Page_Render {

	private $module = 'discography';

	public function __construct() {

		parent::__construct();

		!defined('DISCO_DEFAULT_TEMPLATE') ? define('DISCO_DEFAULT_TEMPLATE', "default") : null;

		$this->load->library('templates');
		$this->load->library($this->module . '/discography_render');

		$this->load->model($this->module . '/discography_model');

		$this->load->helper('text');

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
			$template = isset($parseTagParts[1]) ? $parseTagParts[1] : DISCO_DEFAULT_TEMPLATE;

			$this->templates->load($this->module, $template);
//		$head = $this->templates->get_head();
			$html = $this->templates->get_html();

			$method_name = "_" . $template;

			if (method_exists($this, $method_name)) {

				$html = $this->$method_name($html);
			} else {
				// template name in html tag DOES not match any view
				$html = $this->_no_display();
			}

			/* Set html for current block */
			$block['html'] = $html;
		}

		/* Update blocks with new data */
		$pagebean->setBlocks($moduleBlocks);

		return $pagebean;
	}

	private function _albums($html) {

		$output = null;

		// get categs( albums ) from db
		$categs = $this->discography_model->get_categs(null, true); // onlyVisible = true, limit = <default>
		// if no categs, display no albums message, else render and display list
		if (!is_null($categs)) {

			// get items ( songs ) from db
			$items = $this->discography_model->get_items_front_display(null, null, true);

			/*
			 * Sample format for tags
			 *
			 * {albums}
			 *
			 *	{discography:categ:id}
			 *	{discography:categ:name}
			 *	{discography:categ:slug}
			 *	{discography:categ:buy_url}
			 *	{discography:categ:download_url}
			 *	{discography:categ:image_url}
			 *	{discography:categ:description}
			 *	{discography:categ:is_visible}
			 *	{discography:categ:created}
			 *	{discography:categ:order}
			 *	{discography:categ:count}

			 *	{discography:categ:items:list:1}
			 *		{discography:categ:name}
			 *		{discography:categ:slug}

			 *		{discography:item:id}
			 *		{discography:item:parent_id}
			 *		{discography:item:name}
			 *		{discography:item:slug}
			 *		{discography:item:description}
			 *		{discography:item:order}
			 *		{discography:item:is_visible}
			 *		{discography:item:url}
			 *	{/discography:categ:items:list:1}

			 *	{discography:categ:items:list:2}
			 *		{discography:categ:name}
			 *		{discography:categ:slug}

			 *		{discography:item:id}
			 *		{discography:item:parent_id}
			 *		{discography:item:name}
			 *		{discography:item:slug}
			 *		{discography:item:description}
			 *		{discography:item:order}
			 *		{discography:item:is_visible}
			 *		{discography:item:url}
			 *	{/discography:categ:items:list:2}

			 * {/albums}
			 *
			 *
			 */


			$this->parseData['albums'] = $this->discography_render->prepare_music_page_tags($categs, $items, 1);


			$output = $this->parser->parse_string($html, $this->parseData, true);
		} else {

			$output = "<p>No albums have been added yet, please watch this space to stay updated</p>";
		}

		return $output;
	}

	private function _song( $html ) {

		/**
		 * get slug from url
		 * get song data using slug
		 * if NOt found --> 404
		 * if found --> render tags
		 * set page title, meta, description for page
		 * parse tags in view
		 * return view
		 */
		$output = null;

		// get slug name from url
		$slug = $this->uri->segment(DISCOGRAPHY_SLUG_POSITION, null);

		// get item id using slug name
		$itemId = $this->discography_model->get_item_id_by_slug($slug);

		// if song does not exist --> show 404, else process
		if (!is_null($itemId)) {

			// song found, render song and display
			$item = $this->discography_model->get_item($itemId, true);
			$itemTags = $this->discography_render->prepare_item_tags($item);

			/*
			 *{discography:categ:name}
			 *{discography:categ:slug}

			 *{discography:item:id}
			 *{discography:item:parent_id}
			 *{discography:item:name}
			 *{discography:item:slug}
			 *{discography:item:description}
			 *{discography:item:order}
			 *{discography:item:is_visible}
			 *{discography:item:url}
			 */

			$this->parseData = array_merge($this->parseData, $itemTags);

			$output = $this->parser->parse_string($html, $this->parseData, true);

			// set meta data from database values
			$songName = isset($item['name']) ? $item['name'] : null;
			$albumName = isset($item['categ_name']) ? $item['categ_name'] : null;
			$songDescription = isset($item['description']) ? strip_tags($item['description']) : null;

			$title = "$songName - $albumName";
			$description = word_limiter($songDescription, 40);
			$keywords = null;

			// set meta values
//                        $position = new Template_positions($params);
			$position = Template_positions::get_instance();

			$position->add_meta('title', $title);
			$position->add_meta('description', $description);
			$position->add_meta('keywords', $keywords);
		} else {

			show_page_404();
		}

		return $output;
	}

	private function _no_display() {

		return "<p>view not defined</p>";
	}

}

?>
