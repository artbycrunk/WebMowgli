<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gallery_images_library
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class gallery_images_library {

	private $ci;
	private $module = 'gallery';
	private $configFile = 'gallery_config';    // name of config file for gallery ( gallery_config.php )
	private $config_database = 'gallery_config_database';    // name of config file for gallery database ( gallery_config_database.php )
	private $moduleRoot;
	private $imagesRootUrl;
	private $imagesRootPath;
	private $insertedImageIds = array();

	// public function  __construct( $params ) {
	public function __construct() {

		$this->ci = & get_instance();
//
//                $this->module = $params['module'];
//                $this->configFile = $params['configFile'];
//                $this->config_database = $params['config_database'];

		/* Load configuration files */
		$this->ci->config->load($this->configFile, true, true, $this->module);
		$this->ci->config->load($this->config_database, true, true, $this->module);

		// load helper
		$this->ci->load->helper('directory');

		/* Set module root folder */
		$moduleUri = $this->ci->config->item("module_resource_root_uri");
		$this->moduleRoot = site_url($moduleUri);

		/* Set images root folder */
//                $imagesUri = $moduleUri . '/' . $this->ci->config->item( "gallery_images_uri", $this->configFile );
		$imagesUri = upload_module_uri($this->ci->config->item("gallery_images_uri", $this->configFile));
		$this->imagesRootUrl = site_url($imagesUri);

		$this->imagesRootPath = site_path($imagesUri);
	}

	public function get_insertedImageIds() {
		return $this->insertedImageIds;
	}

	/* renames and copies source file to destinaiton folder
	 * @return string|null returns destination file path on success, returns null on failure  */

	public function process_image($sourceFile, $destPath) {

		$destFile = null;

		// create images upload path if not exists
		create_dir_path(FCPATH . "/$destPath");

		$oldName = pathinfo($sourceFile, PATHINFO_FILENAME);
		$ext = pathinfo($sourceFile, PATHINFO_EXTENSION);
		$newName = $this->_rename_image($oldName, $ext, $destPath);
		$destFile = "$destPath/$newName";

//                echo "sourceFile = $sourceFile <br/><br/>";
//                echo "destFile = $destFile <br/><br/>";

		if (copy($sourceFile, $destFile) == false)
			$destFile = null;

		return $destFile;
	}

	public function import_images($fileLocations, $categId, & $db) {

		$success = true;

		$images = array();      // only for deleting images & thumbnails incase failure

		$modulePath = $this->ci->config->item('module_resource_root_path');
//                $imagesRootPath = $modulePath . "/" . $this->ci->config->item( 'gallery_images_uri', $this->configFile );
		$imagesRootPath = upload_module_uri($this->ci->config->item('gallery_images_uri', $this->configFile));
		$itemType = $this->ci->config->item('items_type_image', $this->config_database);

		$order = $this->ci->gallery_model->get_max_item_order($itemType, $categId);

		if (!is_null($order)) {

			$order += 1;

			/* process each image */
			foreach ($fileLocations as $file) {

				$file = realpath($file);
				$imageName = pathinfo($file, PATHINFO_FILENAME);

				/* Rename and Copy file to images folder */
				$file = $this->process_image($file, $imagesRootPath);

				/* Check if file copied to images folder successfully */
				if (!is_null($file)) {

					$images[] = $file;

					/* Create thumbnail, save in images folder */
					$fileThumb = $this->create_thumbnail($file, pathinfo($file, PATHINFO_DIRNAME));
					$images[] = $fileThumb;

					/* Check if thumbnail successfully created */
					if (!is_null($fileThumb)) {

						$imagesDB = array(
						    //      'gallery_item_id' => null,
						    'gallery_item_parent_id' => $categId,
						    //      'gallery_item_cover_id'         => null,
						    'gallery_item_type' => $itemType,
						    'gallery_item_name' => $imageName,
						    //'gallery_item_name_url'                 => null,
						    //      'gallery_item_desc'             => null,
						    //      'gallery_item_alt'              => null,
						    'gallery_item_uri' => pathinfo($file, PATHINFO_BASENAME),
						    'gallery_item_uri_thumb' => pathinfo($fileThumb, PATHINFO_BASENAME),
						    'gallery_item_order' => $order,
						    'gallery_item_created' => get_gmt_time(),
						    'gallery_item_modified' => get_gmt_time(),
						    'gallery_item_is_visible' => 1
						);

						/* write image to database, check if successfull */
						$newImgId = $this->ci->gallery_model->create_item($imagesDB);
						if ($newImgId) {

							$this->insertedImageIds[] = $newImgId;
							$success = true;
							$order += 1;
						} else {
							// error while writing to database
							$success = false;
							break;
						}
					} else {
						// error creating thumbnail
						$success = false;
						break;
					}
				} else {
					// unable to copy file to images folder
					$success = false;
					break;
				}
			} // end foreach
		} else {
			$success = false;
		}


		/* delete created files if unsuccessfull */
		if ($success == false) {

			foreach ($images as $file) {

				if (file_exists($file)) {
					unlink($file);
				}
			}
		}

		return $success;
	}

	/**
	 * Renames Image based on available names
	 * - Checks $destPath ( image upload folder ) if file exists
	 * - if exists, Increments file name by 1 ( e.g. car.jpg, car-1.jpg, car-2.jpg . . .etc )
	 * - Returns new file name
	 *
	 * @param string $name Name of Image
	 * @param string $extension Extension of image
	 * @param string $destPath Folder where images will be uploaded
	 *
	 * @return string Returns full new name of image ( e.g. car-1.jpg )
	 */
	private function _rename_image($name, $extension, $destPath) {

		$name = strtolower(url_title($name));

		// Check if file exists, if yes--> increment filename, else return current filename
		do {
			$fullPath = $destPath . "/" . $name . "." . $extension;

			if (file_exists($fullPath)) {
				// increment name by 1 ( e.g. -1, -2 . . etc )
				$name = increment_string($name, '-');
			} else {
				break;
			}
		} while (file_exists($fullPath));

		return $name . "." . $extension;
	}

	// create thumbnail, store in destination path, return file path
	public function create_thumbnail($sourceFile, $destPath) {

		$return = null;

		/**
		 * @todo load thumbnail sizes from gallery settings
		 */
		// gallery image - config values
		$width = get_settings('gallery', 'thumbnail_width');
		$height = get_settings('gallery', 'thumbnail_height');
		$thumbPrefix = $this->ci->config->item('images_thumbnail_prefix', $this->configFile);

		$destFile = "$destPath/$thumbPrefix" . pathinfo($sourceFile, PATHINFO_BASENAME);

		$config['image_library'] = 'gd2';
		$config['thumb_marker'] = '';
		$config['source_image'] = $sourceFile;
		$config['new_image'] = $destFile;
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $width;
		$config['height'] = $height;

//                $this->ci->load->library('image_lib', $config);
		$this->ci->load->library('image_lib');
		$this->ci->image_lib->initialize($config);

		$success = $this->ci->image_lib->resize();
		if ($success) {
			$return = $destFile;
		}

		$this->ci->image_lib->clear();

		return $return;
	}

	/**
	 * returns list of images
	 * returns null if no images
	 */
	public function get_images_by_categories($categIds = null, $onlyVisible = true, $orderOnlyByImages = false) {

		return $this->ci->gallery_model->get_images_by_categories($this->imagesRootUrl . '/', $categIds, $onlyVisible, $orderOnlyByImages);
	}

	public function get_categories($categIds = null, $onlyVisible = true, $showHiddenCoverImgs = true) {

		return $this->ci->gallery_model->get_categories($this->imagesRootUrl . '/', $categIds, $onlyVisible, $showHiddenCoverImgs);
	}

	public function get_images($imageIds = null, $onlyVisible = true) {

		return $this->ci->gallery_model->get_images($this->imagesRootUrl . '/', $imageIds, $onlyVisible);
	}

	/**
	 * Checks if itemIds are 'category' or 'image'
	 * if 'category' --> get url, url_thumb for categIds
	 * if 'image' --> get url, url_thumb for imageIds
	 *
	 * @param array $itemIds list of ids ( either category OR image )
	 * @param string $type expected values either 'image' or 'category'
	 *
	 * @return bool
	 */
	public function delete_images_physically($itemIds, $type) {

		$success = false;

		$imageRoot = $this->imagesRootPath . "/";
		$ItemTypeCateg = $this->ci->config->item('items_type_category', $this->config_database);
//                $ItemTypeImage = $this->ci->config->item( 'items_type_image', $this->config_database );

		$filePaths = $this->ci->gallery_model->get_image_paths($imageRoot, $itemIds, $type);

		// check if file exists and delete from file system
		if (!is_null($filePaths)) {

			foreach ($filePaths as $file) {

				if (file_exists($file)) {
					unlink($file);
				}
			}

			$success = true;
		}
		// either no images under mentioned categories OR some error occured
		else {

			// if type is only categ & no images in any categ --> set success to true, else false
			if ($type == $ItemTypeCateg)
				$success = true;
			else
				$success = false;
		}

//                $this->ci->gallery_model->delete_items( $itemIds );

		return $success;
	}

	public function get_image_upload_uri() {
		// module_resource_root_path
//                $moduleRootUri = $this->ci->config->item('module_resource_root_uri');
//                $themeRootUri = $this->ci->config->item( 'themes_resource_uri', $this->configFile );
//                $themeUri = "$moduleRootUri/$themeRootUri/" . $this->themeName;
		$imageUploadUri = "/" . upload_module_uri($this->ci->config->item('gallery_images_uri', $this->configFile));

		return $imageUploadUri;
	}

}

?>
