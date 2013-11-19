<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of blog_tag_base_lib
 *
 * @author Lloyd
 */
class blog_tag_base_lib {

	protected $ci;
	protected $configFile = 'blog_config';
	protected $module = 'blog';
	//----------------------------------
//        private $allowedTypes = null; // set in constructor | array('summary', 'group', 'post', 'widget', 'custom');
//        private $allowedGroupTypes = null; // set in constructor | array('author', 'category', 'tag', 'date');
	//------------- Main block, Instance holder -------------
	private $postId = null;
	private $postSlug = null;
	private $conditions = array();  // all possible conditions for particulr post / categ / both
	private $viewKeyword = null;
	private $allowedConditions = null; // holds list of allowed urlCondition keys | array('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part' );
	private $excerptWordCount = null;
	private $limit = null;

	/**
	 * @var
	 *
	 * possible conditions ( condition_type = 'some_value' )
	 * author, category, tag, year, month, day, post, post_id
	 */
	//--------------------------
	private $tags = array();
	private $error = null;

	public function __construct() {

		$this->reset();

		$this->ci = & get_instance();

		// load config files
		$this->ci->config->load($this->configFile, true, true, $this->module);

		$this->allowedConditions = $this->ci->config->item('allowed_conditions', $this->configFile);

		// load libraries
		$this->ci->load->library('date_time');
		$this->ci->load->library('blog/blog_url_gen_lib');

		// load helpers
		$this->ci->load->helper('array_helper');

		// load models
		$this->ci->load->model('blog/blog_data_model');

		// featured category
		!defined('BLOG_CATEG_FEATURED') ? define('BLOG_CATEG_FEATURED', $this->ci->config->item('category_featured', $this->configFile)) : null; // uncategorized
		// url/block tag condition keys
		!defined('BLOG_CONDITION_AUTHOR') ? define('BLOG_CONDITION_AUTHOR', $this->ci->config->item('condition_key_author', $this->configFile)) : null;
		!defined('BLOG_CONDITION_CATEGORY') ? define('BLOG_CONDITION_CATEGORY', $this->ci->config->item('condition_key_category', $this->configFile)) : null;
		!defined('BLOG_CONDITION_TAG') ? define('BLOG_CONDITION_TAG', $this->ci->config->item('condition_key_tag', $this->configFile)) : null;
		!defined('BLOG_CONDITION_YEAR') ? define('BLOG_CONDITION_YEAR', $this->ci->config->item('condition_key_year', $this->configFile)) : null;
		!defined('BLOG_CONDITION_MONTH') ? define('BLOG_CONDITION_MONTH', $this->ci->config->item('condition_key_month', $this->configFile)) : null;
		!defined('BLOG_CONDITION_DAY') ? define('BLOG_CONDITION_DAY', $this->ci->config->item('condition_key_day', $this->configFile)) : null;
		!defined('BLOG_CONDITION_POST') ? define('BLOG_CONDITION_POST', $this->ci->config->item('condition_key_post', $this->configFile)) : null;
		!defined('BLOG_CONDITION_POST_ID') ? define('BLOG_CONDITION_POST_ID', $this->ci->config->item('condition_key_post_id', $this->configFile)) : null;
		!defined('BLOG_CONDITION_PART') ? define('BLOG_CONDITION_PART', $this->ci->config->item('condition_key_part', $this->configFile)) : null;

		// get settings values
		//
                // get excerpt word count from settings
		$excerptWordCountKey = $this->ci->config->item('setting_excerpt_word_count', $this->configFile);
		$this->set_excerptWordCount(get_settings($this->module, $excerptWordCountKey));

		// get limit or posts or items
		$limitKey = $this->ci->config->item('setting_limit', $this->configFile);
		$this->set_limit(get_settings($this->module, $limitKey));

		// shortcode:ad
		!defined('BLOG_SHORTCODE_AD') ? define('BLOG_SHORTCODE_AD', $this->ci->config->item('shortcodes_ad', $this->configFile)) : null;
	}

	//------------ Setter & Getters ----------------

	public function get_postId() {
		return $this->postId;
	}

	public function set_postId($postId) {
		$this->postId = $postId;
	}

	public function get_postSlug() {
		return $this->postSlug;
	}

	public function set_postSlug($postSlug) {
		$this->postSlug = $postSlug;
	}

	protected function set_tags($tags) {
		$this->tags = $tags;
	}

	public function get_tags() {
		return $this->tags;
	}

	public function get_conditions() {
		return $this->conditions;
	}

	/**
	 * Get a particular condition value, by providing appropriate key
	 * valid keys can be found using constants mentioned below
	 * BLOG_CONDITION_AUTHOR,
	 * BLOG_CONDITION_CATEGORY,
	 * BLOG_CONDITION_TAG,
	 * BLOG_CONDITION_YEAR,
	 * BLOG_CONDITION_MONTH,
	 * BLOG_CONDITION_DAY,
	 * BLOG_CONDITION_POST,
	 * BLOG_CONDITION_POST_ID,
	 * BLOG_CONDITION_PART
	 *
	 * @param string $key refer constants above for valid values
	 *
	 * @return string|null returns null if key is invalid
	 */
	protected function get_condition_value($key) {

		return isset($this->conditions[$key]) ? $this->conditions[$key] : null;
	}

	/**
	 * Adds a new condition value to the list of conditions,
	 * Adds only if key is valid.
	 * can manually override conditions, coming from tag OR url
	 * note: this is mostly used to reuse certain views Eg. featured_posts is actually a reuse of category_post
	 */
	public function add_condition($key, $value) {

		return $this->set_condition_value($key, $value);
	}

	/**
	 * can manually override conditions, coming from tag OR url
	 * note: this is mostly used to reuse certain views Eg. featured_posts is actually a reuse of category_post
	 */
	protected function set_condition_value($key, $value) {

		$success = false;

		// if key is a valid condition key, then set condition value
		if (in_array($key, $this->allowedConditions)) {
			$this->conditions[$key] = $value;
			$success = true;
		}

		return $success;
	}

	public function set_conditions($conditions) {
		$this->conditions = $conditions;
	}

	public function get_viewKeyword() {
		return $this->viewKeyword;
	}

	public function set_viewKeyword($viewKeyword) {
		$this->viewKeyword = $viewKeyword;
	}

	public function get_excerptWordCount() {
		return $this->excerptWordCount;
	}

	public function set_excerptWordCount($excerptWordCount) {
		$this->excerptWordCount = $excerptWordCount;
	}

	protected function get_limit() {
		return $this->limit;
	}

	protected function set_limit($limit) {
		$this->limit = $limit;
	}

	public function get_error() {
		return $this->error;
	}

	public function set_error($error) {
		$this->error = $error;
	}

	//------------ END Setter & Getters ----------------

	public function reset() {

		$this->postId = null;
		$this->postSlug = null;
		$this->conditions = array();
		$this->viewKeyword = null;
		$this->tags = array();

//                $this->type = null;
//                $this->groupType = null;

		$this->error = null;
	}

//        public function initialize($type, $groupType) {
//
//                $success = false;
//
//                if ($this->set_type($type)) {
//                        $this->set_groupType($groupType);
//
//                        $success = true;
//                }
//
//                return $success;
//        }

	/**
	 * Converts post body to excerpt format
	 * - removes html
	 * - limits to $wordCount number of words
	 *
	 * @param string $string
	 * @param int $wordCount
	 *
	 * @return string
	 */
	protected function get_excerpt($string, $wordCount = null) {

		$allowedTags = '<br>';

		$wordCount = is_null($wordCount) ? $this->excerptWordCount : $wordCount;

		// remove html from string
		$string = strip_tags($string, $allowedTags);

		// get first $wordcount words from string
		$this->ci->load->helper('text');
		$string = word_limiter($string, $wordCount);

		// remove shortcodes from excerpt
		$parseData = array(
		    BLOG_SHORTCODE_AD => null
		);

		$string = $this->ci->parser->parse_string($string, $parseData, true);

		return $string;
	}

	/**
	 * Calculates correct DB offset using pageNo and limit
	 * note: page 0, page 1 are considered as same and have the same results ( i.e. the first 'limit' rows )
	 * hence page 2 is actually the next set of rows
	 *
	 * @param int $pageNo page number obtained from url OR from block tag
	 * @param int|null $limit number of rows to obtain at one time, note : null implies all rows after offset
	 *
	 * @return int|null
	 */
	protected function get_db_offset($pageNo, $limit = null) {

		// if pageNo is null OR 0 OR 1 OR lesser than 1, set pageNo to null ( default in database query )
		// subtract 1 from page since page 0 and page 1 is same
		$pageNo = ( is_null($pageNo) OR $pageNo <= 1 ) ? null : $pageNo - 1;
		$limit = is_null($limit) ? $this->get_limit() : $limit;

		return $pageNo * $limit;
	}

	/**
	 * Gets first element in 'delimiter' separated string
	 */
	public function get_first_element($string, $delimiter = ',') {

		$array = explode($delimiter, $string);
		return isset($array[0]) ? $array[0] : null;
	}

	public function get_date_format($date, $type) {

		$return = null;

		switch ($type) {
			case BLOG_CONDITION_YEAR:

				$return = $this->ci->date_time->format($date, "Y");
				break;
			case BLOG_CONDITION_MONTH:

				$return = $this->ci->date_time->format($date, "m");
				break;
			case BLOG_CONDITION_DAY:

				$return = $this->ci->date_time->format($date, "j");
				break;


			default:
				$return = null;
				break;
		}

		return $return;
	}

	/**
	 * Get list of post meta tags in key value pairs, arranged by postIds
	 * sets keys from allowed_post_meta_keys setting,
	 * if key not provided in databse, sets as null by default
	 *
	 * Note tag keywords are same as key from database.
	 * Hence if key = 'header', then tag to use is {header}
	 *
	 *
	 * @param int|array $postIds single postId OR list of postIds
	 *
	 * @return array|null list of metadata values arranged by post id
	 */
	protected function get_post_meta_tags($postIds) {

		$tags = null;
		$blankMeta = null;

		$postIds = is_array($postIds) ? $postIds : array($postIds);

		// get list of valid meta keys from config file
		$metaKeys = $this->ci->config->item('allowed_post_meta_keys', $this->configFile);
		foreach ($metaKeys as $key) {
			$blankMeta[$key] = null;
		}

		/**
		 * expected value
		 * meta = array(
		 *      [id1] => array(
		 *                      'key1' => 'value1',
		 *                      'key2' => 'value2',
		 *                      'key3' => 'value3',
		 *                  ),
		 *      [id2] => array(
		 *                      'key1' => 'value1',
		 *                      'key2' => 'value2',
		 *                      'key3' => 'value3',
		 *                  ),
		 *      [id3] => array(
		 *                      'key1' => 'value1',
		 *                      'key2' => 'value2',
		 *                      'key3' => 'value3',
		 *                  )
		 * )
		 */
		$metaList = $this->ci->blog_data_model->get_post_meta($postIds);

		// run through each postId, add tags according to postId
		// add blank default tags
		// replace with values from db if found
		foreach ($postIds as $postId) {

			// add dummy empty tags to tag list, will later be replace if values found
			$tags[$postId] = $blankMeta;

			if (isset($metaList[$postId]) AND is_array($metaList[$postId])) {

				// overrite blank ( default ) values with values from database
				$tags[$postId] = array_merge($tags[$postId], $metaList[$postId]);
			}
		}

		return $tags;
	}

	//*         * ***************** Begin Default View Tag Generators ********************** */

	/**
	 * Generates tags for a single post display
	 * also concatinates post meta data with regular tags.
	 * post meta tags are same as their actual keyword names in database
	 */
	protected function view_single_post_BACKUP() {

		$success = false;

		$tags = array();

//                if( $this->ci->blog_data_model->check_post_exists($conditions, $allowHidden = false) ){
//
//                }
//                else{
//                        // post does not exist with given conditions
//                }
		// get post slug/id from conditions set.
		$postId = $this->get_condition_value(BLOG_CONDITION_POST_ID);
		$postSlug = $this->get_condition_value(BLOG_CONDITION_POST);

		// gets post data either by slug OR id
		$post = $this->ci->blog_data_model->get_single_post($postSlug, $postId, $allowHidden = false);

		// check if data available
		if (!is_null($post) AND count($post) > 0) {

			// prepare conditions to generate post url ( wherever required )
			$conditions = array(
			    BLOG_CONDITION_POST_ID => $post['id'],
			    BLOG_CONDITION_POST => $post['slug'],
			    BLOG_CONDITION_AUTHOR => $post['author_username'],
			    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
			    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
			    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
			    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
			    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
			);


			$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

			// generate Categ lists with respective url ( eg. /blog/category/categ_name )
			$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
			$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
			$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

			// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
			$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
			$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
			$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);

			$tags['id'] = isset($post['id']) ? $post['id'] : null;
			$tags['title'] = isset($post['title']) ? $post['title'] : null;
			$tags['slug'] = isset($post['slug']) ? $post['slug'] : null;
			$tags['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
			$tags['body'] = isset($post['body']) ? $post['body'] : null;
			$tags['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
			$tags['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
			$tags['url'] = site_url($postUri);
			// ----------------------------------------------------------------------------
			$tags['categ:list'] = $categList;
			$tags['tag:list'] = $tagList;
			$tags['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
			$tags['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
			$tags['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
			// PENDING $tags['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
			// PENDING $tags['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;
			//
                        // get post meta data
			$postId = $post['id'];
			$meta = $this->get_post_meta_tags($postId);
			if (isset($meta[$postId]) AND is_array($meta[$postId])) {

				// merge tags, NOTE: original tags will replace meta_tags in case of conflict
				$tags = array_merge($meta[$postId], $tags);
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags($tags);
			$success = true;
		} else {
			$success = false;
			$this->set_error("No Post found");
		}

		return $success;
	}

	protected function view_summary() {

		$this->set_error('summary view in testing');
		return false;
	}

	protected function view_author() {

		$this->set_error('author view in testing');
		return false;
	}

	/**
	 * Generates tags for a single post display
	 * also concatinates post meta data with regular tags.
	 * post meta tags are same as their actual keyword names in database
	 */
	protected function view_single_post() {

		$success = false;

		$tags = array();

		// check if all conditions of permalink are satisfied, if not throw error, 404
		if ($this->ci->blog_data_model->check_post_exists($this->conditions, $allowHidden = false)) {

			// get post slug/id from conditions set.
			$postId = $this->get_condition_value(BLOG_CONDITION_POST_ID);
			$postSlug = $this->get_condition_value(BLOG_CONDITION_POST);

			// gets post data either by slug OR id
			$post = $this->ci->blog_data_model->get_single_post($postSlug, $postId, $allowHidden = false);

			// check if data available
			if (!is_null($post) AND count($post) > 0) {

				// prepare conditions to generate post url ( wherever required )
				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);


				$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

				// generate Categ lists with respective url ( eg. /blog/category/categ_name )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);

				$tags['id'] = isset($post['id']) ? $post['id'] : null;
				$tags['title'] = isset($post['title']) ? $post['title'] : null;
				$tags['slug'] = isset($post['slug']) ? $post['slug'] : null;
				$tags['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
				$tags['body'] = isset($post['body']) ? $post['body'] : null;
				$tags['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
				$tags['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
				$tags['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags['categ:list'] = $categList;
				$tags['tag:list'] = $tagList;
				$tags['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
				$tags['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
				$tags['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;
				//
				// merge date tags for different date formats
				$tags = array_merge(get_date_tags($post['created']), $tags);


				// get post meta data
				$postId = $post['id'];
				$meta = $this->get_post_meta_tags($postId);
				if (isset($meta[$postId]) AND is_array($meta[$postId])) {

					// merge tags, NOTE: original tags will replace meta_tags in case of conflict
					$tags = array_merge($meta[$postId], $tags);
				}

				// save ( load ) $tags into $this->tags
				$this->set_tags($tags);
				$success = true;
			} else {
				$success = false;
				$this->set_error("Post not found");
			}
		} else {
			// post does not exist with given conditions
			$success = false;
			$this->set_error("Post not found");
		}



		return $success;
	}

	// create tags for keyword = category_posts
	protected function view_category_posts() {

		$success = false;

		$tags = array();

		$category = $this->get_condition_value(BLOG_CONDITION_CATEGORY);
		$pageNo = $this->get_condition_value(BLOG_CONDITION_PART);

		$data = $this->ci->blog_data_model->get_posts_by_categ($category, $this->limit, $this->get_db_offset($pageNo));

		if (!is_null($data)) {

			// get post Ids for individual post meta data

			$postIds = null;
			foreach ($data as $post) {
				$postIds[] = $post['id'];
			}

			// get post meta data for all posts in given category
			// note: meta is arranged by postIds
			$postMeta = $this->get_post_meta_tags($postIds);

			$count = 0;
			// run through each post, save in appropriate tag
			foreach ($data as $post) {

				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);

				$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

				// generate Categ lists with respective url ( eg. /blog/category/categ_name )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);


				$tags[$count]['id'] = isset($post['id']) ? $post['id'] : null;
				$tags[$count]['title'] = isset($post['title']) ? $post['title'] : null;
				$tags[$count]['slug'] = isset($post['slug']) ? $post['slug'] : null;
				$tags[$count]['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
				$tags[$count]['body'] = isset($post['body']) ? $post['body'] : null;
				$tags[$count]['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
				$tags[$count]['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
				$tags[$count]['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags[$count]['categ:list'] = $categList;
				$tags[$count]['tag:list'] = $tagList;
				$tags[$count]['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
				$tags[$count]['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
				$tags[$count]['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags[$count]['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags[$count]['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;

				// merge date tags for different date formats
				$tags[$count] = array_merge(get_date_tags($post['created']), $tags[$count]);

				$postId = $post['id'];
				if (isset($postMeta[$postId]) AND is_array($postMeta[$postId])) {

					// merge tags, NOTE: original tags will replace meta_tags in case of conflict
					$tags[$count] = array_merge($postMeta[$postId], $tags[$count]);
				}

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('posts' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No Posts found");
		}

		return $success;
	}

	// create tags for keyword = tag_posts
	protected function view_tag_posts() {

		$success = false;

		$tags = array();

		// get tag value
		$tagSlug = $this->get_condition_value(BLOG_CONDITION_TAG);
		$pageNo = $this->get_condition_value(BLOG_CONDITION_PART);

		$data = $this->ci->blog_data_model->get_posts_by_tag($tagSlug, $this->limit, $this->get_db_offset($pageNo));

		if (!is_null($data)) {

			// get post Ids for individual post meta data

			$postIds = null;
			foreach ($data as $post) {
				$postIds[] = $post['id'];
			}

			// get post meta data for all posts in given category
			// note: meta is arranged by postIds
			$postMeta = $this->get_post_meta_tags($postIds);

			$count = 0;

			// run through each post, save in appropriate tag
			foreach ($data as $post) {

				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);

				$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

				// generate Categ lists with respective url ( eg. /blog/category/categ_name )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);


				$tags[$count]['id'] = isset($post['id']) ? $post['id'] : null;
				$tags[$count]['title'] = isset($post['title']) ? $post['title'] : null;
				$tags[$count]['slug'] = isset($post['slug']) ? $post['slug'] : null;
				$tags[$count]['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
				$tags[$count]['body'] = isset($post['body']) ? $post['body'] : null;
				$tags[$count]['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
				$tags[$count]['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
				$tags[$count]['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags[$count]['categ:list'] = $categList;
				$tags[$count]['tag:list'] = $tagList;
				$tags[$count]['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
				$tags[$count]['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
				$tags[$count]['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags[$count]['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags[$count]['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;

				//
				// merge date tags for different date formats
				$tags[$count] = array_merge(get_date_tags($post['created']), $tags[$count]);

				$postId = $post['id'];
				if (isset($postMeta[$postId]) AND is_array($postMeta[$postId])) {

					// merge tags, NOTE: original tags will replace meta_tags in case of conflict
					$tags[$count] = array_merge($postMeta[$postId], $tags[$count]);
				}

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('posts' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No Posts found");
		}

		return $success;
	}

	// create tags for keyword = author_posts
	protected function view_author_posts() {

		$success = false;

		$tags = array();

		// get tag value
		$author = $this->get_condition_value(BLOG_CONDITION_AUTHOR);

		// set offset from conditions
		$pageNo = $this->get_condition_value(BLOG_CONDITION_PART);
		$data = $this->ci->blog_data_model->get_posts_by_author($author, $this->limit, $this->get_db_offset($pageNo));

		if (!is_null($data)) {

			// get post Ids for individual post meta data

			$postIds = null;
			foreach ($data as $post) {
				$postIds[] = $post['id'];
			}

			// get post meta data for all posts in given category
			// note: meta is arranged by postIds
			$postMeta = $this->get_post_meta_tags($postIds);

			$count = 0;

			// run through each post, save in appropriate tag
			foreach ($data as $post) {

				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);

				// get correct uri for each post.
				$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

				// generate Categ lists with respective url ( eg. /blog/category/categ_name )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);

				$tags[$count]['id'] = isset($post['id']) ? $post['id'] : null;
				$tags[$count]['title'] = isset($post['title']) ? $post['title'] : null;
				$tags[$count]['slug'] = isset($post['slug']) ? $post['slug'] : null;
				$tags[$count]['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
				$tags[$count]['body'] = isset($post['body']) ? $post['body'] : null;
				$tags[$count]['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
				$tags[$count]['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
				$tags[$count]['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags[$count]['categ:list'] = $categList;
				$tags[$count]['tag:list'] = $tagList;
				$tags[$count]['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
				$tags[$count]['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
				$tags[$count]['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags[$count]['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags[$count]['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;

				//
				// merge date tags for different date formats
				$tags[$count] = array_merge(get_date_tags($post['created']), $tags[$count]);

				$postId = $post['id'];
				if (isset($postMeta[$postId]) AND is_array($postMeta[$postId])) {

					// merge tags, NOTE: original tags will replace meta_tags in case of conflict
					$tags[$count] = array_merge($postMeta[$postId], $tags[$count]);
				}

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('posts' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No Posts found");
		}

		return $success;
	}

	// create tags for keyword = date_posts
	protected function view_date_posts() {

		$success = false;

		$tags = array();

		// get tag value
		$year = $this->get_condition_value(BLOG_CONDITION_YEAR);
		$month = $this->get_condition_value(BLOG_CONDITION_MONTH);
		$day = $this->get_condition_value(BLOG_CONDITION_DAY);
		$pageNo = $this->get_condition_value(BLOG_CONDITION_PART);

		$data = $this->ci->blog_data_model->get_posts_by_date($year, $month, $day, $this->limit, $this->get_db_offset($pageNo));

		if (!is_null($data)) {

			// get post Ids for individual post meta data

			$postIds = null;
			foreach ($data as $post) {
				$postIds[] = $post['id'];
			}

			// get post meta data for all posts in given category
			// note: meta is arranged by postIds
			$postMeta = $this->get_post_meta_tags($postIds);

			$count = 0;

			// run through each post, save in appropriate tag
			foreach ($data as $post) {

				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_AUTHOR => $post['author_username'],
				    BLOG_CONDITION_CATEGORY => $this->get_first_element($post['category_slugs']),
				    BLOG_CONDITION_TAG => $this->get_first_element($post['tag_slugs']),
				    BLOG_CONDITION_YEAR => $this->get_date_format($post['created'], BLOG_CONDITION_YEAR),
				    BLOG_CONDITION_MONTH => $this->get_date_format($post['created'], BLOG_CONDITION_MONTH),
				    BLOG_CONDITION_DAY => $this->get_date_format($post['created'], BLOG_CONDITION_DAY)
				);

				// get correct uri for each post.
				$postUri = $this->ci->blog_url_gen_lib->get_uri_post($conditions);

				// generate Categ lists with respective url ( eg. /blog/category/categ_name )
				$categSlugArray = !is_null($post['category_slugs']) ? explode(',', $post['category_slugs']) : null;
				$categNameArray = !is_null($post['categories']) ? explode(',', $post['categories']) : null;
				$categList = $this->ci->blog_url_gen_lib->get_categ_urls($categSlugArray, $categNameArray, $categNameArray);

				// generate Tag lists with respective url ( eg. /blog/tag/tag_name )
				$tagSlugArray = !is_null($post['tag_slugs']) ? explode(',', $post['tag_slugs']) : null;
				$tagNameArray = !is_null($post['tags']) ? explode(',', $post['tags']) : null;
				$tagList = $this->ci->blog_url_gen_lib->get_tag_urls($tagSlugArray, $tagNameArray, $tagNameArray);

				$tags[$count]['id'] = isset($post['id']) ? $post['id'] : null;
				$tags[$count]['title'] = isset($post['title']) ? $post['title'] : null;
				$tags[$count]['slug'] = isset($post['slug']) ? $post['slug'] : null;
				$tags[$count]['excerpt'] = isset($post['body']) ? $this->get_excerpt($post['body']) : null;
				$tags[$count]['body'] = isset($post['body']) ? $post['body'] : null;
				$tags[$count]['created'] = isset($post['created']) ? $this->ci->date_time->date($post['created']) : null;
				$tags[$count]['updated'] = isset($post['updated']) ? $this->ci->date_time->date($post['updated']) : null;
				$tags[$count]['url'] = site_url($postUri);
				// ----------------------------------------------------------------------------
				$tags[$count]['categ:list'] = $categList;
				$tags[$count]['tag:list'] = $tagList;
				$tags[$count]['author:username'] = isset($post['author_username']) ? $post['author_username'] : null;
				$tags[$count]['author:name'] = isset($post['author_name']) ? $post['author_name'] : null;
				$tags[$count]['author:url'] = site_url($this->ci->blog_url_gen_lib->get_uri_author($post['author_username']));
				// PENDING $tags[$count]['comment:count'] = isset( $post['id'] ) ? $post['id'] : null;
				// PENDING $tags[$count]['comment:url'] = isset( $post['id'] ) ? $post['id'] : null;

				//
				// merge date tags for different date formats
				$tags[$count] = array_merge(get_date_tags($post['created']), $tags[$count]);

				$postId = $post['id'];
				if (isset($postMeta[$postId]) AND is_array($postMeta[$postId])) {

					// merge tags, NOTE: original tags will replace meta_tags in case of conflict
					$tags[$count] = array_merge($postMeta[$postId], $tags[$count]);
				}

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('posts' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No Posts found");
		}

		return $success;
	}

	// create tags for featured posts
	protected function view_featured_posts() {

		// set conditions to 'category' = 'featured'
		$this->set_condition_value(BLOG_CONDITION_CATEGORY, BLOG_CATEG_FEATURED);

		// call default view for category posts
		return $this->view_category_posts();
	}

	// create tags for latest posts
	protected function view_latest_posts() {

		// set conditions to 'year' = null, 'month' = null, 'day' = null,
		// doing this will get latest posts instead of year,month,day conditions
		$this->set_condition_value(BLOG_CONDITION_YEAR, null);
		$this->set_condition_value(BLOG_CONDITION_MONTH, null);
		$this->set_condition_value(BLOG_CONDITION_DAY, null);

		// call default view for category posts
		return $this->view_date_posts();
	}

	// create tags for latest posts
	protected function view_archives() {

		$success = false;

		$tags = array();

		$data = $this->ci->blog_data_model->get_archives($isLatestFirst = true, $limit = null, $offset = null);

		if (!is_null($data)) {

			$count = 0;

			// run through each row, save in appropriate tag
			foreach ($data as $row) {

				$year = isset($row['year']) ? $row['year'] : null;
				$month = isset($row['month']) ? $row['month'] : null;

				// convert full month name to numerical month Eg. ( convert January to 1 )
				// NOTE: converting to monthNumber without leading zeroes
				// ref: http://in2.php.net/manual/en/function.date.php
				$monthNum = date('n', strtotime("$year-$month"));

				$tags[$count]['year'] = $year;
				$tags[$count]['month'] = $month;
				$tags[$count]['count'] = isset($row['count']) ? $row['count'] : null;
				$tags[$count]['url'] = site_url($this->ci->blog_url_gen_lib->get_uri_date($row['year'], $monthNum));

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('archives' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No archives found");
		}

		return $success;
	}

	// create tags for latest posts
	protected function view_archives_categorized() {

		$success = false;

		$tags = array();

		$archives = $this->ci->blog_data_model->get_archives($isLatestFirst = true, $limit = null, $offset = null);

		if (!is_null($archives)) {

			// group data by year
			$years = rearrange_array($archives, 'year', true); // $multiDimension = true
//                        $count = 0;
			$yearTags = null;

			// run through each year,
			// process all months under each year,
			// generate tags for each year,
			// add year tags to main tags
			foreach ($years as $year => $data) {

				$yearCount = 0;
				$monthTags = null;

				// run through each month data
				// create tags for each month, generate list of month tags
				foreach ($data as $row) {

					$month = isset($row['month']) ? $row['month'] : null;
					$monthCount = isset($row['count']) ? $row['count'] : null;
					$yearCount = $yearCount + $monthCount;

					// convert full month name to numerical month Eg. ( convert January to 1 )
					// NOTE: converting to monthNumber without leading zeroes
					// ref: http://in2.php.net/manual/en/function.date.php
					$monthNum = date('n', strtotime("$year-$month"));

					$monthTags[] = array(
					    'month' => $month,
					    'month:count' => $monthCount,
					    'month:url' => site_url($this->ci->blog_url_gen_lib->get_uri_date($year, $monthNum))
					);
				}

				$yearTags = array(
				    'year' => $year,
				    'year:count' => $yearCount,
				    'year:url' => site_url($this->ci->blog_url_gen_lib->get_uri_date($row['year'])),
				    'months' => $monthTags,
				);

				$tags[] = $yearTags;

//                                $count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('archives' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No archives found");
		}

		return $success;
	}

	// create tags for categories
	// note: special categories are NOT included ( i.e. 'uncategorized', 'featured' )
	protected function view_categories() {

		$success = false;

		$tags = array();

//                $pageNo = $this->get_condition_value(BLOG_CONDITION_PART);
//                $data = $this->ci->blog_data_model->get_categories( $this->limit, $this->get_db_offset($pageNo, $this->limit) );
		$data = $this->ci->blog_data_model->get_categories();

		if (!is_null($data)) {

			$count = 0;

			// run through each categ, save in appropriate tag
			foreach ($data as $categ) {

				$tags[$count]['id'] = $categ['id'];
				$tags[$count]['name'] = $categ['name'];
				$tags[$count]['slug'] = $categ['slug'];
				$tags[$count]['excerpt'] = $this->get_excerpt($categ['body']);
				$tags[$count]['body'] = $categ['body'];
				$tags[$count]['created'] = $this->ci->date_time->date($categ['created']);
				$tags[$count]['updated'] = $this->ci->date_time->date($categ['updated']);
				$tags[$count]['count'] = $categ['count'];
				$tags[$count]['url'] = site_url($this->ci->blog_url_gen_lib->get_uri_categ($categ['slug']));

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('categories' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No categories found");
		}

		return $success;
	}

	// create tags for 'tags' ( i.e. tag cloud )
	protected function view_tags() {

		$success = false;

		$tags = array();

//                $pageNo = $this->get_condition_value(BLOG_CONDITION_PART);
//                $data = $this->ci->blog_data_model->get_categories( $this->limit, $this->get_db_offset($pageNo, $this->limit) );
		$data = $this->ci->blog_data_model->get_tags();

		if (!is_null($data)) {

			$count = 0;

			// run through each categ, save in appropriate tag
			foreach ($data as $tag) {

				$tags[$count]['id'] = $tag['id'];
				$tags[$count]['name'] = $tag['name'];
				$tags[$count]['slug'] = $tag['slug'];
				$tags[$count]['excerpt'] = $this->get_excerpt($tag['body']);
				$tags[$count]['body'] = $tag['body'];
				$tags[$count]['count'] = $tag['count'];
				$tags[$count]['url'] = site_url($this->ci->blog_url_gen_lib->get_uri_tag($tag['slug']));

				$count++;
			}

			// save ( load ) $tags into $this->tags
			$this->set_tags(array('tags' => $tags));
			$success = true;
		} else {
			$success = false;
			$this->set_error("No tags found");
		}

		return $success;
	}

	protected function view_pagination() {

		/**
		 * Check if group type OR single post
		 * IF  single post
		 * -
		 *
		 * ELSE group
		 * - get conditions,
		 * - set_counter_flag = true,
		 * - call get_row_count( $conditions )
		 * - calculate prev page, next page
		 * - set tags
		 * - exit
		 *
		 */
		$success = false;

		// get post slug/id from conditions set.
		$postId = $this->get_condition_value(BLOG_CONDITION_POST_ID);
		$postSlug = $this->get_condition_value(BLOG_CONDITION_POST);

		if (isset($postId) OR isset($postSlug)) {

			// type post, get previous and next post titles and slugs
			// create urls from slug
			$success = $this->view_pagination_post();
		} else {

			$success = $this->view_pagination_group();
		}

		return $success;
	}

	/**
	 * Gets gets tags for pagination for single post
	 * note: this is NOT a regular viewKeyword,
	 * it is called from view_pagination() depending on url provided
	 */
	protected function view_pagination_post() {

		$success = false;

		$message = null;
		$tags = null;

		// get post slug/id from conditions set.
		$postId = $this->get_condition_value(BLOG_CONDITION_POST_ID);
		$postSlug = $this->get_condition_value(BLOG_CONDITION_POST);

		$data = $this->ci->blog_data_model->get_pre_next_post($postId, $postSlug);

		if (!is_null($data)) {

			$uri = null;
			$title = null;

			// run through result
			foreach ($data as $post) {

				// get position ( prev OR next )
				$position = $post['position'];

				$conditions = array(
				    BLOG_CONDITION_POST_ID => $post['id'],
				    BLOG_CONDITION_POST => $post['slug'],
				    BLOG_CONDITION_CATEGORY => $post['category'],
				    BLOG_CONDITION_TAG => $post['tag'],
				    BLOG_CONDITION_AUTHOR => $post['author'],
				    BLOG_CONDITION_YEAR => date('Y', strtotime($post['created'])),
				    BLOG_CONDITION_MONTH => date('n', strtotime($post['created'])),
				    BLOG_CONDITION_DAY => date('j', strtotime($post['created']))
				);

				$title[$position] = $post['title'];
				$uri[$position] = $this->ci->blog_url_gen_lib->get_uri_post($conditions);
			}

			$tags = array(
			    'prev:title' => isset($title['prev']) ? "Previous : " . $title['prev'] : null,
			    'prev:url' => isset($uri['prev']) ? site_url($uri['prev']) : null,
			    'next:title' => isset($title['next']) ? "Next : " . $title['next'] : null,
			    'next:url' => isset($uri['next']) ? site_url($uri['next']) : null
			);

			$success = true;
		} else {

			// no prev or next post found
			// post might NOT be present, it might be draft OR no posts in database
			$message = '';
			$success = false;
		}

		// save ( load ) $tags into $this->tags
		$this->set_tags($tags);
		$this->set_error($message);

		return $success;
	}

	/**
	 * Gets gets tags for pagination for group type query
	 * note: this is NOT a regular viewKeyword,
	 * it is called from view_pagination() depending on url provided
	 */
	protected function view_pagination_group() {

		// conditions type = group, get previous page and next page

		$success = false;
		$message = null;
		$totalCount = 0;

		$tags = null;

		// will hold the base uri for any particular group type,
		// note: page number ( next, prev ) should be concatinated at the end after calculating
		$baseUri = null;

		// get url conditions
		$conditions = $this->get_conditions();

		// check for conditions, run respective models for to generate count
		// by date
		if (isset($conditions[BLOG_CONDITION_YEAR])) {

			$year = $conditions[BLOG_CONDITION_YEAR];
			$month = $conditions[BLOG_CONDITION_MONTH];
			$day = $conditions[BLOG_CONDITION_DAY];

//			$result = $this->ci->blog_data_model->get_posts_by_date($year, $month, $day);
			$totalCount = $this->ci->blog_data_model->get_total_rows('get_posts_by_date');
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_date($year, $month, $day);
			$success = true;
		}
		// by category
		elseif (isset($conditions[BLOG_CONDITION_CATEGORY])) {

			$categ = $conditions[BLOG_CONDITION_CATEGORY];
//			$result = $this->ci->blog_data_model->get_posts_by_categ($categ);
			$totalCount = $this->ci->blog_data_model->get_total_rows('get_posts_by_categ');
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_categ($categ);
			$success = true;
		}
		// by tag
		elseif (isset($conditions[BLOG_CONDITION_TAG])) {

			$tagSlug = $conditions[BLOG_CONDITION_TAG];
//			$result = $this->ci->blog_data_model->get_posts_by_tag($tagSlug);
			$totalCount = $this->ci->blog_data_model->get_total_rows('get_posts_by_tag');
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_tag($tagSlug);
			$success = true;
		}
		// by author
		elseif (isset($conditions[BLOG_CONDITION_AUTHOR])) {

			$author = $conditions[BLOG_CONDITION_AUTHOR];
//			$result = $this->ci->blog_data_model->get_posts_by_author($author);
			$totalCount = $this->ci->blog_data_model->get_total_rows('get_posts_by_author');
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_author($author);
			$success = true;
		}
		// not among 4 group types
		else {
			// NOT a group type
			// error
			$success = false;
		}

		if ($success) {

			$prev = null;
			$next = null;
			$maxPage = null;

			$currPageNo = (int) $this->get_condition_value(BLOG_CONDITION_PART);
			$limit = $this->get_limit();

			// check if
			// - no. of rows per view < total no of rows
			// - valid limit and totalCount ( note: $limit $ totalcount will always be > 0 due to settings and conditions of blog )
			if ($limit < $totalCount AND $limit > 0 AND $totalCount > 0) {

				// get maximum number of pages allowed, convert decimal to upper integer.
				$maxPage = (int) ceil($totalCount / $limit);

				// che if current page no. is +ve and lesser than maxPage count, if NOT 404 error
				if ($currPageNo <= $maxPage AND $currPageNo >= 0) {

					// if prev page is less than 1, then set to null ( previous page NOT required )
					$prevPage = ($currPageNo - 1) <= 1 ? null : $currPageNo - 1;

					// check next page lower limit conditions
					// lower limit should start from 2, as page 0 and page 1 are same as page 1
//                                        $next = ( $currPageNo <= 1 ) ? 2 : $currPageNo + 1;
					$nextPage = ( $currPageNo + 1 ) <= 1 ? 2 : $currPageNo + 1;

					// set next page upper limit
					// if nextpage is greater than maxpages then nextpage = maxpage
					$nextPage = $nextPage > $maxPage ? null : $nextPage;


					$tags = array(
					    'prev:title' => ($currPageNo - 1) < 1 ? null : 'Previous',
					    'prev:url' => is_null($prevPage) ? site_url($baseUri) : site_url("$baseUri/page/$prevPage"),
					    'next:title' => is_null($nextPage) ? null : 'Next',
					    'next:url' => is_null($nextPage) ? null : site_url("$baseUri/page/$nextPage")
					);
					$success = true;
				} else {
					// 404 error
					$message = "";
					$success = false;
				}
			} else {
				// single page, pagination NOT needed, No error
				$message = "";
				$success = false;
			}
		}

		// save ( load ) $tags into $this->tags
		$this->set_tags($tags);
		$this->set_error($message);

		return $success;
	}
	protected function view_pagination_group_BACKUP() {

		// conditions type = group, get previous page and next page

		$success = false;
		$message = null;

		$tags = null;

		// will hold the base uri for any particular group type,
		// note: page number ( next, prev ) should be concatinated at the end after calculating
		$baseUri = null;

		// get url conditions
		$conditions = $this->get_conditions();

		// setting this will delete $select statements to get dummy results
		$this->ci->blog_data_model->set_counter_flag(true);

		// check for conditions, run respective models for to generate count
		// by date
		if (isset($conditions[BLOG_CONDITION_YEAR])) {

			$year = $conditions[BLOG_CONDITION_YEAR];
			$month = $conditions[BLOG_CONDITION_MONTH];
			$day = $conditions[BLOG_CONDITION_DAY];

			$result = $this->ci->blog_data_model->get_posts_by_date($year, $month, $day);
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_date($year, $month, $day);
			$success = true;
		}
		// by category
		elseif (isset($conditions[BLOG_CONDITION_CATEGORY])) {

			$categ = $conditions[BLOG_CONDITION_CATEGORY];
			$result = $this->ci->blog_data_model->get_posts_by_categ($categ);
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_categ($categ);
			$success = true;
		}
		// by tag
		elseif (isset($conditions[BLOG_CONDITION_TAG])) {

			$tagSlug = $conditions[BLOG_CONDITION_TAG];
			$result = $this->ci->blog_data_model->get_posts_by_tag($tagSlug);
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_tag($tagSlug);
			$success = true;
		}
		// by author
		elseif (isset($conditions[BLOG_CONDITION_AUTHOR])) {

			$author = $conditions[BLOG_CONDITION_AUTHOR];
			$result = $this->ci->blog_data_model->get_posts_by_author($author);
			$baseUri = $this->ci->blog_url_gen_lib->get_uri_author($author);
			$success = true;
		}
		// not among 4 group types
		else {
			// NOT a group type
			// error
			$success = false;
		}

		// reset counter flag
		$this->ci->blog_data_model->set_counter_flag(false);

		// run respective database query ( without actual data, only dummy queries ),
		// get actual number of rows
		$totalCount = $this->ci->blog_data_model->get_row_count();

		if ($success) {

			$prev = null;
			$next = null;
			$maxPage = null;

			$currPageNo = (int) $this->get_condition_value(BLOG_CONDITION_PART);
			$limit = $this->get_limit();

			// check if
			// - no. of rows per view < total no of rows
			// - valid limit and totalCount ( note: $limit $ totalcount will always be > 0 due to settings and conditions of blog )
			if ($limit < $totalCount AND $limit > 0 AND $totalCount > 0) {

				// get maximum number of pages allowed, convert decimal to upper integer.
				$maxPage = (int) ceil($totalCount / $limit);

				// che if current page no. is +ve and lesser than maxPage count, if NOT 404 error
				if ($currPageNo <= $maxPage AND $currPageNo >= 0) {

					// if prev page is less than 1, then set to null ( previous page NOT required )
					$prevPage = ($currPageNo - 1) <= 1 ? null : $currPageNo - 1;

					// check next page lower limit conditions
					// lower limit should start from 2, as page 0 and page 1 are same as page 1
//                                        $next = ( $currPageNo <= 1 ) ? 2 : $currPageNo + 1;
					$nextPage = ( $currPageNo + 1 ) <= 1 ? 2 : $currPageNo + 1;

					// set next page upper limit
					// if nextpage is greater than maxpages then nextpage = maxpage
					$nextPage = $nextPage > $maxPage ? null : $nextPage;


					$tags = array(
					    'prev:title' => ($currPageNo - 1) < 1 ? null : 'Previous',
					    'prev:url' => is_null($prevPage) ? site_url($baseUri) : site_url("$baseUri/page/$prevPage"),
					    'next:title' => is_null($nextPage) ? null : 'Next',
					    'next:url' => is_null($nextPage) ? null : site_url("$baseUri/page/$nextPage")
					);
					$success = true;
				} else {
					// 404 error
					$message = "";
					$success = false;
				}
			} else {
				// single page, pagination NOT needed, No error
				$message = "";
				$success = false;
			}
		}

		// save ( load ) $tags into $this->tags
		$this->set_tags($tags);
		$this->set_error($message);

		return $success;
	}

}

?>
