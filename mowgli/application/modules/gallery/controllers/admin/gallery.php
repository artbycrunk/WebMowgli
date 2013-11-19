<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 * Description of gallery
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//GALLERY_DEFAULT_CATEG_ID
!defined("GALLERY_DEFAULT_CATEG_ID") ? define("GALLERY_DEFAULT_CATEG_ID", 1) : null;

class gallery extends Admin_Controller implements I_Admin_Extract {

	private $module = "gallery";
	private $configFile = 'gallery_config';
	private $config_database = "gallery_config_database";

	public function __construct() {

		parent::__construct();

		$this->config->load($this->configFile, true, true, $this->module);
		$this->config->load($this->config_database, true, true, $this->module);

		// Load libraries
		$this->load->library('templates');
		$this->load->library('gallery/gallery_render');
		$this->load->library('gallery/gallery_themes_library');
		$this->load->library('gallery/gallery_images_library');
		$this->load->library('json_response');

		// Load helpers
		$this->load->helper('admin_helper');

		// Load models
		$this->load->model('gallery/gallery_model');

		$this->parseData['module:resource'] = module_resource_url($this->module);
	}

	public function _extract_content($tempId, $parseTag, $innerText, & $db) {

		//  split the tag string by ':', create an array of key-value pair
		$tagParts = $this->gallery_render->decode_block_tag($parseTag);

		$tempId = null;

		// if template mentioned in tag string, create template in db
		if (isset($tagParts['template'])) {

			// add template, if template exists --> do not add new one, simply get tempId
			$this->templates->set_db($db);
			$tempId = $this->templates->add_module_template($this->module, $tagParts['template'], $innerText, null, null, true); // head=null, desc=null, visible=true
		}

		$tag = array(
//                    'tag_id' => null,
		    'tag_module_name' => $this->module,
		    'tag_temp_id' => $tempId,
		    'tag_keyword' => $parseTag,
		    'tag_name' => $parseTag,
		    'tag_data_id' => isset($tagParts['ids']) ? implode(',', $tagParts['ids']) : null
			//    'tag_description' => null
		);


		$this->gallery_model->set_db($db);
		$tagId = $this->gallery_model->create_tag($tag);

		return $tagId > 0 ? $tagId : null;
	}

	//------------------- Gallery Theme Import controllers --------------------------

	public function import_theme() {

		$pageName = "Import Gallery Theme";

		$this->load->helper('form');
		$this->parseData['admin:error_message'] = validation_errors();

		$this->parseData['action_url'] = site_url('admin/gallery/import_theme_do');
		$output = $this->parser->parse('gallery/import_theme', $this->parseData, true);
		unset($this->parseData['action_url']);

//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);

		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	public function import_theme_do() {

		$pageName = "Import Gallery Theme";

		/* Set configuration for file upload */
		$config['upload_path'] = $this->config->item('dump_path');
		$config['allowed_types'] = $this->config->item('zip_filetype', $this->configFile);
		$config['max_size'] = $this->config->item('zip_max_size', $this->configFile); // KB
		$config['remove_spaces'] = true;

		$this->load->library('upload', $config);

		$output = null;

		/* Check if upload is valid */
		if ($this->upload->do_upload('zip-file')) {

			/* Get details of zip file uploaded */
			$fileData = $this->upload->data();
			$zipFile = $fileData['full_path'];

			/* Set value for unzipping */
			$dumpPath = $this->config->item('dump_path');
			$unzipPath = "$dumpPath/gallery-theme-" . time(); // temporary, deleted later

			$unzipPath = mkdir($unzipPath) == false ? null : $unzipPath;

			$allowedFiletypes = $this->config->item('allowed_filetypes', $this->configFile);
			//$allowedFiletypes = null;
			$unzipPreservePath = $this->config->item('unzip_preserve_path', $this->configFile);

			/* Unzip file */
			$this->load->library('unzip');
			$fileLocations = $this->unzip->unzip_file($zipFile, $unzipPath, $allowedFiletypes, $unzipPreservePath);

			/* check if successfully unzipped */
			if ($fileLocations != false) {

				$themeConfig = $this->gallery_themes_library->get_theme_config($unzipPath);
				$themeVersion = $themeConfig['version'];
				$this->gallery_themes_library->set_theme_config($themeConfig);
				$this->gallery_themes_library->set_theme_name($themeConfig['name']);
				$themeName = $this->gallery_themes_library->get_theme_name();

				/* Check if theme config is valid */
				if (!is_null($themeConfig)) {

//                                        $this->load->model('gallery/gallery_model');
					$themeDetails = $this->gallery_model->get_theme_details($themeName);

					/* check if theme already installed */
					if (is_null($themeDetails)) {

						// SUCCESS - process further here

						/* Create theme directory */
						if ($this->gallery_themes_library->create_theme_dir()) {

//                                                        $themeDir = $this->gallery_themes_library->get_theme_resource_path();
//                                                        $themeDir = realpath( $themeDir );

							$fileDetails = $this->gallery_themes_library->separate_pages_from_resources($fileLocations, 'all');
							$templates = & $fileDetails['templates'];
							$resources = & $fileDetails['resources'];

							/* Get global script for theme ( returns string OR null ), set warnings if no scripts found */
							$themeScript = $this->gallery_themes_library->get_theme_script($unzipPath);
//////////////                                                        $themeScript = $this->gallery_themes_library->process_links( $themeScript );

							$templates = $this->gallery_themes_library->remove_main_theme_file($templates, $unzipPath);

							$themeDb = array(
							    //      'gallery_theme_id' => null,
							    'gallery_theme_name' => $themeName,
							    'gallery_theme_resource_uri' => $this->gallery_themes_library->get_theme_resource_uri(),
							    'gallery_theme_scripts' => $themeScript,
							    'gallery_theme_version' => $themeVersion
							);

							/* Load and set database transactions */
//                                                        $this->load->model('gallery/gallery_model');
							$db = & $this->gallery_model->get_db();
							$this->gallery_model->transaction_strict(true);
							$this->gallery_model->transaction_begin();

							$themeId = $this->gallery_model->create_theme($themeDb);

							$resourceSuccess = $this->gallery_themes_library->import_theme_resources($resources, $unzipPath, $db);

							if ($resourceSuccess) {


								$templateSuccess = $this->gallery_themes_library->import_theme_templates($templates, $db);

								if ($templateSuccess) {

									$this->gallery_model->transaction_commit();

									$output = "<p>Gallery theme successfully imported</p>";
								}
							} else {

								// error while copying resources to resource folder
								// Revert transactions
								$this->gallery_model->transaction_rollback();
							}


							/* TESTING ONLY */
//                                                        if( false ) { // isTest ?
//
//                                                                $this->gallery_model->delete_themes( array( $themeId ) );
//                                                                //$this->load->helper('file');
//                                                                //delete_files( $themeDir, TRUE);
//
//                                                        }
						} else {

							$output = "<p>Unable to create Theme resource directory</p>";
						}
					} else {

						$output = "<p>A theme with the same name already exists</p>";
					}
				} else {

					$output = "<p>Invalid config data provided</p>";
				}
			} else {
				/* NOT successfully unzipped, call back form, parse error message */
				$output = "<p>uploaded file could not be extracted</p>";
			}


			/* delete zip file */
			unlink($zipFile);

			/* Delete temporary unzip directory from dump */
			$this->load->helper('file');
			delete_files($unzipPath, TRUE); // true --> delete inner files and directories
			rmdir($unzipPath);
		} else {

			$output = "<p>File could not be uploaded, provide valid upload filetype ( .zip )</p>";
		}

//
//
//
//                // TEMPORARY REMOVE THIS
//                $this->load->library('notifications');
//                foreach ( $this->notifications->get() as $type => $list ) {
//                        $output .= "<h2>$type</h2>";
//                        foreach ( $list as $message ) {
//
//                                $output .= "<p>$message</p>";
//                        }
//                }
//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);

		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	//------------------- Gallery Zip Image upload controllers --------------------------

	public function upload_images() {


		$pageName = "Upload Images";

		$this->load->helper('form');
		$this->parseData['admin:error_message'] = validation_errors();

		$this->parseData['action_url'] = site_url('admin/gallery/upload_images_do');
		$output = $this->parser->parse('gallery/upload_images', $this->parseData, true);
		unset($this->parseData['action_url']);

//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);

		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	public function upload_images_do() {

		$pageName = "Upload Images";

		/* Set configuration for file upload */
		$config['upload_path'] = $this->config->item('dump_path');
		$config['allowed_types'] = $this->config->item('zip_filetype', $this->configFile);
		$config['max_size'] = $this->config->item('zip_max_size', $this->configFile); // KB
		$config['remove_spaces'] = true;

		$this->load->library('upload', $config);

		$output = null;

		if ($this->upload->do_upload('zip-file')) {

			/* Get details of zip file uploaded */
			$fileData = $this->upload->data();
			$zipFile = $fileData['full_path'];

			/* Set value for unzipping */
			$dumpPath = $this->config->item('dump_path');
			$unzipPath = "$dumpPath/gallery-images-" . time(); // temporary, deleted later

			$unzipPath = mkdir($unzipPath) == false ? $dumpPath : $unzipPath;

			$allowedFiletypes = $this->config->item('images_allowed_filetypes', $this->configFile);

			/* Unzip file */
			$this->load->library('unzip');
			$fileLocations = $this->unzip->unzip_file($zipFile, $unzipPath, $allowedFiletypes, true);  // false = do not preserve path

			/* check if successfully unzipped */
			if ($fileLocations != false) {

//                                $params = array(
//                                        'module' => $this->module,
//                                        'configFile' => $this->configFile,
//                                        'config_database' => $this->config_database
//                                );
//                                $this->load->library( 'gallery/gallery_images_library', $params );
				$this->load->library('gallery/gallery_images_library');
				//$this->load->helper('date');
//                                $this->load->library( 'gallery/gallery_model' );
				$db = & $this->gallery_model->get_db();

				$this->gallery_model->transaction_strict(true);
				$this->gallery_model->transaction_begin();
				$categId = $this->gallery_model->get_categ_id(); // returns default id if categ not specified

				$success = $this->gallery_images_library->import_images($fileLocations, $categId, $db);

				if ($success) {
					$this->gallery_model->transaction_commit();
					$output = "<p>Images Successfully Uploaded</p>";
				} else {
					$this->gallery_model->transaction_rollback();
					$output = "<p>Error occured while uploading images</p>";
				}
			} else {
				/* NOT successfully unzipped, call back form, parse error message */
				$output = "<p>uploaded zip file could not be extracted or no files found, check file types</p>";
			}

			/* delete zip file */
			unlink($zipFile);

			/* Delete temporary unzip directory from dump */
			$this->load->helper('file');
			delete_files($unzipPath, TRUE); // true --> delete inner files and directories
			rmdir($unzipPath);      // remove directory
		} else {
			$output = "<p>File could not be uploaded, check zip filetype and maximum upload size</p>";
		}


//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);

		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	//------------------- Gallery Settings controllers --------------------------

	public function settings() {

		$title = 'Gallery Settings';
		$viewFile = 'gallery/settings';

		// default values of form
		$this->parseData['default-message'] = "Edit gallery settings";
		$this->parseData['thumbnail_width'] = get_settings($this->module, 'thumbnail_width');
		$this->parseData['thumbnail_height'] = get_settings($this->module, 'thumbnail_height');

		// set use_theme checkbox
		$attributes = array(
		    'name' => 'use_theme',
		    'id' => 'use_theme',
		    'value' => true,
		    'checked' => (bool) get_settings($this->module, 'use_theme'),
		    'style' => ''
		);
		$this->parseData['gallery:checkbox:use_theme'] = form_checkbox($attributes);

		$themes = $this->gallery_model->get_theme_names();
		foreach ($themes as $theme) {
			$options[$theme] = $theme;
		}
		$selectedValue = get_settings($this->module, 'current_theme');
		$this->parseData['set:select:current_theme'] = form_dropdown('current_theme', $options, $selectedValue, "id='current_theme' class='form_select_fields'");

		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function settings_save() {

		$settings = array(
		    "thumbnail_width" => $this->input->post('thumbnail_width'),
		    "thumbnail_height" => $this->input->post('thumbnail_height'),
		    "use_theme" => (bool) $this->input->post('use_theme'),
		    "current_theme" => $this->input->post('current_theme')
		);


		$this->form_validation->set_rules('thumbnail_width', 'Thumbnail width', 'integer|required');
		$this->form_validation->set_rules('thumbnail_height', 'Thumbnail height', 'integer|required');
		$this->form_validation->set_rules('use_theme', 'Allow use_theme', '');
		$this->form_validation->set_rules('current_theme', 'Current theme', '');


		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			if ($this->site_settings->set_by_category($this->module, $settings)) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, "settings successfully saved");
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, "unable to save settings");
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Form fields have invalid values, settings not saved")
				->set_redirect(null)
				->add_validation_msg("thumbnail_width", form_error("thumbnail_width"))
				->add_validation_msg("thumbnail_height", form_error("thumbnail_height"))
				->add_validation_msg("use_theme", form_error("use_theme"))
				->add_validation_msg("current_theme", form_error("current_theme"));
		}

		$this->form_response->send();
	}

	//------------------- Gallery Manage controllers --------------------------

	public function manage() {

		$pageName = "Manage Gallery";

		$maxFilesizeInBytes = $this->config->item('image_max_size', $this->configFile) * 1024;

		$this->parseData['save_image_do'] = site_url("admin/gallery/upload_save_image");
		$this->parseData['dump_path'] = relative_root_path(dump_uri());    // generate path relative to docroot
		$this->parseData['allowed_file_extensions'] = '*.jpg;*.jpeg;*.gif;*.png;*.tif';
		$this->parseData['max_upload_size'] = $maxFilesizeInBytes;


		$output = $this->parser->parse('gallery/manage_categories/index.php', $this->parseData, true);

//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);

		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	//------------------- Gallery Miscellaneous controllers --------------------------

	public function get_json_array() {

		/**
		 * @todo If no images or categ in db, output null
		 */
		$output = null;
		$json = array(
		    'categories' => null,
		    'settings' => null
		);

//                $categories = $this->gallery_model->get_categories();
		$categories = $this->gallery_images_library->get_categories(null, false); // null=all categs, false = include invisible
		if (!is_null($categories)) {

			$categories = $this->_rearrange_array($categories, 'id');

			$categId = GALLERY_DEFAULT_CATEG_ID;
//                        $images = $this->gallery_model->get_images_by_categories( array( $categId ) );
			$images = $this->gallery_images_library->get_images_by_categories(array($categId), false);

			if (!is_null($images)) {

				$images = $this->_rearrange_array($images, 'id');
			} else {

				// no images found in default categ
			}

			/* run through each category, prepare json array */
			foreach ($categories as $id => $categ) {

				$imageData = ( $id == $categId ) ? $images : null;

				$json['categories'][$id] = array(
				    'meta' => $categ,
				    'images' => $imageData
				);
			}

			$json['settings']['default_categ'] = 1; // id of category
//                        $output = json_encode( $json );
			$output = $json;
		} else {
			// no categories found
			$output = null;
		}

//                echo $output;

		$this->json_response
			->set_message(WM_STATUS_SUCCESS)
			->set_data($json)
			->send();
	}

	public function create_category() {

		$output = null;

		$categName = $this->input->post('cat_name');
		$categNameUrl = $this->input->post('cat_name_url');
		$description = $this->input->post('cat_description');
		$visible = (bool) $this->input->post('cat_visible');

		$type = $this->config->item('items_type_category', $this->config_database);
		$order = $this->gallery_model->get_max_item_order($type);

		if (!is_null($order)) {

			$order += 1;

			$categDb = array(
			    //'gallery_item_id'             => null,
			    //'gallery_item_parent_id'      => null,
			    //'gallery_item_cover_id'       => null,
			    'gallery_item_type' => $type,
			    'gallery_item_name' => $categName,
			    'gallery_item_name_url' => $categNameUrl,
			    'gallery_item_desc' => $description,
			    //'gallery_item_alt'            => null,
			    //'gallery_item_uri'            => null,
			    //'gallery_item_uri_thumb'      => null,
			    'gallery_item_order' => $order,
			    'gallery_item_created' => get_gmt_time(),
			    'gallery_item_modified' => get_gmt_time(),
			    'gallery_item_is_visible' => $visible
			);


			$categId = $this->gallery_model->create_item($categDb);

			if ($categId > 0) {

				$output = "Success: category $categName was successfully created";
				$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
				$this->json_response->set_data(array('cat_id' => $categId));
			} else {
				$output = "Error: could not create category $categName";
				$this->json_response->set_message(WM_STATUS_ERROR, $output);
			}
		} else {

			$output = "Error: could not create category $categName";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}
//                echo $output;

		$this->json_response->send();
	}

	public function edit_category_details() {

		$output = null;

		$id = $this->input->post('cat_id');
		$coverId = $this->input->post('cat_cover_id');
		$categName = $this->input->post('cat_name');
		$categNameUrl = $this->input->post('cat_name_url');
		$description = $this->input->post('cat_description');
		$alt = $this->input->post('cat_alt');
		$visible = (bool) $this->input->post('cat_visible');


//                $type = $this->config->item( 'items_type_category', $this->config_database );

		$categDb = array(
		    //'gallery_item_id'             => null,
		    //'gallery_item_parent_id'      => null,
		    'gallery_item_cover_id' => ( $coverId == "" ) ? null : $coverId,
		    //'gallery_item_type'           => $type,
		    'gallery_item_name' => $categName,
		    'gallery_item_name_url' => $categNameUrl,
		    'gallery_item_desc' => $description,
		    'gallery_item_alt' => $alt,
		    //'gallery_item_uri'            => null,
		    //'gallery_item_uri_thumb'      => null,
		    //'gallery_item_order'          => null,
		    //'gallery_item_created'          => get_gmt_time(),
		    'gallery_item_modified' => get_gmt_time(),
		    'gallery_item_is_visible' => $visible
		);


		$success = $this->gallery_model->edit_item($id, $categDb);
		if ($success) {
			$output = "Success: category $categName was successfully updated";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Error: could not update category $categName";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;
		$this->json_response->send();
	}

	public function edit_image_details() {

		$output = null;

		$id = $this->input->post('img_id');
		$imageName = $this->input->post('img_name');
		$imageNameUrl = $this->input->post('img_name_url');
		$description = $this->input->post('img_description');
		$alt = $this->input->post('img_alt');
		$visible = (bool) $this->input->post('img_visible');

//                $type = $this->config->item( 'items_type_category', $this->config_database );

		$imageDb = array(
		    //'gallery_item_id'             => null,
		    //'gallery_item_parent_id'      => null,
		    //'gallery_item_cover_id'       => null,
		    //'gallery_item_type'           => $type,
		    'gallery_item_name' => $imageName,
		    'gallery_item_name_url' => $imageNameUrl,
		    'gallery_item_desc' => $description,
		    'gallery_item_alt' => $alt,
		    //'gallery_item_uri'            => null,
		    //'gallery_item_uri_thumb'      => null,
		    //'gallery_item_order'          => null,
		    //'gallery_item_created'          => get_gmt_time(),
		    'gallery_item_modified' => get_gmt_time(),
		    'gallery_item_is_visible' => $visible
		);


		$success = $this->gallery_model->edit_item($id, $imageDb);
		if ($success) {
			$output = "Success: image $imageName was successfully updated";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Error: could not update image $imageName";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;
		$this->json_response->send();
	}

	public function edit_category_cover() {

		$output = null;

		$categId = $this->input->post('cat_id');
		$imageId = $this->input->post('img_id');

		$categDb = array(
		    //'gallery_item_id'             => null,
		    //'gallery_item_parent_id'      => null,
		    'gallery_item_cover_id' => $imageId,
		    //'gallery_item_type'           => $type,
		    //'gallery_item_name'             => $categName,
		    //'gallery_item_name_url'         => $categNameUrl,
		    //'gallery_item_desc'             => $description,
		    //'gallery_item_alt'            => null,
		    //'gallery_item_uri'            => null,
		    //'gallery_item_uri_thumb'      => null,
		    //'gallery_item_order'          => null,
		    //'gallery_item_created'          => get_gmt_time(),
		    'gallery_item_modified' => get_gmt_time(),
			//'gallery_item_is_visible'       => $visible
		);

		$success = $this->gallery_model->edit_item($categId, $categDb);
		if ($success) {
			$output = "Success: cover image was successfully updated";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Error: could not update cover image";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;
		$this->json_response->send();
	}

	public function get_images_specific() {

		$imageIds = $this->input->post('item_ids');
//                $images = $this->gallery_model->get_images( $imageIds, false ); // $onlyVisible = false
		$images = $this->gallery_images_library->get_images($imageIds, false); // $onlyVisible = false
//                echo json_encode( $images );
		$this->json_response
			->set_message(WM_STATUS_SUCCESS)
			->set_data($images)
			->send();
	}

	public function get_categories() {

		$categId = null;
		/* get all categories data ( --> no parameter ) */
//                $categories = $this->gallery_model->get_categories( $categId, false ); // $onlyVisible = false
		$categories = $this->gallery_images_library->get_categories($categId, false); // $onlyVisible = false
		//
//                echo json_encode( $categories );

		$this->json_response
			->set_message(WM_STATUS_SUCCESS)
			->set_data($categories)
			->send();
	}

	/**
	 * Moves a list of images from old category to new category,
	 * Also if any of the moved images in the 'oldCategories' display image,
	 * it sets the display image for the old category as null
	 *
	 */
	public function change_category() {

		$output = null;
		$success = false;

		$oldCategId = $this->input->post('old_cat_id');
		$newCategId = $this->input->post('new_cat_id');
		$imageIds = $this->input->post('img_ids');

		$success = $this->gallery_model->images_change_category($oldCategId, $newCategId, $imageIds);

		if ($success) {
			$output = "Success: image(s) successfully moved";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
			//$success= true;
		} else {
			$output = "Error: could not move image(s)";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
//                        $success= false;
		}

//                $output = array( 'message'=>$output, 'status'=> $success );
//                $output = json_encode($output);
//                echo $output;

		$this->json_response->send();
	}

	public function change_order() {

		$oldId = $this->input->post('old_id');
		$newId = $this->input->post('new_id');

		$success = $this->gallery_model->change_position($oldId, $newId);

		if ($success) {

			$output = "Reordering successfull";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Reordering failed";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	private function _rearrange_array($array, $key) {

		$return = null;

		/* run through array */
		foreach ($array as $element) {

			$arrangeBy = isset($element[$key]) ? $element[$key] : null;

			/* check if key is present in provided array, if not terminate */
			if (!is_null($arrangeBy)) {

				$return[$arrangeBy] = $element;
			} else {
				$return = null;
				break;
			}
		}

		return $return;
	}

	public function upload_save_image() {

		$categId = $this->input->post('cat_id');
		$filearray = $this->input->post('filearray');
		$fileDetails = json_decode($filearray, true);

		$this->load->library('gallery/gallery_images_library');
		//$this->load->helper('date');
//                                $this->load->library( 'gallery/gallery_model' );
		$db = & $this->gallery_model->get_db();

//                $this->gallery_model->transaction_strict( true );
//                $this->gallery_model->transaction_begin();
//                $categId = $this->gallery_model->get_categ_id();        // returns default id if categ not specified


		$fileName = $fileDetails['file_name'];
		$fileRealName = $fileDetails['real_name'];
		$fileExt = $fileDetails['file_ext'];
		$fileSize = $fileDetails['file_size'];
		$filePath = $fileDetails['file_path'];
		$fileTempPath = $fileDetails['file_temp'];

		$fileLocations = array($filePath);

		$success = $this->gallery_images_library->import_images($fileLocations, $categId, $db);
		$images = null;

		if ($success) {

			$imgIds = array();
			$imgIds = $this->gallery_images_library->get_insertedImageIds();

			$images = $this->gallery_images_library->get_images($imgIds, false); // $onlyVisible = false

			$this->load->helper('array');
			$images = rearrange_array($images, 'id');

			$output = "<p>Images Successfully Uploaded</p>";
			$this->json_response
				->set_message(WM_STATUS_SUCCESS, $output)
				->set_data($images);

//                        $this->gallery_model->transaction_commit();
		} else {

//                        $this->gallery_model->transaction_rollback();
			$output = "<p>Error occured while uploading images</p>";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		// remove from temporary dump folder
		foreach ($fileLocations as $file) {

			if (file_exists($file))
				unlink($file);
		}

		$this->json_response->send();
	}

	/*	 * ********** FRONT CONTROLLERS **************** */

	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	/////////////////// NEW MANAGE Controller ///////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////

	public function manage_new() {

		$pageName = "Manage Gallery";

//		$maxFilesizeInBytes = $this->config->item('image_max_size', $this->configFile) * 1024;
//		$this->parseData['save_image_do'] = site_url("admin/gallery/upload_save_image");
//		$this->parseData['dump_path'] = relative_root_path(dump_uri());    // generate path relative to docroot
//		$this->parseData['allowed_file_extensions'] = '*.jpg;*.jpeg;*.gif;*.png;*.tif';
//		$this->parseData['max_upload_size'] = $maxFilesizeInBytes;


		$categories = $this->gallery_images_library->get_categories(null, false, true); // null=all categs, false = include invisible, true = show hidden Cover img
		if (!is_null($categories)) {

			$this->parseData['categs'] = $categories;

			// get images in default Category
			$images = $this->gallery_images_library->get_images_by_categories(array(GALLERY_DEFAULT_CATEG_ID), false);

			if (!is_null($images)) {

				$this->parseData['categs'] = $categories;
				$this->parseData['images'] = $images;
			} else {

				// no images found in default categ
			}
		} else {
			// no categories found
			$output = null;
		}

//		echo $output = $this->load->view('gallery/new/manage_new', $this->parseData, true);
		echo $output = $this->parser->parse('gallery/new/manage_new', $this->parseData, true);
//
//                $this->load->library('admin/admin_views');
//                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
//		echo get_admin_main_content($this->parseData, $pageName, $output);
	}

	/** gets images for given category id */
	public function get_category_data() {

		$categId = $this->input->post('cat_id');
//                $categId = 1;
//                $categories = $this->gallery_model->get_categories( array( $categId ), false ); // $onlyVisible = false
		$categories = $this->gallery_images_library->get_categories(array($categId), false, true); // $onlyVisible = false
		$images = $this->gallery_images_library->get_images_by_categories(array($categId), false); // $onlyVisible = false
		$json = array(
		    'meta' => $categories[0],
		    //'images' => $this->gallery_model->get_images_by_categories( array( $categId ) )
		    'images' => !is_null($images) ? $this->_rearrange_array($images, 'id') : null
		);

//                echo json_encode( $json );
////                $this->load->library('json_response');
		$this->json_response
			->set_message(WM_STATUS_SUCCESS)
			->set_data($json)
			->send();
	}

	/**
	 * Get meta data for particular image
	 */
	public function get_image_data() {

		$imageIds = $this->input->post('img_id');
		$images = $this->gallery_images_library->get_images($imageIds, false); // $onlyVisible = false

		$imageData = null;
		if (isset($images[0])) {
			$imageData = $images[0];
		}

		$this->json_response
			->set_message(WM_STATUS_SUCCESS)
			->set_data($imageData)
			->send();
	}

	public function edit_visibility() {

		$output = null;

		$visible = (boolean) $this->input->post('visible');
		$ids = $this->input->post('item_ids');
//                $ids = is_array( $ids ) ? $ids : array( $ids );

		if ($this->gallery_model->edit_item_visibility($ids, $visible)) {

			$output = "Changes successfully saved";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Changes not saved";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                return $output;

		$this->json_response->send();
	}

	public function delete_items() {

		/**
		 * @todo let user decide
		 *  if images for a category should be moved to uncategorized
		 *  or to be deleted permanently
		 *
		 * accordingly perform actual image deletions if needed
		 */
		$ids = $this->input->post('item_ids');
		$type = $this->input->post('item_type');
		//$ids = is_array( $ids ) ? $ids : array( $ids );

		$output = null;

		// delete files physically from file system
		$success = $this->gallery_images_library->delete_images_physically($ids, $type);

		// delete from database only if actual file deletion successful
		if ($success) {

			$success = $this->gallery_model->delete_items($ids);
			if ($success) {
				$success = true;
				$count = count($ids);
				$output = "Success: $count item(s) successfully deleted";
				$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
			} else {
				$success = false;
				$output = "Error: could not delete item(s)";
				$this->json_response->set_message(WM_STATUS_ERROR, $output);
			}
		} else {

			$success = false;
			$output = "Error: could not delete item(s)";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;

		$this->json_response->send();
	}

	public function reorder_categs() {

		$oldPos = (int) $this->input->post('old_pos');
		$newPos = (int) $this->input->post('new_pos');

		if ($this->gallery_model->reorder_categs($oldPos, $newPos)) {
			$this->json_response->set_message(WM_STATUS_SUCCESS, "Categories successfully reordered");
		} else {
			$this->json_response->set_message(WM_STATUS_ERROR, "Error occured while reordering categories");
		}

		$this->json_response->send();
	}
	public function reorder_images() {
		$categId = (int) $this->input->post('categ_id');
		$oldPos = (int) $this->input->post('old_pos');
		$newPos = (int) $this->input->post('new_pos');

		if ($this->gallery_model->reorder_images($categId, $oldPos, $newPos)) {
			$this->json_response->set_message(WM_STATUS_SUCCESS, "Images successfully reordered");
		} else {
			$this->json_response->set_message(WM_STATUS_ERROR, "Error occured while reordering images");
		}

		$this->json_response->send();
	}

}

?>
