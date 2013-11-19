<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of videos_render
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class videos_render {

	private $ci;
	private $module = 'videos';

//        private $templateViewDir;
//        private $videoTags = array();

	public function __construct($params = null) {

		$this->ci = & get_instance();

//                $this->templateViewDir = $this->module . "/page_templates";
	}

	// processes videos from database, creates parse tags array, returns parse tag array
	/**
	 * @param array[]|array[][] $videos expects single OR multidimensional array
	 */
	public function prepare_tags($videos = null) {

		$parseArray = null;

		if (!is_null($videos) AND is_array($videos)) {

			// check if array is single or multidimensional array
			if (isset($videos[0]) AND is_array($videos[0])) {

				// array is multidimensional

				foreach ($videos as $video) {

					$parseArray[] = array(
					    'video:id' => $video['id'],
					    'video:ref_id' => $video['ref_id'],
					    'video:title' => $video['title'],
					    'video:description' => $video['description'],
//					    'video:image_url' => site_url($video['image_url']),
					    'video:image_url' => $video['image_url'],
					    'video:script' => $video['script'],
					    'video:order' => $video['order'],
					    'video:is_visible' => $video['is_visible']
					);
				}
			} else {
				// this is a single dimension array

				$parseArray = array(
				    'video:id' => $videos['id'],
				    'video:ref_id' => $videos['ref_id'],
				    'video:title' => $videos['title'],
				    'video:description' => $videos['description'],
//				    'video:image_url' => site_url($videos['image_url']),
				    'video:image_url' => $videos['image_url'],
				    'video:script' => $videos['script'],
				    'video:order' => $videos['order'],
				    'video:is_visible' => $videos['is_visible']
				);
			}
		} else {
			$parseArray = array(
			    'video:id' => "",
			    'video:ref_id' => "",
			    'video:title' => "",
			    'video:description' => "",
			    'video:image_url' => "",
			    'video:script' => "",
			    'video:order' => "",
			    'video:is_visible' => ""
			);
		}

		return $parseArray;
	}

}

/* End of file videos_render.php */
/* Location: ./application/.... videos_render.php */
?>
