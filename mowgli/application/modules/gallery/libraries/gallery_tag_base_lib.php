<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of gallery_tag_base_lib
 *
 * @author Lloyd Saldanha
 */
class gallery_tag_base_lib {

	protected $ci;
	protected $module = 'gallery';
	protected $configFile = 'gallery_config';
	protected $error;
	protected $tags;
	private $view;  // viewKeyword, OR Gallery type
	private $ids = array(); // list of IDs

	public function __construct() {

		$this->ci = & get_instance();

		// load libraries
		$this->ci->load->library('gallery/gallery_images_library');

		// load config files
		$this->ci->config->load($this->configFile, true, true, $this->module);

		$this->reset();
	}

	protected function get_view() {
		return $this->view;
	}

	protected function set_view($view) {
		$this->view = $view;
	}

	protected function get_ids() {
		return $this->ids;
	}

	public function set_ids($ids) {
		$this->ids = $ids;
	}

	public function get_tags() {
		return $this->tags;
	}

	protected function set_tags($tags) {
		$this->tags = $tags;
	}

	public function get_error() {
		return $this->error;
	}

	protected function set_error($error) {
		$this->error = $error;
	}

	public function reset() {

		$this->set_error(null);
		$this->set_tags(array());
	}

	protected function _get_slug($string) {
		return url_title($string);
	}

	/**
	 * Converts the category data from database into parse tags
	 *
	 * Valid Tags for individual categories
	 *
	 * {categ:id}
	 * {categ:cover_id}
	 * {categ:type}
	 * {categ:name}
	 * {categ:slug}
	 * {categ:name_url}
	 * {categ:count}
	 * {categ:description}
	 * {categ:alt}
	 * {categ:uri}
	 * {categ:uri_thumb}
	 * {categ:order}
	 * {categ:created}
	 * {categ:modified}
	 * {categ:visible}
	 * {categ:images}
	 *
	 * @param array $categories Categories data from db
	 *
	 * @return array array of tags for categories
	 */
	protected function _get_category_parse_tags($categories) {

		$tags = null;

		/* Check if $categories not null */
		if (!is_null($categories)) {

			/* run through all categories */
			foreach ($categories as $row) {

				$imageCount = isset($row['count']) ? $row['count'] : 0;

				// Do not add categories that DO not have images
				if ($imageCount > 0) {

					$tags[] = array(
					    'categ:id' => isset($row['id']) ? $row['id'] : '',
					    //'categ:parent_id'       => isset ( $row['parent_id'] ) ? $row['parent_id'] : '',
					    'categ:cover_id' => isset($row['cover_id']) ? $row['cover_id'] : '',
					    'categ:type' => isset($row['type']) ? $row['type'] : '',
					    'categ:name' => isset($row['name']) ? $row['name'] : '',
					    'categ:slug' => isset($row['name']) ? $this->_get_slug($row['name']) : '',
					    'categ:name_url' => isset($row['name_url']) ? $row['name_url'] : '',
					    'categ:count' => isset($row['count']) ? $row['count'] : '',
					    'categ:description' => isset($row['description']) ? $row['description'] : '',
					    'categ:alt' => isset($row['alt']) ? $row['alt'] : '',
					    'categ:uri' => isset($row['uri']) ? $row['uri'] : '',
					    'categ:uri_thumb' => isset($row['uri_thumb']) ? $row['uri_thumb'] : '',
					    'categ:order' => isset($row['order']) ? $row['order'] : '',
					    'categ:created' => isset($row['created']) ? $row['created'] : '',
					    'categ:modified' => isset($row['modified']) ? $row['modified'] : '',
					    'categ:visible' => isset($row['visible']) ? $row['visible'] : '',
					    'images' => null
					    /* @deprecated
					     * 'categ:images' => null
					     */
					);
				}
			}
		}

		return $tags;
	}

	/**
	 * Converts the Image data from database into parse tags
	 *
	 * Valid Tags for individual Images
	 *
	 * {image:id}
	 * {image:parent_id}
	 * {image:category}
	 * {image:category:slug}
	 * {image:type}
	 * {image:name}
	 * {image:name_url}
	 * {image:description}
	 * {image:alt}
	 * {image:uri}
	 * {image:uri_thumb}
	 * {image:order}
	 * {image:created}
	 * {image:modified}
	 * {image:visible}
	 *
	 * @param array $images images data from db
	 *
	 * @return array array of tags for images
	 */
	protected function _get_image_parse_tags($images) {

		$tags = null;

		/* Check if $images not null */
		if (!is_null($images)) {

			/* run through each image */
			foreach ($images as $row) {

				$tags[] = array(
				    'image:id' => isset($row['id']) ? $row['id'] : '',
				    'image:parent_id' => isset($row['parent_id']) ? $row['parent_id'] : '',
				    'image:category' => isset($row['category']) ? $row['category'] : '',
				    'image:category:slug' => isset($row['category']) ? $this->_get_slug($row['category']) : '',
				    'image:type' => isset($row['type']) ? $row['type'] : '',
				    'image:name' => isset($row['name']) ? $row['name'] : '',
				    'image:name_url' => isset($row['name_url']) ? $row['name_url'] : '',
				    'image:description' => isset($row['description']) ? $row['description'] : '',
				    'image:alt' => isset($row['alt']) ? $row['alt'] : '',
				    'image:uri' => isset($row['uri']) ? $row['uri'] : '',
				    'image:uri_thumb' => isset($row['uri_thumb']) ? $row['uri_thumb'] : '',
				    'image:order' => isset($row['order']) ? $row['order'] : '',
				    'image:created' => isset($row['created']) ? $row['created'] : '',
				    'image:modified' => isset($row['modified']) ? $row['modified'] : '',
				    'image:visible' => isset($row['visible']) ? $row['visible'] : '',
				);
			}
		}

		return $tags;
	}

	/** Stores images tags under respective category tag, based on category id */
	public function merge_categ_image_tags($categTags, $imageTags) {

		$return = null;

		/* rearange images according to category id */
		$imageTags = $this->_rearrange_array($imageTags, 'image:parent_id');
		$categTags = $this->_rearrange_array($categTags, 'categ:id');

		/* run through categories, save imageTags under categories['images'] */
		foreach ($categTags as $categId => $tagList) {

			// check if category has any images, if yes --> process further, else ignore
			if (isset($imageTags[$categId])) {

				// run through each image under current category
				foreach ($tagList as $tag) {

					/* @depricated
					$tag['categ:images'] = $imageTags[$categId];
					*/
					$tag['images'] = $imageTags[$categId];
					$return[] = $tag;
				}
			}
		}

		return $return;
	}

	/** rearranges a given array by the menitoned key
	 * @param array $array
	 * @param string $key the key by which the array should be arranged
	 *
	 * @return array|null returns null if key does not exist
	 */
	private function _rearrange_array($array, $key) {

		$return = null;

		/* run through array */
		foreach ($array as $element) {

			$arrangeBy = isset($element[$key]) ? $element[$key] : null;

			/* check if key is present in provided array, if not terminate */
			if (!is_null($arrangeBy)) {

				$return[$arrangeBy][] = $element;
			} else {
				$return = null;
				break;
			}
		}

		return $return;
	}

	//******************* VIEWs ******************************

	/**
	 * Generates tags in hte format
	 *
	 * {categories}
	 *
	 * 	{categ:...}
	 * 	{categ:...}
	 *
	 * 	{images}
	 *
	 * 		{image:...}
	 * 		{image:...}
	 *
	 * 	{/images}
	 * {/categories}
	 */
	protected function view_galleries() {

		/* Get categories, images from database */

		$categories = $this->ci->gallery_images_library->get_categories($this->get_ids());
		$images = $this->ci->gallery_images_library->get_images_by_categories($this->get_ids());

		if (!is_null($categories) AND !is_null($images)) {

			/* create parse tags from data */
			$categTags = $this->_get_category_parse_tags($categories);
			$imageTags = $this->_get_image_parse_tags($images);
			$tags['categories'] = $this->merge_categ_image_tags($categTags, $imageTags);

			$this->set_tags($tags);
			$success = true;
		} else {
			$success = false;
		}

		return $success;
	}

	/**
	 * Generates tags in hte format
	 *
	 * {categories}
	 *
	 * 	{categ:...}
	 * 	{categ:...}
	 *
	 * {/categories}
	 *
	 * {images}
	 *
	 * 	{image:...}
	 * 	{image:...}
	 *
	 * {/images}
	 */
	protected function view_galleries_split() {

		/* Get categories, images from database */

		$categories = $this->ci->gallery_images_library->get_categories($this->get_ids());
		$images = $this->ci->gallery_images_library->get_images_by_categories($this->get_ids(), true, true); // onlyVisible, OrderOnlyByImages

		if (!is_null($categories) AND !is_null($images)) {

			/* create parse tags from data */
			$tags['categories'] = $this->_get_category_parse_tags($categories);
			$tags['images'] = $this->_get_image_parse_tags($images);

			$this->set_tags($tags);
			$success = true;
		} else {
			$success = false;
		}

		return $success;
	}

	/**
	 * Generates tags in hte format
	 *
	 * {categories}
	 *
	 * 	{categ:...}
	 * 	{categ:...}
	 *
	 * {/categories}
	 */
	protected function view_categories() {

		/* Get categories, images from database */
		$categories = $this->ci->gallery_images_library->get_categories($this->get_ids());
		if (!is_null($categories)) {

			$tags['categories'] = $this->_get_category_parse_tags($categories);

			$this->set_tags($tags);
			$success = true;
		} else {
			$success = false;
		}

		return $success;
	}

	/**
	 * Generates tags in hte format
	 *
	 * {images}
	 *
	 * 	{image:...}
	 * 	{image:...}
	 *
	 * {/images}
	 */
	protected function view_images() {

		$images = $this->ci->gallery_images_library->get_images($this->get_ids());

		if (!is_null($images)) {

			$tags['images'] = $this->_get_image_parse_tags($images);

			$this->set_tags($tags);
			$success = true;
		} else {
			$success = false;
		}

		return $success;
	}

}

?>
