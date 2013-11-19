<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of blog
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class blog extends Admin_Controller implements I_Admin_Extract {

	private $module = "blog";
	private $configFile = 'blog_config';

//        private $config_database = "blog_config_database";

	public function __construct() {

		parent::__construct();

		// load config files
		$this->config->load($this->configFile, true, true, $this->module);

		// Define Constants
		!defined('BLOG_ACTION_ADD') ? define('BLOG_ACTION_ADD', "add") : null;
		!defined('BLOG_ACTION_EDIT') ? define('BLOG_ACTION_EDIT', "edit") : null;
		!defined('BLOG_DEFAULT_CATEG_SLUG') ? define('BLOG_DEFAULT_CATEG_SLUG', $this->config->item('category_uncategorized', $this->configFile)) : null; // uncategorized
		// view keyword constants
		!defined('BLOG_VIEW_SUMMARY') ? define('BLOG_VIEW_SUMMARY', $this->config->item('view_summary', $this->configFile)) : null;
		!defined('BLOG_VIEW_CATEGORY_POSTS') ? define('BLOG_VIEW_CATEGORY_POSTS', $this->config->item('view_category_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_TAG_POSTS') ? define('BLOG_VIEW_TAG_POSTS', $this->config->item('view_tag_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_AUTHOR_POSTS') ? define('BLOG_VIEW_AUTHOR_POSTS', $this->config->item('view_author_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_DATE_POSTS') ? define('BLOG_VIEW_DATE_POSTS', $this->config->item('view_date_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_SINGLE_POST') ? define('BLOG_VIEW_SINGLE_POST', $this->config->item('view_single_post', $this->configFile)) : null;

		!defined('BLOG_VIEW_FEATURED') ? define('BLOG_VIEW_FEATURED', $this->config->item('view_featured_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_LATEST') ? define('BLOG_VIEW_LATEST', $this->config->item('view_latest_posts', $this->configFile)) : null;
		!defined('BLOG_VIEW_ARCHIVES') ? define('BLOG_VIEW_ARCHIVES', $this->config->item('view_archives', $this->configFile)) : null;
		!defined('BLOG_VIEW_ARCHIVES_CATEGORIZED') ? define('BLOG_VIEW_ARCHIVES_CATEGORIZED', $this->config->item('view_archives_categorized', $this->configFile)) : null;

		!defined('BLOG_DEFAULT_CATEG_SLUG') ? define('BLOG_DEFAULT_CATEG_SLUG', $this->config->item('category_uncategorized', $this->configFile)) : null; // uncategorized
		!defined('BLOG_POST_STATUS_DRAFT') ? define('BLOG_POST_STATUS_DRAFT', $this->config->item("post_status_draft", $this->configFile)) : null;
		!defined('BLOG_POST_STATUS_PUBLISHED') ? define('BLOG_POST_STATUS_PUBLISHED', $this->config->item("post_status_published", $this->configFile)) : null;

		// load libraries
		$this->load->library('blog/blog_lib');
		$this->load->library('blog/blog_render_lib');
		$this->load->library('templates');
		$this->load->library('date_time');
		$this->load->library('pagination');
		$this->load->library('form_response');
		$this->load->library('form_validation');
		//$this->form_validation->CI = & $this;    // required for form validation to work with hmvc

		$this->load->library('blog/blog_tag_base_lib');
		$this->load->library('blog/blog_url_gen_lib');

		// load helpers
		$this->load->helper('admin_helper');
		$this->load->helper('settings_helper');

		// load models
		$this->load->model('blog/blog_model');

		$this->parseData['module:resource'] = module_resource_url($this->module);
		$this->parseData['default-message'] = "";
	}

	//------------------- Extraction controllers --------------------------

	public function _extract_content($tempId, $parseTag, $innerText, & $db) {

		$tagId = null;

		//  split the tag string by ':', create an array of key-value pair
		$tagParts = $this->blog_render_lib->decode_block_tag($parseTag);

		$tempId = null;

		// if template mentioned in tagstring, create template in db
		if (isset($tagParts['template'])) {

			$this->templates->set_db($db);

			// add template, if template exists --> do not add new one, simply get tempId
			$tempId = $this->templates->add_module_template($this->module, $tagParts['template'], $innerText, null, null, true);
		}

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

	//------------------- Blog Settings controllers --------------------------

	public function settings() {

		$title = 'Blog Settings';
		$viewFile = 'blog/settings';

		$settings = $this->site_settings->get_data($this->module);

		// default values of form
		$this->parseData['default-message'] = "Edit blog settings";

		$options = array(
		    BLOG_VIEW_SUMMARY => 'Summary',
		    BLOG_VIEW_FEATURED => 'Featured posts',
		    BLOG_VIEW_LATEST => 'Latest posts',
		    BLOG_VIEW_ARCHIVES => 'Archives',
		    BLOG_VIEW_ARCHIVES_CATEGORIZED => 'Categorized archives'
		);
		$selectedValue = get_settings($this->module, $this->config->item('setting_default_view', $this->configFile));
		$this->parseData['set:select:default_view'] = form_dropdown('default_view', $options, $selectedValue, "id='default_view' class='form_select_fields'");

		$this->parseData['permalink'] = get_settings($this->module, $this->config->item('setting_permalink', $this->configFile));
		$this->parseData['excerpt_word_count'] = get_settings($this->module, $this->config->item('setting_excerpt_word_count', $this->configFile));
		$this->parseData['limit'] = get_settings($this->module, $this->config->item('setting_limit', $this->configFile));
		$this->parseData['default_header'] = get_settings($this->module, $this->config->item('setting_default_header', $this->configFile));
		$this->parseData['default_footer'] = get_settings($this->module, $this->config->item('setting_default_footer', $this->configFile));
		$this->parseData['ad_script'] = get_settings($this->module, $this->config->item('setting_ad_script', $this->configFile));

		// set comments visibility ( post_is_comments )
		$attributes = array(
		    'name' => 'allow_comments',
		    'id' => 'allow_comments',
		    'value' => true,
		    'checked' => (bool) get_settings($this->module, $this->config->item('setting_allow_comments', $this->configFile)),
		    'style' => ''
		);
		$this->parseData['blog:checkbox:allow_comments'] = form_checkbox($attributes);

		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function settings_save() {

		$settings = array(
		    "default_view" => $this->input->post('default_view'),
		    "permalink" => trim($this->input->post('permalink'), '/'),
		    "excerpt_word_count" => $this->input->post('excerpt_word_count'),
		    "limit" => $this->input->post('limit'),
		    "default_header" => $this->input->post('default_header'),
		    "default_footer" => $this->input->post('default_footer'),
		    "ad_script" => $this->input->post('ad_script'),
		    "allow_comments" => (bool) $this->input->post('allow_comments')
		);

		$this->form_validation->set_rules('default_view', 'Default view', '');
		$this->form_validation->set_rules('permalink', 'Permalink structure', 'required|callback__check_permalink_structure'); // validation for permalink
		$this->form_validation->set_rules('excerpt_word_count', 'Excerpts word count', 'integer');
		$this->form_validation->set_rules('limit', 'Number of posts', 'integer|greater_than[4]|less_than[21]');
		$this->form_validation->set_rules('default_header', 'Default header', '');
		$this->form_validation->set_rules('default_footer', 'Default footer', '');
		$this->form_validation->set_rules('ad_script', 'Default Ad script', '');
		$this->form_validation->set_rules('allow_comments', 'Allow Comments', '');


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
				->add_validation_msg("default_view", form_error("default_view"))
				->add_validation_msg("permalink", form_error("permalink"))
				->add_validation_msg("excerpt_word_count", form_error("excerpt_word_count"))
				->add_validation_msg("limit", form_error("limit"))
				->add_validation_msg("default_header", form_error("default_header"))
				->add_validation_msg("default_footer", form_error("default_footer"))
				->add_validation_msg("ad_script", form_error("ad_script"))
				->add_validation_msg("allow_comments", form_error("allow_comments"));
		}

		$this->form_response->send();
	}

	//------------------- POSTS controllers --------------------------

	public function add_post() {

		$title = 'Add Post';
		$viewFile = 'blog/post_edit';

		// default values of form
		$this->parseData['default-message'] = ""; //Add Post
		$this->parseData['default-category'] = BLOG_DEFAULT_CATEG_SLUG;

		$this->parseData['action_type'] = BLOG_ACTION_ADD;
		$this->parseData['username'] = get_username();

		$this->parseData['post_id'] = null;
		$this->parseData['post_title'] = "";
		$this->parseData['post_slug'] = "";
		$this->parseData['post_body'] = "";
		$this->parseData['post_tags'] = "";


		// create drop down list for post_status
		$postStatus = array(
		    'draft' => 'Draft',
		    'published' => 'Published'
		);
		$attribs = " id='post_status' ";
		$attribs .= " class='form_select_fields' ";
		$select = '';
		$this->parseData['blog:select:post_status'] = form_dropdown('post_status', $postStatus, $select, $attribs);


		// set comments visibility ( post_is_comments )
		$attributes = array(
		    'name' => 'post_is_comments',
		    'id' => 'post_is_comments',
		    'value' => true,
		    'checked' => true,
		    'style' => '',
		);
		$this->parseData['blog:checkbox:post_is_comments'] = form_checkbox($attributes);

		// get list of categories, with checked OR unchecked value ( note sice add new post, all categs will be unchecked )
		$categories = $this->blog_model->get_categ_post_assoc(); // $postId = null --> Get all categories
		$categList = null;
		foreach ($categories as $categ) {

			$checkBoxAttrib = array(
			    'name' => 'post_categories[]',
			    'class' => 'post_categories',
			    'value' => $categ['slug'],
			    'checked' => ( $categ['slug'] == BLOG_DEFAULT_CATEG_SLUG ) ? true : $categ['checked'], // check 'uncategorized' by default
			    'style' => ''
			);

			$categList .= form_checkbox($checkBoxAttrib) . $categ['name'];
		}
		$this->parseData['blog:multi-checkbox:post_categories'] = $categList;


		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function save_post() {

		/*
		 * -- POST values --
		 *
		 * post_id
		 * action_type
		 *
		 * post_title
		 * post_slug
		 * post_body
		 * post_status
		 * post_is_comments
		 *
		 * ----- Meta Data --------
		 * meta_title*
		 * meta_keywords*
		 * meta_description*
		 * post_excerpt*
		 * post_header*
		 * post_footer*
		 * post_description*
		 *
		 * ---- Other related table values ----------
		 * post_categories[]
		 * post_tags[]
		 *
		 * --------- Other generated values required ----------
		 * author_username
		 *
		 */

		// get post values
		$actionType = $this->input->post('action_type');
		$postId = $this->input->post('post_id');
		$categSlugs = $this->input->post('post_categories'); // array
		$postTags = $this->input->post('post_tags'); // comma separated string

		$this->form_validation->set_rules('post_title', 'Title', 'required');
		$this->form_validation->set_rules('post_slug', 'Slug', "required|alpha_dash|callback__check_slug_unique[$actionType]");
		$this->form_validation->set_rules('post_body', 'Body', 'required');
		$this->form_validation->set_rules('post_status', 'Status', 'required');
		$this->form_validation->set_rules('post_is_comments', 'Comments', '');
		$this->form_validation->set_rules('post_categories', 'Categories', 'required');
		$this->form_validation->set_rules('post_tags', 'Tags', '');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$blogDb = array(
			    //"blog_post_id" => null,
			    "blog_post_author_username" => $this->input->post('username'),
			    "blog_post_title" => $this->input->post('post_title'),
			    "blog_post_slug" => $this->input->post('post_slug'),
			    "blog_post_body" => $this->input->post('post_body'),
			    "blog_post_created" => $this->date_time->now(),
			    "blog_post_modified" => $this->date_time->now(),
			    "blog_post_status" => $this->input->post('post_status'),
			    "blog_post_is_comments" => (bool) $this->input->post('post_is_comments')
			);

			$success = false;
			$error = null;

			$db = & $this->blog_model->get_db();

			$this->blog_model->transaction_strict(true);
			$this->blog_model->transaction_begin();

			switch ($actionType) {
				case BLOG_ACTION_ADD:

					// insert new post and get new post_id
					$postId = $this->blog_model->add_post($blogDb);

					// add post under mentioned categories
					$success = $this->blog_model->set_post_categ_assoc($postId, $categSlugs);

					// add post under mentioned tags
					$success = $this->blog_lib->set_post_tag_assoc($postId, $postTags, $db);

					$successMsg = "Post successfully created";
					$error = "Unable to add post";
					break;

				case BLOG_ACTION_EDIT:

					// remove unnecessary fields from db list
					if (isset($blogDb['blog_post_created']))
						unset($blogDb['blog_post_created']);

					$success = $this->blog_model->edit_post($postId, $blogDb);

					// add post under mentioned categories
					$success = $this->blog_model->set_post_categ_assoc($postId, $categSlugs);

					// add post under mentioned tags
					$success = $this->blog_lib->set_post_tag_assoc($postId, $postTags, $db);

					$successMsg = "Post successfully updated";
					$error = "Unable to edit post";
					break;

				default:
					$success = false;
					$error = "Invalid action type";
					break;
			}

			if ($success) {

//                                echo "success";
				$this->blog_model->transaction_commit();
				$this->form_response->set_message(WM_STATUS_SUCCESS, $successMsg);
			} else {

//                                echo "fail";
				$this->blog_model->transaction_rollback();
				$this->form_response->set_message(WM_STATUS_ERROR, $error);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("post_title", form_error("post_title"))
				->add_validation_msg("post_slug", form_error("post_slug"))
				->add_validation_msg("post_body", form_error("post_body"))
				->add_validation_msg("post_status", form_error("post_status"))
				->add_validation_msg("post_is_comments", form_error("post_is_comments"))
				->add_validation_msg("post_categories", form_error("post_categories"))
				->add_validation_msg("post_tags", form_error("post_tags"));


//                        echo "invalid form fields";
		}

		$this->form_response->send();

//                echo get_admin_main_content($this->parseData, $pageTitle, $output);
	}

	public function edit_post($postId = null) {

		$title = 'Edit Post';
		$viewFile = 'blog/post_edit';

		$postDetails = $this->blog_model->get_post_details($postId, null); // $status = null inplies ignore status of post

		$output = null;

		if (!is_null($postDetails)) {

			// default values of form
			$this->parseData['default-message'] = "Edit Post";
			$this->parseData['default-category'] = BLOG_DEFAULT_CATEG_SLUG;

			$this->parseData['action_type'] = BLOG_ACTION_EDIT;
			$this->parseData['username'] = $postDetails['username'];

			$this->parseData['post_id'] = $postDetails['id'];
			$this->parseData['post_title'] = $postDetails['title'];
			$this->parseData['post_slug'] = $postDetails['slug'];
			$this->parseData['post_body'] = $postDetails['body'];
			$this->parseData['post_tags'] = $this->blog_lib->get_post_tags_assoc($postDetails['id']);


			// create drop down list for post_status
			$postStatus = array(
			    'draft' => 'Draft',
			    'published' => 'Published'
			);
			$attribs = " id='post_status' ";
			$attribs .= " class='form_select_fields' ";
			$select = $postDetails['status'];
			$this->parseData['blog:select:post_status'] = form_dropdown('post_status', $postStatus, $select, $attribs);


			// set comments visibility ( post_is_comments )
			$attributes = array(
			    'name' => 'post_is_comments',
			    'id' => 'post_is_comments',
			    'value' => true,
			    'checked' => (bool) $postDetails['is_comments'],
			    'style' => '',
			);
			$this->parseData['blog:checkbox:post_is_comments'] = form_checkbox($attributes);

			// get list of categories, with checked OR unchecked value ( note sice add new post, all categs will be unchecked )
			$categories = $this->blog_model->get_categ_post_assoc($postDetails['id']); // $postId = null --> Get all categories
			$categList = null;
			foreach ($categories as $categ) {

				$checkBoxAttrib = array(
				    'name' => 'post_categories[]',
				    'class' => 'post_categories',
				    'value' => $categ['slug'],
				    'checked' => $categ['checked'], // check 'uncategorized' by default
				    'style' => ''
				);

				$categList .= form_checkbox($checkBoxAttrib) . $categ['name'];
			}
			$this->parseData['blog:multi-checkbox:post_categories'] = $categList;

			$output = $this->parser->parse($viewFile, $this->parseData, true);
		} else {

			// Post NOT found
			$output = "Post not found";
		}

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function manage_posts($pageNo = 0) {

		$title = 'Manage Posts';
		$view = "blog/posts_manage.php";

		$module = strtolower(__CLASS__);
		$method = strtolower(__FUNCTION__);
		$pageUrl = site_url("admin/$module/$method");

		$offset = $this->pagination->get_offset($pageNo, WM_PAGINATION_LIMIT);

		$posts = $this->blog_data_model->get_posts_by_date(null, null, null, WM_PAGINATION_LIMIT, $offset, true); // $allowHidden = true

		$this->parseData['posts'] = null;

		if (!is_null($posts)) {

			// set pagination params
			$config['base_url'] = $pageUrl;
			$config['total_rows'] = $this->blog_data_model->get_total_rows('get_posts_by_date');
//			$config['total_rows'] = $this->blog_data_model->total_row_count();
			// initialize pagination & create pagination links
			$this->pagination->initialize($config, $pageNo);
			$this->parseData['pagination'] = $this->pagination->create_links();
			$this->parseData['status:published'] = BLOG_POST_STATUS_PUBLISHED;
			$this->parseData['status:draft'] = BLOG_POST_STATUS_DRAFT;

			$tags = array();
			$count = 0;

			// process each post, create appropriate tags
			foreach ($posts as $post) {

				// generate post conditions to get post url
				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->blog_tag_base_lib->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->blog_tag_base_lib->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->blog_tag_base_lib->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->blog_tag_base_lib->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->blog_tag_base_lib->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);

				// get correct uri for each post.
				$postUri = $this->blog_url_gen_lib->get_uri_post($conditions);


				// generate Categ lists with respective url ( eg. admin/blog/edit_categ/categ_slug )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categIdsArray = !is_null($post['category_ids']) ? explode(',', $post['category_ids']) : null;

				$categTags = null;
				if (is_array($categSlugArray)) {

					foreach ($categSlugArray as $no => $slug) {

						$categTags[] = array(
						    'categ:slug' => trim($slug),
						    'categ:name' => trim($categNameArray[$no]),
						    'categ:id' => trim($categIdsArray[$no])
						);
					}
				}


				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagIdsArray = !is_null($post['tag_ids']) ? explode(',', $post['tag_ids']) : null;
				$tagTags = array();
				if (is_array($tagSlugArray)) {
					foreach ($tagSlugArray as $no => $slug) {

						$tagTags[] = array(
						    'tag:slug' => trim($slug),
						    'tag:name' => trim($tagNameArray[$no]),
						    'tag:id' => trim($tagIdsArray[$no])
						);
					}
				}

				$tags[$count]['id'] = $post['id'];
				$tags[$count]['title'] = $post['title'];
				$tags[$count]['slug'] = $post['slug'];
//                $tags[$count]['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
//                $tags[$count]['body'] = isset($post['body']) ? $post['body'] : null;
//                $tags[$count]['created'] = isset($post['created']) ? $this->date_time->date($post['created']) : null;
				$tags[$count]['updated'] = $this->date_time->date($post['updated']);
				$tags[$count]['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags[$count]['categs'] = $categTags;
				$tags[$count]['tags'] = $tagTags;
				$tags[$count]['author:username'] = $post['author_username'];
				$tags[$count]['author:name'] = $post['author_name'];
//                $tags[$count]['author:url'] = site_url($this->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags[$count]['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags[$count]['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;
				$tags[$count]['published'] = ( strtolower($post['status']) == 'published' ) ? true : null;
				$tags[$count]['status'] = strtolower($post['status']);

				$count++;
			}

			$this->parseData['posts'] = $tags;
		}
		$output = $this->parser->parse($view, $this->parseData, true);
//		else {
//
//			$output = "No posts have been created yet";
//		}
		echo get_admin_main_content($this->parseData, $title, $output);
	}

	/**
	 * Sets a post status to specified status
	 * can also handle array of posts
	 */
	public function set_posts_status($id = null, $status = null) {

		// POST values override param values
		$ids = $id;
		if (isset($_POST['ids']) AND isset($_POST['status'])) {

			$ids = $this->input->post('ids');
			$status = $this->input->post('status');
		}

		if (!is_null($ids) AND ( $status == BLOG_POST_STATUS_DRAFT OR $status == BLOG_POST_STATUS_PUBLISHED )) {

			// Toggle categ is_visible
			$success = $this->blog_model->set_posts_status($ids, $status);

			// delete from database only if actual file deletion successful
			if ($success) {
				$success = true;
				$count = count($ids);
				$output = "changes successfull";
				$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
			} else {
				$success = false;
				$output = "changes unsuccessfull";
				$this->json_response->set_message(WM_STATUS_ERROR, $output);
			}
		} else {
			// invalid status values
			$output = "Invalid values provided";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	public function delete_posts($id = null) {

		$success = false;

		$ids = isset($_POST['ids']) ? $this->input->post('ids') : array($id);

		// delete category from db, note: pending move orphan posts to uncategorized
		$success = $this->blog_model->delete_posts($ids);

		if ($success) {
			$success = true;
			$count = count($ids);
			$output = ( $count == 1 ) ? "$count post successfully deleted" : "$count posts successfully deleted";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$success = false;
			$output = "Could not delete post(s)";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	//------------------- CATEGORY controllers --------------------------

	public function manage_categories($pageNo = 0) {

		$title = 'Manage Categories';
		$viewFile = "blog/categ_manage";

		$pageUrl = site_url("admin/" . strtolower(__CLASS__) . "/" . strtolower(__FUNCTION__));

		$offset = $this->pagination->get_offset($pageNo, WM_PAGINATION_LIMIT);

		$categs = $this->blog_data_model->get_categories(WM_PAGINATION_LIMIT, $offset, true, true, true); // $allowHiddenPosts = true, $allowHidden = true, $allowSpecial = true
		$this->parseData['categories'] = null;

		if (!is_null($categs)) {

			// set pagination params
			$config['base_url'] = $pageUrl;
			$config['total_rows'] = $this->blog_data_model->get_total_rows('get_categories');
//			$config['total_rows'] = $this->blog_data_model->total_row_count();
			// initialize pagination & create pagination links
			$this->pagination->initialize($config, $pageNo);
			$this->parseData['pagination'] = $this->pagination->create_links();

			$tags = array();

			$count = 0;

			// run through each categ, save in appropriate tag
			foreach ($categs as $categ) {

				$tags[$count]['id'] = $categ['id'];
				$tags[$count]['name'] = $categ['name'];
				$tags[$count]['slug'] = $categ['slug'];
//				$tags[$count]['excerpt'] = $this->get_excerpt($categ['body']);
//				$tags[$count]['body'] = $categ['body'];
//				$tags[$count]['created'] = $this->date_time->date($categ['created']);
				$tags[$count]['updated'] = $this->date_time->date($categ['updated']);
				$tags[$count]['count'] = $categ['count'];
				$tags[$count]['is_visible'] = $categ['is_visible'];
				$tags[$count]['is_special'] = $categ['is_special'];
				$tags[$count]['is_comments'] = $categ['is_comments'];
				$tags[$count]['url'] = site_url($this->blog_url_gen_lib->get_uri_categ($categ['slug']));

				$count++;
			}

			$this->parseData['categories'] = $tags;

			$output = $this->parser->parse($viewFile, $this->parseData, true);
		} else {
			$output = "No Categories have been created yet";
		}


		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function delete_categs($id = null) {

		$ids = isset($_POST['ids']) ? $this->input->post('ids') : array($id);

		// delete category from db, note: pending move orphan posts to uncategorized
		$success = $this->blog_model->delete_categs($ids);

		if ($success) {
			$success = true;
			$count = count($ids);
			$output = ( $count == 1 ) ? "$count category successfully deleted" : "$count categories successfully deleted";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$success = false;
			$output = "Could not delete Categories";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	public function toggle_categs_visibility($id = null, $visible = null) {

		// POST values override param values
		$ids = $id;
		if (isset($_POST['ids']) AND isset($_POST['is_visible'])) {

			$ids = $this->input->post('ids');
			$visible = (bool)$this->input->post('is_visible');
		}

		// Toggle categ is_visible
		$success = $this->blog_model->toggle_categs_visibility($ids, $visible);

		if ($success) {
			$success = true;
			$count = count($ids);
			$output = "changes successfull";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$success = false;
			$output = "changes unsuccessfull";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	/**
	 * Toggles / Sets categs comment status
	 * if is_comments provided --> sets comments status
	 * else toggles current status
	 *
	 * @param $id int id of categ
	 * @param $is_comments bool (optional)
	 */
	public function toggle_categs_comments($id = null, $is_comments = null) {

		// POST values override param values
		$ids = $id;
		if (isset($_POST['ids']) AND isset($_POST['is_comments'])) {

			$ids = $this->input->post('ids');
			$is_comments = $this->input->post('is_comments');
		}

		// Toggle categ is_visible
		$success = $this->blog_model->toggle_categs_comments($ids, $is_comments);

		// delete from database only if actual file deletion successfull
		if ($success) {
			$success = true;
			$count = count($ids);
			$output = "changes successfull";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$success = false;
			$output = "changes unsuccessfull";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	/**
	 * this is a private controller, that simply generates the add category section of the manage categories page,
	 * It is called from manage_categories()
	 */
	public function add_categ() {

		$title = 'Add Category';
		$viewFile = 'blog/categ_edit';

		// default values of form
		$this->parseData['default-message'] = "Add Category";

		$this->parseData['action_type'] = BLOG_ACTION_ADD;

		$this->parseData['categ_id'] = null;
		$this->parseData['categ_name'] = "";
		$this->parseData['categ_slug'] = "";
		$this->parseData['categ_description'] = "";

		// set comments visibility ( categ_is_comments )
		$attributes = array(
		    'name' => 'categ_is_comments',
		    'id' => 'categ_is_comments',
		    'value' => true,
		    'checked' => true,
		    'style' => '',
		);
		$this->parseData['blog:checkbox:categ_is_comments'] = form_checkbox($attributes);

		// set comments visibility ( categ_is_visible )
		$attributes = array(
		    'name' => 'categ_is_visible',
		    'id' => 'categ_is_visible',
		    'value' => true,
		    'checked' => true,
		    'style' => '',
		);
		$this->parseData['blog:checkbox:categ_is_visible'] = form_checkbox($attributes);

		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function save_categ() {

		// get post values
		$actionType = $this->input->post('action_type');
		$categId = $this->input->post('categ_id');

		$this->form_validation->set_rules('categ_name', 'Name', 'required');
		$this->form_validation->set_rules('categ_slug', 'Slug', "required|alpha_dash|callback__check_categ_slug_unique[$categId]");
		$this->form_validation->set_rules('categ_description', 'Description', '');
		$this->form_validation->set_rules('categ_is_comments', 'Comments', '');
		$this->form_validation->set_rules('categ_is_visible', 'Visible', '');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$categDb = array(
			    //"blog_categ_id" => null,
//                            'blog_categ_parent_id' => null,
			    "blog_categ_name" => $this->input->post('categ_name'),
			    "blog_categ_slug" => $this->input->post('categ_slug'),
			    "blog_categ_description" => $this->input->post('categ_description'),
			    "blog_categ_created" => $this->date_time->now(),
			    "blog_categ_modified" => $this->date_time->now(),
			    "blog_categ_is_comments" => (bool) $this->input->post('categ_is_comments'),
			    "blog_categ_is_visible" => (bool) $this->input->post('categ_is_visible')
			);

			$success = false;
			$error = null;

			switch ($actionType) {
				case BLOG_ACTION_ADD:

					// insert new categ and get new categ_id
					$categId = $this->blog_model->add_categ($categDb);

					$success = $categId > 0 ? true : false;

					$successMsg = "category successfully created";
					$error = "Unable to add category";
					break;

				case BLOG_ACTION_EDIT:

					// remove unnecessary fields from db list
					if (isset($categDb['blog_categ_created']))
						unset($categDb['blog_categ_created']);

					$success = $this->blog_model->edit_categ($categId, $categDb);

					$successMsg = "category successfully updated";
					$error = "Unable to edit category";
					break;

				default:
					$success = false;
					$error = "Invalid action type";
					break;
			}

			if ($success) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, $successMsg);
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, $error);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("categ_name", form_error("categ_name"))
				->add_validation_msg("categ_slug", form_error("categ_slug"))
				->add_validation_msg("categ_description", form_error("categ_description"))
				->add_validation_msg("categ_is_comments", form_error("categ_is_comments"))
				->add_validation_msg("categ_is_visible", form_error("categ_is_visible"));


//                        echo "invalid form fields";
		}

		$this->form_response->send();
	}

	public function edit_categ($categId = null) {

		$title = "Edit Category";
		$viewFile = 'blog/categ_edit';

		$categDetails = $this->blog_model->get_categ_details($categId, true, false); // $allowHidden = true, $allowSpecial = false

		$output = null;

		if (!is_null($categDetails)) {

			$this->parseData['default-message'] = "Edit Category";
			$this->parseData['action_type'] = BLOG_ACTION_EDIT;

			$this->parseData['categ_id'] = $categDetails['id'];
			$this->parseData['categ_name'] = $categDetails['name'];
			$this->parseData['categ_slug'] = $categDetails['slug'];
			$this->parseData['categ_description'] = $categDetails['description'];

			// set comments visibility ( categ_is_comments )
			$attributes = array(
			    'name' => 'categ_is_comments',
			    'id' => 'categ_is_comments',
			    'value' => true,
			    'checked' => (bool) $categDetails['is_comments'],
			    'style' => '',
			);
			$this->parseData['blog:checkbox:categ_is_comments'] = form_checkbox($attributes);

			// set comments visibility ( categ_is_visible )
			$attributes = array(
			    'name' => 'categ_is_visible',
			    'id' => 'categ_is_visible',
			    'value' => true,
			    'checked' => (bool) $categDetails['is_comments'],
			    'style' => '',
			);
			$this->parseData['blog:checkbox:categ_is_visible'] = form_checkbox($attributes);

			$output = $this->parser->parse($viewFile, $this->parseData, true);
		} else {

			// Categ NOT found
			$output = "The Category you are trying to edit does not exist or it is a special category and cannot be edited.";
		}

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	//------------------- TAG controllers --------------------------


	public function manage_tags($pageNo = 0) {

		$title = 'Manage Tags';
		$viewFile = "blog/tag_manage";

		$pageUrl = site_url("admin/" . strtolower(__CLASS__) . "/" . strtolower(__FUNCTION__));

		$offset = $this->pagination->get_offset($pageNo, WM_PAGINATION_LIMIT);

		$tags = $this->blog_data_model->get_tags(WM_PAGINATION_LIMIT, $offset, true);  //$allowHiddenPosts = true
		$this->parseData['tags'] = null;

		if (!is_null($tags)) {

			// set pagination params
			$config['base_url'] = $pageUrl;
			$config['total_rows'] = $this->blog_data_model->get_total_rows('get_tags');
//			$config['total_rows'] = $this->blog_data_model->total_row_count();
			// initialize pagination & create pagination links
			$this->pagination->initialize($config, $pageNo);
			$this->parseData['pagination'] = $this->pagination->create_links();

			$parse = array();

			$count = 0;

			// run through each tag, save in appropriate tag
			foreach ($tags as $tag) {

				$parse[$count]['id'] = $tag['id'];
				$parse[$count]['name'] = $tag['name'];
				$parse[$count]['slug'] = $tag['slug'];
//				$parse[$count]['excerpt'] = $this->get_excerpt($tag['body']);
//				$parse[$count]['body'] = $tag['body'];
				$parse[$count]['count'] = $tag['count'];
				$parse[$count]['url'] = site_url($this->blog_url_gen_lib->get_uri_tag($tag['slug']));

				$count++;
			}

			$this->parseData['tags'] = $parse;
		} else {

			$output = "No tags have been created yet";
		}

		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function add_tag() {

		$title = 'Add Tag';
		$viewFile = 'blog/tag_edit';

		// default values of form
		$this->parseData['default-message'] = "Add Tag";

		$this->parseData['action_type'] = BLOG_ACTION_ADD;

		$this->parseData['tag_id'] = null;
		$this->parseData['tag_name'] = "";
		$this->parseData['tag_slug'] = "";
		$this->parseData['tag_description'] = "";

		$output = $this->parser->parse($viewFile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function save_tag() {

		// get post values
		$actionType = $this->input->post('action_type');
		$tagId = $this->input->post('tag_id');

		$this->form_validation->set_rules('tag_name', 'Name', 'required');
		$this->form_validation->set_rules('tag_slug', 'Slug', "required|alpha_dash|callback__check_tag_slug_unique[$tagId]");
		$this->form_validation->set_rules('tag_description', 'Description', '');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			$tagDb = array(
			    //"blog_tag_id" => null,
//                            'blog_tag_parent_id' => null,
			    "blog_tag_name" => $this->input->post('tag_name'),
			    "blog_tag_slug" => $this->input->post('tag_slug'),
			    "blog_tag_description" => $this->input->post('tag_description')
			);

			$success = false;
			$error = null;

			switch ($actionType) {
				case BLOG_ACTION_ADD:

					// insert new tag and get new tag_id
					$tagId = $this->blog_model->add_tag($tagDb);

					$success = $tagId > 0 ? true : false;

					$successMsg = "tag successfully created";
					$error = "Unable to create tag";
					break;

				case BLOG_ACTION_EDIT:

					$success = $this->blog_model->edit_tag($tagId, $tagDb);

					$successMsg = "tag successfully updated";
					$error = "Unable to edit tag";
					break;

				default:
					$success = false;
					$error = "Invalid action type";
					break;
			}

			if ($success) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, $successMsg);
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, $error);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("tag_name", form_error("tag_name"))
				->add_validation_msg("tag_slug", form_error("tag_slug"))
				->add_validation_msg("tag_description", form_error("tag_description"));
		}

		$this->form_response->send();
	}

	public function edit_tag($tagId = null) {

		$title = 'Edit Tag';
		$viewFile = 'blog/tag_edit';

		$tagDetails = $this->blog_model->get_tag_details($tagId);

		$output = null;

		if (!is_null($tagDetails)) {

			// default values of form
			$this->parseData['default-message'] = "Edit Tag";

			$this->parseData['action_type'] = BLOG_ACTION_EDIT;

			$this->parseData['tag_id'] = $tagDetails['id'];
			$this->parseData['tag_name'] = $tagDetails['name'];
			$this->parseData['tag_slug'] = $tagDetails['slug'];
			$this->parseData['tag_description'] = $tagDetails['description'];

			$output = $this->parser->parse($viewFile, $this->parseData, true);
		} else {

			$output = "Tag not found";
		}

		echo get_admin_main_content($this->parseData, $title, $output);
	}

	public function delete_tags($id = null) {

		$ids = isset($_POST['ids']) ? $this->input->post('ids') : array($id);

		// delete tag from db
		$success = $this->blog_model->delete_tags($ids);

		if ($success) {
			$success = true;
			$count = count($ids);
			$output = ( $count == 1 ) ? "$count tag successfully deleted" : "$count tags successfully deleted";
			$this->json_response->set_message(WM_STATUS_SUCCESS, $output);
		} else {
			$success = false;
			$output = "Could not delete tags";
			$this->json_response->set_message(WM_STATUS_ERROR, $output);
		}

		$this->json_response->send();
	}

	// ------------------------------ Private functions -------------------------

	/**
	 * Form validation functin to check if a slug is NOT already used in the database for posts
	 *
	 * @param string $slug value sent by CIs validation functions
	 * @return bool
	 */
	public function _check_slug_unique($slug, $actionType) {

		$success = true;

		if ($actionType == 'add') {

			// if slug exists --> success = false, else true
			if (!$this->blog_model->check_is_post_slug_unique($slug)) {

				$this->form_validation->set_message('_check_slug_unique', 'This slug has already been used, please select a unique slug');

				$success = false;
			}
		}

		return $success;
	}

	public function _check_permalink_structure($permalink) {

		$success = true;

		$allowedPermaParts = $this->config->item('allowed_perma_parts', $this->configFile);

		$permaParts = explode("/", $permalink);

		foreach ($permaParts as $segment) {

			// check if current segment -- starts with %, ends with %, has atleast one char inbetween
			if (preg_match("/^%(.+)%$/", $segment, $matches)) {

				// check if current segment is a valid permalink part
				// if NOT --> break, throw error
				if (!in_array($segment, $allowedPermaParts)) {

					// error
					$errorMsg = "invalid permalink structure, please use valid permalink segments";
					$this->form_validation->set_message(__FUNCTION__, $errorMsg);
					$success = false;
					break;
				}
			} else {
				// this is a constant, ignore
			}
		}

		return $success;
	}

	/**
	 * Form validation functin to check if a slug is NOT already used in the database for categs
	 *
	 * @param string $slug value sent by CIs validation functions
	 * @return bool
	 */
	public function _check_categ_slug_unique($categSlug, $categId) {

		$success = true;

		// if slug exists --> success = false, else true
		if (!$this->blog_model->check_is_categ_slug_unique($categSlug, $categId)) {

			$this->form_validation->set_message('_check_categ_slug_unique', 'This slug has already been used, please select a unique slug');

			$success = false;
		}


		return $success;
	}

	/**
	 * Form validation functin to check if a slug is NOT already used in the database for categs
	 *
	 * @param string $slug value sent by CIs validation functions
	 * @return bool
	 */
	public function _check_tag_slug_unique($tagSlug, $tagId) {

		$success = true;

		// if slug exists --> success = false, else true
		if (!$this->blog_model->check_is_tag_slug_unique($tagSlug, $tagId)) {

			$this->form_validation->set_message('_check_tag_slug_unique', 'This slug has already been used, please select a unique slug');

			$success = false;
		}


		return $success;
	}

}

?>
