<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of discography_render
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class discography_render {

	private $ci;
	private $module = 'discography';
	private $configFile = "discography_config";

	public function __construct($params = null) {

		$this->ci = & get_instance();

		$this->ci->config->load($this->configFile, true, true, $this->module);
	}

	/**
	 * processes discography Categories from database, creates parse tags array, returns parse tag array
	 *
	 * @param array[]|array[][] $categ expects single OR multidimensional array
	 */
	public function prepare_categ_tags($categs = null) {

		$parseArray = null;

		if (!is_null($categs) AND is_array($categs)) {

			// check if array is single or multidimensional array
			if (isset($categs[0]) AND is_array($categs[0])) {

				// array is multidimensional

				foreach ($categs as $categ) {

					$parseArray[] = array(
					    'discography:categ:id' => isset($categ['id']) ? $categ['id'] : "",
					    'discography:categ:name' => isset($categ['name']) ? $categ['name'] : "",
					    'discography:categ:slug' => isset($categ['slug']) ? $categ['slug'] : "",
					    'discography:categ:buy_url' => isset($categ['buy_url']) ? $categ['buy_url'] : "",
					    'discography:categ:download_url' => isset($categ['download_url']) ? $categ['download_url'] : "",
					    'discography:categ:image_url' => isset($categ['image_url']) ? $categ['image_url'] : "",
					    'discography:categ:description' => isset($categ['description']) ? $categ['description'] : "",
					    'discography:categ:is_visible' => isset($categ['is_visible']) ? $categ['is_visible'] : "",
					    'discography:categ:created' => isset($categ['created']) ? $categ['created'] : "",
					    'discography:categ:order' => isset($categ['order']) ? $categ['order'] : "",
					    'discography:categ:count' => isset($categ['count']) ? $categ['count'] : ""
					);
				}
			} else {
				// this is a single dimension array

				$parseArray = array(
				    'discography:categ:id' => isset($categs['id']) ? $categs['id'] : "",
				    'discography:categ:name' => isset($categs['name']) ? $categs['name'] : "",
				    'discography:categ:slug' => isset($categs['slug']) ? $categs['slug'] : "",
				    'discography:categ:buy_url' => isset($categs['buy_url']) ? $categs['buy_url'] : "",
				    'discography:categ:download_url' => isset($categs['download_url']) ? $categs['download_url'] : "",
				    'discography:categ:image_url' => isset($categs['image_url']) ? $categs['image_url'] : "",
				    'discography:categ:description' => isset($categs['description']) ? $categs['description'] : "",
				    'discography:categ:is_visible' => isset($categs['is_visible']) ? $categs['is_visible'] : "",
				    'discography:categ:created' => isset($categs['created']) ? $categs['created'] : "",
				    'discography:categ:order' => isset($categs['order']) ? $categs['order'] : "",
				    'discography:categ:count' => isset($categs['count']) ? $categs['count'] : ""
				);
			}
		} else {
			$parseArray = array(
			    'discography:categ:id' => "",
			    'discography:categ:name' => "",
			    'discography:categ:slug' => "",
			    'discography:categ:buy_url' => "",
			    'discography:categ:download_url' => "",
			    'discography:categ:image_url' => "",
			    'discography:categ:description' => "",
			    'discography:categ:is_visible' => "",
			    'discography:categ:created' => "",
			    'discography:categ:order' => "",
			    'discography:categ:count' => ""
			);
		}

		return $parseArray;
	}

	/**
	 * processes discography Items from database, creates parse tags array, returns parse tag array
	 *
	 * @param array[]|array[][] $item expects single OR multidimensional array
	 */
	public function prepare_item_tags($items = null) {

		$parseArray = null;


		$songsPageUri = $this->ci->config->item('songs_page', $this->configFile);
		$itemUrlRoot = site_url($songsPageUri) . "/";

		if (!is_null($items) AND is_array($items)) {

			// check if array is single or multidimensional array
			if (isset($items[0]) AND is_array($items[0])) {

				// array is multidimensional

				foreach ($items as $item) {

					$parseArray[] = array(
					    'discography:categ:name' => isset($item['categ_name']) ? $item['categ_name'] : "",
					    'discography:categ:slug' => isset($item['categ_slug']) ? $item['categ_slug'] : "",
					    'discography:item:id' => isset($item['id']) ? $item['id'] : "",
					    'discography:item:parent_id' => isset($item['parent_id']) ? $item['parent_id'] : "",
					    'discography:item:name' => isset($item['name']) ? $item['name'] : "",
					    'discography:item:slug' => isset($item['slug']) ? $item['slug'] : "",
					    'discography:item:description' => isset($item['description']) ? $item['description'] : "",
					    'discography:item:order' => isset($item['order']) ? $item['order'] : "",
					    'discography:item:is_visible' => isset($item['is_visible']) ? $item['is_visible'] : "",
					    'discography:item:url' => isset($item['slug']) ? $itemUrlRoot . $item['slug'] : ""
					);
				}
			} else {
				// this is a single dimension array

				$parseArray = array(
				    'discography:categ:name' => isset($items['categ_name']) ? $items['categ_name'] : "",
				    'discography:categ:slug' => isset($items['categ_slug']) ? $items['categ_slug'] : "",
				    'discography:item:id' => isset($items['id']) ? $items['id'] : "",
				    'discography:item:parent_id' => isset($items['parent_id']) ? $items['parent_id'] : "",
				    'discography:item:name' => isset($items['name']) ? $items['name'] : "",
				    'discography:item:slug' => isset($items['slug']) ? $items['slug'] : "",
				    'discography:item:description' => isset($items['description']) ? $items['description'] : "",
				    'discography:item:order' => isset($items['order']) ? $items['order'] : "",
				    'discography:item:is_visible' => isset($items['is_visible']) ? $items['is_visible'] : "",
				    'discography:item:url' => isset($items['slug']) ? $itemUrlRoot . $items['slug'] : ""
				);
			}
		} else {
			$parseArray = array(
			    'discography:categ:name' => "",
			    'discography:categ:slug' => "",
			    'discography:item:id' => "",
			    'discography:item:parent_id' => "",
			    'discography:item:name' => "",
			    'discography:item:slug' => "",
			    'discography:item:description' => "",
			    'discography:item:order' => "",
			    'discography:item:is_visible' => "1",
			    'discography:item:url' => ""
			);
		}

		return $parseArray;
	}

	public function prepare_music_page_tags($categs, $items, $splits = 0) {

		$tags = null;
		$splits = ( $splits <= 0 ) ? 1 : $splits;

		if (!is_null($categs)) {

			$this->ci->load->helper('array');

			// arrange categs by id
			$categsById = rearrange_array($categs, "id");

			// arrange items by parent_id
			$itemsByParent = rearrange_array($items, "parent_id", true);

			$itemChunks = array();

			// run through each categ and process tags
			foreach ($categsById as $categId => $categ) {

				$itemTags = array();

				// check if items present for given categ
				if (isset($itemsByParent[$categId])) {

					$currentItemArray = $itemsByParent[$categId];

					$size = ceil(count($currentItemArray) / $splits);

					$arrayParts = array_chunk($currentItemArray, $size, false);

					foreach ($arrayParts as $chunkNo => $itemsArray) {

						$itemChunks[$chunkNo] = $this->prepare_item_tags($itemsArray);
					}
				} else {

					$categTags['songs'] = null;

					// no items for current categ. no songs

//					$itemTagList1 = array($this->prepare_item_tags());
//					$itemTagList2 = array($this->prepare_item_tags());
				}

				$categTags = $this->prepare_categ_tags($categ);
//                                $categTags['discography:categ:items:list:1'] = $itemTagList1;
//                                $categTags['discography:categ:items:list:2'] = $itemTagList2;

				foreach ($itemChunks as $count => $itemTags) {



					if (count($itemChunks) > 1) {

						$chunkNo = $count + 1;
						$categTags["songs:$chunkNo"] = $itemTags;
						$categTags['songs'] = true;
					} else {

						// only one chunk of items
						$categTags['songs'] = $itemTags;
					}
				}

				$tags[] = $categTags;
			}
		}

		return $tags;
	}

	public function prepare_music_page_tags_TWO_LISTS($categs, $items) {

		/*
		 * Sample format for tags
		 *
		 * {discography:categs}
		 *
		 *      {categ . . . .  tags}

		  {discography:categ:items:list:1}

		  {item:url}
		  {item:name}

		  {/discography:categ:items:list:1}


		  {discography:categ:items:list:2}

		  {item:url}
		  {item:name}

		  {/discography:categ:items:list:2}

		  {/discography:categs}
		 *
		 *
		 */



		$tags = null;

		if (!is_null($categs)) {

			$this->ci->load->helper('array');

			// arrange categs by id
			$categsById = rearrange_array($categs, "id");

			// arrange items by parent_id
			$itemsByParent = rearrange_array($items, "parent_id", true);

			foreach ($categsById as $categId => $categ) {


				$itemTagList1 = array();
				$itemTagList2 = array();

				// check if items present for given categ
				if (isset($itemsByParent[$categId])) {

					$currentItemArray = $itemsByParent[$categId];

					$count = count($currentItemArray);
					$size = ceil($count / 2);

					$arrayParts = array_chunk($currentItemArray, $size, false);

					$itemTagList1 = $this->prepare_item_tags($arrayParts[0]);

					if (isset($arrayParts[1])) {

						$itemTagList2 = $this->prepare_item_tags($arrayParts[1]);
					}
//
				} else {

					// no items for current categ. no songs

					$itemTagList1 = array($this->prepare_item_tags());
					$itemTagList2 = array($this->prepare_item_tags());
				}

				$categTags = $this->prepare_categ_tags($categ);
				$categTags['discography:categ:items:list:1'] = $itemTagList1;
				$categTags['discography:categ:items:list:2'] = $itemTagList2;

				$tags[] = $categTags;
			}
		}

		return $tags;
	}

}

/* End of file discography_render.php */
/* Location: ./application/.... discography_render.php */
?>
