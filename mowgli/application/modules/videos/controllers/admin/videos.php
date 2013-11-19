<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of videos
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class videos extends Admin_Controller implements I_Admin_Extract {

	private $module = "videos";

	public function __construct() {

		parent::__construct();

//                $this->config->load( $this->configFile, true, true, $this->module );
		// define constants
		!defined('VIDEOS_URI_ADD') ? define('VIDEOS_URI_ADD', "admin/" . $this->module . "/add") : null;
//                ! defined( 'VIDEOS_URI_ADD_SAVE' ) ? define( 'VIDEOS_URI_ADD_SAVE', "admin/" . $this->module . "/save" ) : null;
		!defined('VIDEOS_URI_EDIT') ? define('VIDEOS_URI_EDIT', "admin/" . $this->module . "/edit") : null;
		!defined('VIDEOS_URI_SAVE') ? define('VIDEOS_URI_SAVE', "admin/" . $this->module . "/save") : null;
		!defined('VIDEOS_URI_DELETE') ? define('VIDEOS_URI_DELETE', "admin/" . $this->module . "/delete") : null;

		!defined('VIDEOS_ACTION_ADD') ? define('VIDEOS_ACTION_ADD', "add") : null;
		!defined('VIDEOS_ACTION_EDIT') ? define('VIDEOS_ACTION_EDIT', "edit") : null;

		!defined('VIDEOS_DEFAULT_TEMPLATE') ? define('VIDEOS_DEFAULT_TEMPLATE', "default") : null;

		// load libraries
		$this->load->library('templates');
		$this->load->library('videos/videos_render');
		$this->load->library('json_response');
		$this->load->library('form_response');
		$this->load->library('form_validation');
		// $this->form_validation->CI = & $this;;	   // required for form validation to work with hmvc
		// Load models
		$this->load->model('videos/videos_model');

		$this->parseData['module:resource'] = site_url(module_resource_uri($this->module));

		$this->parseData['default-message'] = "";
	}

	public function _extract_content($tempId, $parseTag, $innerText, & $db) {

		$tagParts = explode(':', $parseTag);
		$template = isset($tagParts[1]) ? $tagParts[1] : VIDEOS_DEFAULT_TEMPLATE;

		$this->templates->set_db($db);

		// add template, if template exists --> do not add new one, simply get tempId
		$tempId = $this->templates->add_module_template($this->module, $template, $innerText);

		$tag = array(
		    'tag_id' => null,
		    'tag_module_name' => $this->module,
		    'tag_temp_id' => $tempId,
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

	public function manage() {

		$pageName = "Manage Videos";

		$output = null;
		$videos = $this->videos_model->get_videos(false); // $onlyVisible = false ( by default )

		if (!is_null($videos)) {

			foreach ($videos as $video) {

				$this->parseData['videos'][] = array(
				    'video:edit_link' => site_url(VIDEOS_URI_EDIT . "/" . $video['id']),
				    'video:delete_link' => site_url(VIDEOS_URI_DELETE . "/" . $video['id']),
				    'video:id' => $video['id'],
				    'video:ref_id' => $video['ref_id'],
				    'video:title' => $video['title'],
				    'video:description' => $video['description'],
				    'video:image_url' => $video['image_url'],
				    'video:script' => htmlentities($video['script']),
				    'video:order' => $video['order'],
				    'video:is_visible' => $video['is_visible']
				);
			}

			$output = $this->parser->parse('videos/manage.php', $this->parseData, true);
		} else {
			$output = "<p>No videos added</p>";
		}

//                $this->parseData['videos'] = $parseVideos;


		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function add() {

		$pageName = "Add Video";

		// set form metadata parse values
		$this->parseData['video:post_url'] = site_url(VIDEOS_URI_SAVE);
		$this->parseData['video:action_type'] = VIDEOS_ACTION_ADD;
		$this->parseData['video:id'] = 'null';

		// initialize form values
		$parseTags = $this->videos_render->prepare_tags(null); // set initial values to ''
		$this->parseData = array_merge($this->parseData, $parseTags);
		$this->parseData['video:checkbox:is_visible'] = form_checkbox('is_visible', 1, true);
//                $this->parseData['video:id'] = $video['id'];
//                $this->parseData['video:ref_id'] = $video['ref_id'];
//                $this->parseData['video:title'] = $video['title'];
//                $this->parseData['video:description'] = $video['description'];
//                $this->parseData['video:image_url'] = $video['image_url'];
//                $this->parseData['video:script'] = $video['script'];
//                $this->parseData['video:order'] = $video['order'];
//                $this->parseData['video:is_visible'] = $video['is_visible'];

		$output = $this->parser->parse('videos/edit.php', $this->parseData, true);

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function edit($videoId = null) {

		$pageName = "Edit Video";
		$output = null;

		$videoId = isset($_POST['video_id']) ? $this->input->post('video_id') : $videoId;

		$this->parseData['video:post_url'] = site_url(VIDEOS_URI_SAVE);
		$this->parseData['video:action_type'] = VIDEOS_ACTION_EDIT;
		$this->parseData['video:id'] = $videoId;

		$video = $this->videos_model->get_video($videoId);

		if (!is_null($video)) {

			// initialize form values
			$parseTags = $this->videos_render->prepare_tags($video);
			$this->parseData = array_merge($this->parseData, $parseTags);
			$this->parseData['video:checkbox:is_visible'] = form_checkbox('is_visible', 1, (bool) $video['is_visible']);

			$output = $this->parser->parse('videos/edit.php', $this->parseData, true);
		} else {
			// video NOT available in database OR error
			$output = "<p>Video unavailable</p>";
		}

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
	}

	public function save() {

		$actionType = $this->input->post('action_type');
		$videoId = $this->input->post('video_id');

		$this->form_validation->set_rules('ref_id', 'Ref Id', 'required');
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('description', 'Description', '');
		$this->form_validation->set_rules('image_url', 'Image Url', '');
		$this->form_validation->set_rules('script', 'Script', '');
//                $this->form_validation->set_rules( 'order', 'Order', 'numeric' );
		$this->form_validation->set_rules('is_visible', 'is_visible', '');


		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$videoDb = array(
			    //"video_id" => null,
			    "video_ref_id" => $this->input->post('ref_id'),
			    "video_title" => $this->input->post('title'),
			    "video_description" => $this->input->post('description'),
			    "video_image_url" => trim($this->input->post('image_url'), '/'),
			    "video_script" => $this->input->post('script'),
			    "video_order" => $this->videos_model->get_max_order() + 1, // note if edit --> will be removed from array later
			    "video_is_visible" => $this->input->post('is_visible')
			);

			$success = false;

			switch ($actionType) {
				case VIDEOS_ACTION_ADD:

					$success = $this->videos_model->create_video($videoDb);
					break;

				case VIDEOS_ACTION_EDIT:

					unset($videoDb['video_order']);
					$success = $this->videos_model->edit_video($videoId, $videoDb);
					break;

				default:
					$success = false;
					break;
			}

			if ($success) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, "Video saved");
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, "Unable to save video");
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("ref_id", form_error("ref_id"))
				->add_validation_msg("title", form_error("title"))
				->add_validation_msg("description", form_error("description"))
				->add_validation_msg("image_url", form_error("image_url"))
				->add_validation_msg("script", form_error("script"))
//                                ->add_validation_msg( "order", form_error( "order" ) )
				->add_validation_msg("is_visible", form_error("is_visible"));
		}

		$this->form_response->send();
	}

	public function reorder_videos($oldId = null, $newId = null) {

		$output = null;

		$oldId = isset($_POST['old_id']) ? $this->input->post('old_id') : $oldId;
		$newId = isset($_POST['new_id']) ? $this->input->post('new_id') : $newId;

		$success = $this->videos_model->reorder_categs($oldId, $newId);

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

	public function delete($videoId = null) {

//                $pageName = "Delete Video";
		// if post value set, use post value, else use parameter value
		$videoIds = isset($_POST['video_ids']) ? $this->input->post('video_ids') : $videoId;
		$videoIds = is_array($videoIds) ? $videoIds : array($videoIds);

		// delete video, check if successfull
		if ($this->videos_model->delete_videos($videoIds)) {

//                        echo "success";
			$this->json_response->set_message(WM_STATUS_SUCCESS, "Video(s) deleted");
		} else {
			// video NOT available in database OR error
//                        echo "error";
			$this->json_response->set_message(WM_STATUS_ERROR, "Video(s) could not be deleted");
		}

		$this->json_response->send();
//                $this->load->library('admin/admin_views' );
//                echo $this->admin_views->get_main_content( $this->parseData, $pageName, $output );
	}

}

?>
