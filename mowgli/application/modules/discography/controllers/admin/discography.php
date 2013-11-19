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
class discography extends Admin_Controller implements I_Admin_Extract {

	private $module = "discography";

	public function __construct() {

		parent::__construct();

//                $this->config->load( $this->configFile, true, true, $this->module );
		// define constants
		!defined('DISCO_URI_CATEG_ADD') ? define('DISCO_URI_CATEG_ADD', "admin/" . $this->module . "/add_categ") : null;
		!defined('DISCO_URI_CATEG_EDIT') ? define('DISCO_URI_CATEG_EDIT', "admin/" . $this->module . "/edit_categ") : null;
		!defined('DISCO_URI_CATEG_SAVE') ? define('DISCO_URI_CATEG_SAVE', "admin/" . $this->module . "/save_categ") : null;
		!defined('DISCO_URI_CATEG_DELETE') ? define('DISCO_URI_CATEG_DELETE', "admin/" . $this->module . "/delete_categs") : null;

		!defined('DISCO_URI_ITEM_ADD') ? define('DISCO_URI_ITEM_ADD', "admin/" . $this->module . "/add_item") : null;
		!defined('DISCO_URI_ITEM_EDIT') ? define('DISCO_URI_ITEM_EDIT', "admin/" . $this->module . "/edit_item") : null;
		!defined('DISCO_URI_ITEM_SAVE') ? define('DISCO_URI_ITEM_SAVE', "admin/" . $this->module . "/save_item") : null;
		!defined('DISCO_URI_ITEM_DELETE') ? define('DISCO_URI_ITEM_DELETE', "admin/" . $this->module . "/delete_items") : null;
		!defined('DISCO_URI_ITEM_MANAGE') ? define('DISCO_URI_ITEM_MANAGE', "admin/" . $this->module . "/manage_items") : null;

		!defined('DISCO_ACTION_ADD') ? define('DISCO_ACTION_ADD', "add") : null;
		!defined('DISCO_ACTION_EDIT') ? define('DISCO_ACTION_EDIT', "edit") : null;

		!defined('DISCO_DEFAULT_TEMPLATE') ? define('DISCO_DEFAULT_TEMPLATE', "default") : null;

		// load libraries
		$this->load->library('templates');
		$this->load->library('discography/discography_render');
		$this->load->library('json_response');
		$this->load->library('form_response');
		$this->load->library('form_validation');
		// $this->form_validation->CI = & $this;;	   // required for form validation to work with hmvc
		// Load models
		$this->load->model($this->module . '/discography_model');

		$this->parseData['module:resource'] = site_url(module_resource_uri($this->module));

		$this->parseData['default-message'] = "";
	}

	public function _extract_content($tempId, $parseTag, $innerText, & $db) {

		$tagParts = explode(':', $parseTag);
		$template = isset($tagParts[1]) ? $tagParts[1] : DISCO_DEFAULT_TEMPLATE;

		// add template, if template exists --> do not add new one, simply get tempId
		$this->templates->set_db($db);
		$tempId = $this->templates->add_module_template($this->module, $template, $innerText);

		$tag = array(
		    'tag_id' => null,
		    'tag_module_name' => $this->module,
		    'tag_temp_id' => $tempId, //$tempId,
		    'tag_keyword' => $parseTag,
		    'tag_name' => $parseTag,
		    'tag_data_id' => null,
			//    'tag_description' => null
		);

		$this->load->model('tags_model');
		$this->tags_model->set_db($db);
		$tagId = $this->tags_model->create_tag($tag);

		return $tagId > 0 ? $tagId : null;
	}

	/*	 * ******************** Categs( Album ) functions *********************************************** */

	public function add_categ() {

		$pageName = "Add Album";

		// set form metadata parse values
		$this->parseData['discography:post_url'] = site_url(DISCO_URI_CATEG_SAVE);
		$this->parseData['discography:action_type'] = DISCO_ACTION_ADD;
		$this->parseData['discography:categ:id'] = 'null';

		// initialize form values
		$parseTags = $this->discography_render->prepare_categ_tags(null); // set initial values to ''
		$this->parseData = array_merge($this->parseData, $parseTags);
		$this->parseData['discography:checkbox:categ:is_visible'] = form_checkbox('is_visible', 1, true);

		$output = $this->parser->parse('discography/categ_edit.php', $this->parseData, true);

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function save_categ() {

		$actionType = $this->input->post('action_type');
		$categId = $this->input->post('categ_id');

		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash');
		$this->form_validation->set_rules('buy_url', 'Buy Url', '');
		$this->form_validation->set_rules('download_url', 'Download Url', '');
		$this->form_validation->set_rules('image_url', 'Image Url', '');
		$this->form_validation->set_rules('is_visible', 'Visible', '');
		$this->form_validation->set_rules('description', 'Description', '');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$discographyDb = array(
			    //"discography_categ_id" => null,
			    "discography_categ_name" => $this->input->post('name'),
			    "discography_categ_slug" => $this->input->post('slug'),
			    "discography_categ_buy_url" => $this->input->post('buy_url'),
			    "discography_categ_download_url" => $this->input->post('download_url'),
			    "discography_categ_image_url" => $this->input->post('image_url'),
			    "discography_categ_description" => $this->input->post('description'),
			    "discography_categ_is_visible" => $this->input->post('is_visible'),
			    "discography_categ_created" => get_gmt_time(),
			    "discography_categ_order" => $this->discography_model->get_categ_max_order() + 1,
			);

			$success = false;

			switch ($actionType) {
				case DISCO_ACTION_ADD:

					$success = $this->discography_model->create_categ($discographyDb);
					break;

				case DISCO_ACTION_EDIT:

					// remove categ order if save is needed.
					unset($discographyDb['discography_categ_order']);
					$success = $this->discography_model->edit_categ($categId, $discographyDb);
					break;

				default:
					$success = false;
					break;
			}

			if ($success) {

//                                echo "success";
				$this->form_response->set_message(WM_STATUS_SUCCESS, "Album saved");
			} else {

//                                echo "fail";
				$this->form_response->set_message(WM_STATUS_ERROR, "Unable to save Album");
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("name", form_error("name"))
				->add_validation_msg("slug", form_error("slug"))
				->add_validation_msg("buy_url", form_error("buy_url"))
				->add_validation_msg("download_url", form_error("download_url"))
				->add_validation_msg("image_url", form_error("image_url"))
				->add_validation_msg("is_visible", form_error("is_visible"))
				->add_validation_msg("description", form_error("description"));

//                        echo "invalid form fields";
		}

		$this->form_response->send();
	}

	public function edit_categ($categId = null) {

		$pageName = "Edit Album";
		$output = null;

		$categId = isset($_POST['categ_id']) ? $this->input->post('categ_id') : $categId;

		$this->parseData['discography:post_url'] = site_url(DISCO_URI_CATEG_SAVE);
		$this->parseData['discography:action_type'] = DISCO_ACTION_EDIT;
		$this->parseData['discography:id'] = $categId;

		$category = $this->discography_model->get_categ($categId, false); // $onlyVisible = false

		if (!is_null($category)) {

			// initialize form values
			$parseTags = $this->discography_render->prepare_categ_tags($category);
			$this->parseData = array_merge($this->parseData, $parseTags);
			$this->parseData['discography:checkbox:categ:is_visible'] = form_checkbox('is_visible', 1, (bool) $category['is_visible']);

			$output = $this->parser->parse('discography/categ_edit.php', $this->parseData, true);
		} else {
			// discography NOT available in database OR error
//                        $this->parseData['error_message'] = "Album unavailable";
//                        $output = $this->parser->parse('discography/errors.php', $this->parseData, true );

			$message = "Album unavailable";
			$output = $this->_get_error_view($message);
		}

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function manage_categs() {

		$pageName = "Manage Albums";

		$output = null;
		$categs = $this->discography_model->get_categs(null, false); // $onlyVisible = false ( by default )

		if (!is_null($categs)) {

			$categTags = $this->discography_render->prepare_categ_tags($categs);

			foreach ($categTags as $key => $categ) {

				$extraCategTags = array(
				    'discography:categ:edit_link' => site_url(DISCO_URI_CATEG_EDIT . "/" . $categ['discography:categ:id']),
				    'discography:categ:delete_link' => site_url(DISCO_URI_CATEG_DELETE . "/" . $categ['discography:categ:id'])
				);

				$categTags[$key] = array_merge($categ, $extraCategTags);
			}

			$this->parseData['discography:categs'] = $categTags;
			$output = $this->parser->parse('discography/categ_manage.php', $this->parseData, true);
		} else {
//                        $this->parseData['error_message'] = "No Albums added";
//                        $output = $this->parser->parse('discography/errors.php', $this->parseData, true );

			$message = "No Albums added";
			$output = $this->_get_error_view($message);
		}

//                $this->parseData['categs'] = $parsecategs;


		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function delete_categs($categId = null) {

//                $pageName = "Delete categ";
		// if post value set, use post value, else use parameter value
		$categIds = isset($_POST['categ_ids']) ? $this->input->post('categ_ids') : $categId;
//                $categIds = is_array( $categIds ) ? $categIds : array( $categIds );
		// delete categ, check if successfull
		if ($this->discography_model->delete_categs($categIds)) {

//                        echo "success";
			$this->json_response->set_message(WM_STATUS_SUCCESS, "Album(s) deleted");
		} else {
			// categ NOT available in database OR error
//                        echo "error";
			$this->json_response->set_message(WM_STATUS_ERROR, "Album(s) could not be deleted");
		}

		$this->json_response->send();
//                $this->load->library('admin/admin_views' );
//                echo $this->admin_views->get_main_content( $this->parseData, $pageName, $output );
	}

	public function reorder_categs($oldId = null, $newId = null) {

		$output = null;

		$oldId = isset($_POST['old_id']) ? $this->input->post('old_id') : $oldId;
		$newId = isset($_POST['new_id']) ? $this->input->post('new_id') : $newId;

		$success = $this->discography_model->reorder_categs($oldId, $newId);

		if ($success) {

			$output = "Reordering successfull";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Reordering failed";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;
		$this->json_response->send();
	}

	/*	 * ******************** Item( song ) functions *********************************************** */

	public function add_item() {

		$pageName = "Add Song";

		$categList = $this->discography_model->get_categ_list();

		$output = null;

		if (!is_null($categList)) {

			// set form metadata parse values
			$this->parseData['discography:post_url'] = site_url(DISCO_URI_ITEM_SAVE);
			$this->parseData['discography:action_type'] = DISCO_ACTION_ADD;
			$this->parseData['discography:item:id'] = 'null';

			// initialize form values
			$parseTags = $this->discography_render->prepare_item_tags(null); // set initial values to ''
			$this->parseData = array_merge($this->parseData, $parseTags);
			$this->parseData['discography:checkbox:item:is_visible'] = form_checkbox('is_visible', 1, true);

			// create drop down list for selecting Albums
			$categList = $this->discography_model->get_categ_list();

			$attribs = " id='parent_id' ";
			$attribs .= " class='form_select_fields' ";
			$this->parseData['discography:select:categ:id'] = form_dropdown('parent_id', $categList, array(), $attribs);

			$output = $this->parser->parse('discography/item_edit.php', $this->parseData, true);
		} else {
//                        $this->parseData['error_message'] = "No Albums Added";
//                        $output = $this->parser->parse('discography/errors.php', $this->parseData, true );

			$message = "No Albums added";
			$output = $this->_get_error_view($message);
		}



		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function save_item() {

		$actionType = $this->input->post('action_type');
		$itemId = $this->input->post('item_id');

		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('slug', 'Slug', 'required|alpha_dash');
		$this->form_validation->set_rules('description', 'Description', '');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$categId = $this->input->post('parent_id');

			$itemDb = array(
			    //"discography_item_id" => null,
			    "discography_item_parent_id" => $categId,
			    "discography_item_name" => $this->input->post('name'),
			    "discography_item_slug" => $this->input->post('slug'),
			    "discography_item_description" => $this->input->post('description'),
			    "discography_item_is_visible" => $this->input->post('is_visible'),
			    "discography_item_order" => $this->discography_model->get_item_max_order($categId) + 1,
			);

			$success = false;

			switch ($actionType) {
				case DISCO_ACTION_ADD:

					$success = $this->discography_model->create_item($itemDb);
					break;

				case DISCO_ACTION_EDIT:

					unset($itemDb['discography_item_order']);
					$success = $this->discography_model->edit_item($itemId, $itemDb);
					break;

				default:
					$success = false;
					break;
			}

			if ($success) {

//                                echo "success";
				$this->form_response->set_message(WM_STATUS_SUCCESS, "Song saved");
			} else {

//                                echo "fail";
				$this->form_response->set_message(WM_STATUS_ERROR, "Unable to save Song");
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("name", form_error("name"))
				->add_validation_msg("slug", form_error("slug"))
				->add_validation_msg("description", form_error("description"));

//                        echo "invalid form fields";
		}

//                echo "<br/>Done";
		$this->form_response->send();
	}

	public function edit_item($itemId = null) {

		$pageName = "Edit Song";
		$output = null;

		$itemId = isset($_POST['item_id']) ? $this->input->post('item_id') : $itemId;

		$this->parseData['discography:post_url'] = site_url(DISCO_URI_ITEM_SAVE);
		$this->parseData['discography:action_type'] = DISCO_ACTION_EDIT;
		$this->parseData['discography:item:id'] = $itemId;

		$item = $this->discography_model->get_item($itemId, false); // $onlyVisible = false

		if (!is_null($item)) {

			// initialize form values
			$parseTags = $this->discography_render->prepare_item_tags($item);
			$this->parseData = array_merge($this->parseData, $parseTags);
			$this->parseData['discography:checkbox:item:is_visible'] = form_checkbox('is_visible', 1, (bool) $item['is_visible']);

			// create drop down list for selecting Albums
			$categList = $this->discography_model->get_categ_list();

			$attribs = " id='parent_id' ";
			$attribs .= " class='form_select_fields' ";
			$this->parseData['discography:select:categ:id'] = form_dropdown('parent_id', $categList, $item['parent_id'], $attribs);

			$output = $this->parser->parse('discography/item_edit.php', $this->parseData, true);
		} else {
			// discography NOT available in database OR error
//                        $this->parseData['error_message'] = "Song unavailable";
//                        $output = $this->parser->parse('discography/errors.php', $this->parseData, true );

			$message = "Song unavailable";
			$output = $this->_get_error_view($message);
		}

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function manage_items($categId = null) {

		$pageName = "Manage Songs";
		$output = null;

		$categId = isset($_POST['categ_id']) ? $this->input->post('categ_id') : $categId;

		// if null --> select first view as default
		$categId = is_null($categId) ? $this->discography_model->get_first_categ_id() : $categId;

		if (!is_null($categId)) {

			$this->parseData['discography:post_url'] = site_url(DISCO_URI_ITEM_MANAGE);
			// create drop down list for selecting Albums
			$categList = $this->discography_model->get_categ_list();

			$attribs = " id='categ_id' ";
			$attribs .= " class='form_select_fields' ";
			$this->parseData['discography:select:categ:id'] = form_dropdown('categ_id', $categList, $categId, $attribs);

			$items = $this->discography_model->get_items($categId, null, false, null); // $onlyVisible = false ( by default )

			if (!is_null($items)) {

				$itemTags = $this->discography_render->prepare_item_tags($items);

				foreach ($itemTags as $key => $item) {

					$extraItemTags = array(
					    'discography:item:edit_link' => site_url(DISCO_URI_ITEM_EDIT . "/" . $item['discography:item:id']),
					    'discography:item:delete_link' => site_url(DISCO_URI_ITEM_DELETE . "/" . $item['discography:item:id'])
					);

					$itemTags[$key] = array_merge($item, $extraItemTags);
				}

				$this->parseData['temp_hide'] = '';

//                                $this->parseData['discography:post_url'] = site_url( DISCO_URI_ITEM_MANAGE );
				$this->parseData['discography:items'] = $itemTags;

//                                // create drop down list for selecting Albums
//                                $categList = $this->discography_model->get_categ_list();
//                                $this->parseData['discography:select:categ:id'] = form_dropdown( 'categ_id', $categList, $categId );

				$this->parseData['discography:item:list'] = $this->parser->parse('discography/item_manage_list.php', $this->parseData, true);

				$output = $this->parser->parse('discography/item_manage.php', $this->parseData, true);
			} else {
				// no songs created for given album
//                                $this->parseData['discography:post_url'] = site_url( DISCO_URI_ITEM_MANAGE );
				// create drop down list for selecting Albums
//                                $categList = $this->discography_model->get_categ_list();
//                                $this->parseData['discography:select:categ:id'] = form_dropdown( 'categ_id', $categList, $categId );
//                                $this->parseData['discography:item:list'] = "<p>No songs created in current album</p>";
//                                $this->parseData['error_message'] = "No Albums Added";
//                                $this->parseData['discography:item:list'] = $this->parser->parse('discography/errors.php', $this->parseData, true );

				$message = "No Albums Added";
//                                $this->parseData['discography:item:list'] = $this->_get_error_view( $message );
				// temporary fix
				$itemTags = $this->discography_render->prepare_item_tags(null);
				$this->parseData['temp_hide'] = 'hide';
				$this->parseData['discography:items'] = array($itemTags);
				$this->parseData['discography:item:list'] = $this->parser->parse('discography/item_manage_list.php', $this->parseData, true);

				$output = $this->parser->parse('discography/item_manage.php', $this->parseData, true);
			}
		} else {

			// Error, no albums created
//                        $this->parseData['error_message'] = "No Albums added";
//                        $output = $this->parser->parse('discography/errors.php', $this->parseData, true );

			$message = "No Albums Added";
			$output = $this->_get_error_view($message);
		}
//                $this->parseData['items'] = $parseitems;


		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function delete_items($itemId = null) {

//                $pageName = "Delete item";
		// if post value set, use post value, else use parameter value
		$itemIds = isset($_POST['item_ids']) ? $this->input->post('item_ids') : $itemId;
//                $itemIds = is_array( $itemIds ) ? $itemIds : array( $itemIds );
		// delete item, check if successfull
		if ($this->discography_model->delete_items($itemIds)) {

//                        echo "success";
			$this->json_response->set_message(WM_STATUS_SUCCESS, "Song(s) deleted");
		} else {
			// item NOT available in database OR error
//                        echo "error";
			$this->json_response->set_message(WM_STATUS_ERROR, "Song(s) could not be deleted");
		}

		$this->json_response->send();
//                $this->load->library('admin/admin_views' );
//                echo $this->admin_views->get_main_content( $this->parseData, $pageName, $output );
	}

	public function reorder_items($oldId = null, $newId = null) {

		$output = null;

		$oldId = isset($_POST['old_id']) ? $this->input->post('old_id') : $oldId;
		$newId = isset($_POST['new_id']) ? $this->input->post('new_id') : $newId;

		$success = $this->discography_model->reorder_items($oldId, $newId);

		if ($success) {

			$output = "Reordering successfull";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$output = "Reordering failed";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

//                echo $output;
		$this->json_response->send();
	}

	/*	 * ******************** General functions *********************************************** */

	private function _get_error_view($message) {

		$this->parseData['error_message'] = $message;
		$output = $this->parser->parse('discography/errors.php', $this->parseData, true);

		return $output;
	}

}

?>
