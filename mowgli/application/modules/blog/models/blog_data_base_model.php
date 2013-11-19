<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of blog_data_base_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
include_once MODULEPATH . 'blog/models/blog_model.php';

class Blog_Data_Base_Model extends Blog_Model {

	private $rowCount = null;
	private $counterFlag = false;
	private $totalRows = null; // array mapping function name with total rows of query

	public function __construct() {

		parent::__construct();

		// load config files
		$this->config->load($this->configFile, true, true, $this->module);

		!defined('BLOG_GROUP_TYPE_AUTHOR') ? define('BLOG_GROUP_TYPE_AUTHOR', $this->config->item('data_group_type_author', $this->configFile)) : null;
		!defined('BLOG_GROUP_TYPE_CATEGORY') ? define('BLOG_GROUP_TYPE_CATEGORY', $this->config->item('data_group_type_category', $this->configFile)) : null;
		!defined('BLOG_GROUP_TYPE_TAG') ? define('BLOG_GROUP_TYPE_TAG', $this->config->item('data_group_type_tag', $this->configFile)) : null;
		!defined('BLOG_GROUP_TYPE_DATE') ? define('BLOG_GROUP_TYPE_DATE', $this->config->item('data_group_type_date', $this->configFile)) : null;


		// blog permalink/url condition keys
		!defined('BLOG_CONDITION_AUTHOR') ? define('BLOG_CONDITION_AUTHOR', $this->config->item('condition_key_author', $this->configFile)) : null;
		!defined('BLOG_CONDITION_CATEGORY') ? define('BLOG_CONDITION_CATEGORY', $this->config->item('condition_key_category', $this->configFile)) : null;
		!defined('BLOG_CONDITION_TAG') ? define('BLOG_CONDITION_TAG', $this->config->item('condition_key_tag', $this->configFile)) : null;
		!defined('BLOG_CONDITION_YEAR') ? define('BLOG_CONDITION_YEAR', $this->config->item('condition_key_year', $this->configFile)) : null;
		!defined('BLOG_CONDITION_MONTH') ? define('BLOG_CONDITION_MONTH', $this->config->item('condition_key_month', $this->configFile)) : null;
		!defined('BLOG_CONDITION_DAY') ? define('BLOG_CONDITION_DAY', $this->config->item('condition_key_day', $this->configFile)) : null;
		!defined('BLOG_CONDITION_POST') ? define('BLOG_CONDITION_POST', $this->config->item('condition_key_post', $this->configFile)) : null;
		!defined('BLOG_CONDITION_POST_ID') ? define('BLOG_CONDITION_POST_ID', $this->config->item('condition_key_post_id', $this->configFile)) : null;
		!defined('BLOG_CONDITION_PART') ? define('BLOG_CONDITION_PART', $this->config->item('condition_key_part', $this->configFile)) : null;
	}

	public function get_total_rows($functionName) {

		return isset($this->totalRows[$functionName]) ? $this->totalRows[$functionName] : null;
	}

	private function _record_total_rows($functionName) {

		$this->totalRows[$functionName] = $this->total_row_count();
	}

//	public function get_row_count() {
//		return $this->rowCount;
//	}
//
//	public function set_row_count($rowCount) {
//		$this->rowCount = $rowCount;
//	}
//	public function get_counter_flag() {
//		return $this->counterFlag;
//	}
//	public function set_counter_flag($counterFlag) {
//		$this->counterFlag = $counterFlag;
//	}

	public function get_summary_data() {

	}

	/**
	 * Get meta data ( extra fields ) of single post OR multiple posts
	 * return value is an array with a list of meta data for each post id
	 *
	 * EG. of return value
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
	 *
	 * @param int|array $postIds single postId OR list of postIds
	 *
	 * @return array|null list of metadata values arranged by post id
	 */
	public function get_post_meta($postIds) {

		$data = null;

		$postIds = $this->addslashes($postIds);

		if (!is_array($postIds)) {

			$postIds = array($postIds);
		}

		$select = "
                        blog_post_meta_post_id AS id,
                        blog_post_meta_key AS `key`,
                        blog_post_meta_value AS value
                ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_post_meta']);
		$this->db->where_in('blog_post_meta_post_id', $postIds);

		$this->db->order_by('blog_post_meta_post_id', 'ASC');
		$this->db->order_by('blog_post_meta_key', 'ASC');
//                $this->db->group_by('blog_post_meta_post_id');

		$query = $this->db->get();

		$meta = null;

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			// strip slashes
			foreach ($data as & $row) {

				$postId = $row['id'];
				$key = $row['key'];
				$value = $row['value'];

				$meta[$postId][$key] = stripslashes($value);
			}
		}

		return $meta;
	}

	/**
	 * Checks if a particular post exists, for given conditions
	 * conditions are typically same as permalink Conditions
	 * if even a single condition fails, post return value is false
	 *
	 * @param array $conditions permalink conditions to check
	 * @param bool $allowHidden if drafts should be allowed or not
	 *
	 * @return bool
	 */
	public function check_post_exists($conditions, $allowHidden = false) {

		$success = false;

		$postSlug = isset($conditions[BLOG_CONDITION_POST]) ? $this->addslashes($conditions[BLOG_CONDITION_POST]) : null;
		$postId = isset($conditions[BLOG_CONDITION_POST_ID]) ? $this->addslashes($conditions[BLOG_CONDITION_POST_ID]) : null;
		$postCateg = isset($conditions[BLOG_CONDITION_CATEGORY]) ? $this->addslashes($conditions[BLOG_CONDITION_CATEGORY]) : null;
		$postTag = isset($conditions[BLOG_CONDITION_TAG]) ? $this->addslashes($conditions[BLOG_CONDITION_TAG]) : null;
		$postAuthor = isset($conditions[BLOG_CONDITION_AUTHOR]) ? $this->addslashes($conditions[BLOG_CONDITION_AUTHOR]) : null;
		$postYear = isset($conditions[BLOG_CONDITION_YEAR]) ? $this->addslashes($conditions[BLOG_CONDITION_YEAR]) : null;
		$postMonth = isset($conditions[BLOG_CONDITION_MONTH]) ? $this->addslashes($conditions[BLOG_CONDITION_MONTH]) : null;
		$postDay = isset($conditions[BLOG_CONDITION_DAY]) ? $this->addslashes($conditions[BLOG_CONDITION_DAY]) : null;
//                $postPart = isset($conditions[BLOG_CONDITION_PART]) ? $this->addslashes($conditions[BLOG_CONDITION_PART]) : null;


		$select = "
                                post.blog_post_id            AS id
                        ";

		$this->db->select($select);

		$this->db->from($this->tables['blog_posts'] . " AS post");
		$this->db->join($this->tables['blog_post_categs'] . " AS categ_rel", "post.blog_post_id = categ_rel.blog_post_categ_post_id", 'left');
		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", "tag_rel.blog_post_tag_post_id = post.blog_post_id", 'left');

		// postslug condition
		if (!is_null($postSlug)) {
			$this->db->where('post.blog_post_slug', $postSlug);
		}
		// post id condition
		if (!is_null($postId)) {
			$this->db->where('post.blog_post_id', $postId);
		}
		// author username condition
		if (!is_null($postAuthor)) {
			$this->db->where('post.blog_post_author_username', $postAuthor);
		}
		// year condition
		if (!is_null($postYear)) {
			$this->db->where("year( post.blog_post_created ) = $postYear");
		}
		// month condition
		if (!is_null($postMonth)) {
			$this->db->where("month( post.blog_post_created ) = $postMonth");
		}
		// day condition
		if (!is_null($postDay)) {
			$this->db->where("day( post.blog_post_created ) = $postDay");
		}
		// category condition
		if (!is_null($postCateg)) {
			$this->db->where('categ_rel.blog_post_categ_categ_slug', $postCateg);
		}
		// tag condition
		if (!is_null($postTag)) {
			$this->db->where('tag_rel.blog_post_tag_tag_slug', $postTag);
		}

		// check if draft should be displayed
		if ($allowHidden == false) {
			$this->db->where('post.blog_post_status', BLOG_POST_STATUS_PUBLISHED);
		}
		$this->db->limit(1);

//                $sql_before = $this->db->_compile_select();


		$query = $this->db->get();

//                        $sql_after = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$success = true;
		}

		return $success;
	}

	/**
	 * Get single post data for view
	 *
	 * @param string $postSlug slug of post
	 * @param int $postId id of post ( required only if slug not available, if not provided, uses slug by default )
	 * @param bool $allowHidden if drafts should be allowed or not
	 */
	public function get_single_post($postSlug, $postId = null, $allowHidden = false) {

		$post = null;

		$postSlug = $this->addslashes($postSlug);
		$postId = $this->addslashes($postId);

		$select = "
                        post.blog_post_id            AS id,
                        post.blog_post_title         AS title,
                        post.blog_post_slug          AS slug,
                        post.blog_post_body          AS body,
                        post.blog_post_created       AS created,
                        post.blog_post_modified      AS updated,
                        post.blog_post_is_comments   AS is_comments,

			GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

			GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,

                        author.user_auth_username      AS author_username,
                        author.user_auth_name          AS author_name
                        ";

		$this->db->select($select, $escape = false);

		$this->db->from($this->tables['blog_posts'] . " AS post");
//                $this->db->from($this->tables['blog_post_categs'] . " AS categ_rel");
		$this->db->join($this->tables['blog_post_categs'] . " AS categ_rel", "post.blog_post_id = categ_rel.blog_post_categ_post_id", 'left');
//                $this->db->join($this->tables['blog_posts'] . " AS post", "post.blog_post_id = categ_rel.blog_post_categ_post_id", 'left');
		$this->db->join($this->tables['blog_categories'] . " AS categ", "categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug", 'left');

		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", "tag_rel.blog_post_tag_post_id = post.blog_post_id", 'left');
		$this->db->join($this->tables['blog_tags'] . " AS tag", "tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug", 'left');

		$this->db->join($this->tables['author'] . " AS author", "author.user_auth_username = post.blog_post_author_username", 'left');

		// use post slug if provided, else use post id
		if (!is_null($postSlug)) {
			$this->db->where('post.blog_post_slug', $postSlug);
		} else {
			$this->db->where('post.blog_post_id', $postId);
		}
		// check if draft should be displayed
		if ($allowHidden == false) {
			$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);
		}

		// required for list of categs and tags
		$this->db->group_by('post.blog_post_id');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get();

//                        $sql_after = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$post = $query->row_array();

			// strip slashes
			foreach ($post as $key => $value) {

				$post[$key] = $this->stripslashes($value);
//                                $post[$key] = stripslashes($value);
			}
		}

		return $post;
	}

	public function get_posts_by_categ($categories, $limit = null, $offset = null) {

		/*
		  SELECT
		  SQL_CALC_FOUND_ROWS
		  post.blog_post_id AS id,
		  post.blog_post_title AS title,
		  post.blog_post_slug AS slug,
		  post.blog_post_body AS body,
		  post.blog_post_created AS created,
		  post.blog_post_modified AS updated,
		  post.blog_post_is_comments AS is_comments,
		  GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
		  GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
		  GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

		  GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
		  GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
		  GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,
		  author.user_auth_username AS author_username,
		  author.user_auth_name AS author_name

		  FROM (blog_post_categs AS categ_rel)
		  LEFT JOIN blog_posts AS post ON post.blog_post_id = categ_rel.blog_post_categ_post_id
		  LEFT JOIN blog_categories AS categ ON categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug
		  LEFT JOIN blog_post_tags AS tag_rel ON tag_rel.blog_post_tag_post_id = post.blog_post_id
		  LEFT JOIN blog_tags AS tag ON tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug
		  LEFT JOIN users_auth AS author ON author.user_auth_username = post.blog_post_author_username

		  WHERE
		  categ_rel.blog_post_categ_categ_slug IN ('featured')
		  AND `blog_post_status` = 'published'

		  GROUP BY
		  post.blog_post_id

		  ORDER BY
		  categ.blog_categ_slug ASC,
		  post.blog_post_created DESC LIMIT 20
		 */

		$posts = null;

		$categories = !is_array($categories) ? array($categories) : $categories;
		$categories = $this->addslashes($categories);

		$select = "
			SQL_CALC_FOUND_ROWS
                        post.blog_post_id            AS id,
                        post.blog_post_title         AS title,
                        post.blog_post_slug          AS slug,
                        post.blog_post_body          AS body,
                        post.blog_post_created       AS created,
                        post.blog_post_modified      AS updated,
                        post.blog_post_is_comments   AS is_comments,

                        GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

			GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,

                        author.user_auth_username      AS author_username,
                        author.user_auth_name          AS author_name
                        ";

		$this->db->select($select, $escape = false);

		$this->db->from($this->tables['blog_post_categs'] . " AS categ_rel");

		$this->db->join($this->tables['blog_posts'] . " AS post", "post.blog_post_id = categ_rel.blog_post_categ_post_id", 'left');
		$this->db->join($this->tables['blog_categories'] . " AS categ", "categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug", 'left');

		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", "tag_rel.blog_post_tag_post_id = post.blog_post_id", 'left');
		$this->db->join($this->tables['blog_tags'] . " AS tag", "tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug", 'left');

		$this->db->join($this->tables['author'] . " AS author", "author.user_auth_username = post.blog_post_author_username", 'left');

		$this->db->where_in('categ_rel.blog_post_categ_categ_slug', $categories);
		$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);

		$this->db->group_by('post.blog_post_id');

		$this->db->order_by('categ.blog_categ_slug', 'ASC');
		$this->db->order_by('post.blog_post_created', 'DESC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

		if ($query->num_rows() > 0) {

			$posts = $query->result_array();

			// strip slashes
			foreach ($posts as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}

			// record current queries total row count for pagination
			$this->_record_total_rows(__FUNCTION__);
		}

		return $posts;
	}

	public function get_posts_by_tag($tags, $limit = null, $offset = null) {

		$posts = null;

		$tags = !is_array($tags) ? array($tags) : $tags;
		$tags = $this->addslashes($tags);

		$select = "
			SQL_CALC_FOUND_ROWS
                        post.blog_post_id            AS id,
                        post.blog_post_title         AS title,
                        post.blog_post_slug          AS slug,
                        post.blog_post_body          AS body,
                        post.blog_post_created       AS created,
                        post.blog_post_modified      AS updated,
                        post.blog_post_is_comments   AS is_comments,

                        GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

			GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,

                        author.user_auth_username      AS author_username,
                        author.user_auth_name          AS author_name
                        ";

		$this->db->select($select, $escape = false);

		// main condition table ( post-tag relation )
		$this->db->from($this->tables['blog_post_tags'] . " AS tag_rel");

		// post + tag_rel
		$this->db->join($this->tables['blog_posts'] . " AS post", "post.blog_post_id = tag_rel.blog_post_tag_post_id", 'left');

		// tag + tag_rel
		$this->db->join($this->tables['blog_tags'] . " AS tag", "tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug", 'left');

		// categ_rel + post
		$this->db->join($this->tables['blog_post_categs'] . " AS categ_rel", "categ_rel.blog_post_categ_post_id = post.blog_post_id", 'left');

		// categ + categ_rel
		$this->db->join($this->tables['blog_categories'] . " AS categ", "categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug", 'left');

		$this->db->join($this->tables['author'] . " AS author", "author.user_auth_username = post.blog_post_author_username", 'left');

		$this->db->where_in('tag_rel.blog_post_tag_tag_slug', $tags);
		$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);

		$this->db->group_by('post.blog_post_id');

		$this->db->order_by('tag.blog_tag_slug', 'ASC');
		$this->db->order_by('post.blog_post_created', 'DESC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

		if ($query->num_rows() > 0) {

			$posts = $query->result_array();

			// strip slashes
			foreach ($posts as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}

			// record current queries total row count for pagination
			$this->_record_total_rows(__FUNCTION__);
		}

		return $posts;
	}

	public function get_posts_by_author($author, $limit = null, $offset = null) {

		$posts = null;

		$author = !is_array($author) ? array($author) : $author;
		$author = $this->addslashes($author);

		$select = "
			SQL_CALC_FOUND_ROWS
                        post.blog_post_id            AS id,
                        post.blog_post_title         AS title,
                        post.blog_post_slug          AS slug,
                        post.blog_post_body          AS body,
                        post.blog_post_created       AS created,
                        post.blog_post_modified      AS updated,
                        post.blog_post_is_comments   AS is_comments,

                        GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

			GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,

                        author.user_auth_username      AS author_username,
                        author.user_auth_name          AS author_name
                        ";

		$this->db->select($select, $escape = false);

		// main condition table ( author )
		$this->db->from($this->tables['author'] . " AS author");

		// post + author
		$this->db->join($this->tables['blog_posts'] . " AS post", "post.blog_post_author_username = author.user_auth_username", 'left');

		// tag_rel + post
		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", "tag_rel.blog_post_tag_post_id = post.blog_post_id", 'left');

		// tag + tag_rel
		$this->db->join($this->tables['blog_tags'] . " AS tag", "tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug", 'left');

		// categ_rel + post
		$this->db->join($this->tables['blog_post_categs'] . " AS categ_rel", "categ_rel.blog_post_categ_post_id = post.blog_post_id", 'left');

		// categ + categ_rel
		$this->db->join($this->tables['blog_categories'] . " AS categ", "categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug", 'left');


		$this->db->where_in('author.user_auth_username', $author);
		$this->db->where('author.user_auth_is_active', true);   // only active users
		$this->db->where('author.user_auth_is_deleted', false); // users NOT deleted

		$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);       // only published posts
		// to group posts for category list and tag list
		$this->db->group_by('post.blog_post_id');

		$this->db->order_by('post.blog_post_created', 'DESC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

		if ($query->num_rows() > 0) {

			$posts = $query->result_array();

			// strip slashes
			foreach ($posts as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}

			// record current queries total row count for pagination
			$this->_record_total_rows(__FUNCTION__);
		}

		return $posts;
	}

	/**
	 * Gets list of posts by date conditions
	 * if year = null --> ignores month, day --> gets latest posts
	 * if year != null --> get posts in a particular year OR year/month OR year/month/day
	 */
	public function get_posts_by_date($year = null, $month = null, $day = null, $limit = null, $offset = null, $allowHidden = false) {

//		echo "<h2>in posts date</h2>";
		$posts = null;

		$year = $this->addslashes($year);
		$month = $this->addslashes($month);
		$day = $this->addslashes($day);

		$select = "
                        SQL_CALC_FOUND_ROWS
                        post.blog_post_id            AS id,
                        post.blog_post_title         AS title,
                        post.blog_post_slug          AS slug,
                        post.blog_post_body          AS body,
                        post.blog_post_created       AS created,
                        post.blog_post_modified      AS updated,
                        post.blog_post_is_comments   AS is_comments,
                        post.blog_post_status   AS status,

			GROUP_CONCAT( DISTINCT categ.blog_categ_id SEPARATOR ',' )  AS category_ids,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_name SEPARATOR ',' )  AS categories,
                        GROUP_CONCAT( DISTINCT categ.blog_categ_slug SEPARATOR ',' )  AS category_slugs,

			GROUP_CONCAT( DISTINCT tag.blog_tag_id SEPARATOR ',' )  AS tag_ids,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_name SEPARATOR ',' )  AS tags,
                        GROUP_CONCAT( DISTINCT tag.blog_tag_slug SEPARATOR ',' )  AS tag_slugs,

                        author.user_auth_username      AS author_username,
                        author.user_auth_name          AS author_name
                        ";

		$this->db->select($select, $escape = false);

		// main condition table ( post )
		$this->db->from($this->tables['blog_posts'] . " AS post");

		// tag_rel + post
		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", "tag_rel.blog_post_tag_post_id = post.blog_post_id", 'left');

		// tag + tag_rel
		$this->db->join($this->tables['blog_tags'] . " AS tag", "tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug", 'left');

		// categ_rel + post
		$this->db->join($this->tables['blog_post_categs'] . " AS categ_rel", "categ_rel.blog_post_categ_post_id = post.blog_post_id", 'left');

		// categ + categ_rel
		$this->db->join($this->tables['blog_categories'] . " AS categ", "categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug", 'left');

		// author + post
		$this->db->join($this->tables['author'] . " AS author", "author.user_auth_username = post.blog_post_author_username", 'left');

		// check if draft should be displayed
		if ($allowHidden == false) {
			// only published posts
			$this->db->where('post.blog_post_status', BLOG_POST_STATUS_PUBLISHED);
		}

		// check if year provided, if NOT provided, assume latest posts should be returned
		if (!is_null($year)) {

			// get posts by year OR year/month OR year/month/day
			// depending on conditions provided
			// set default interval as 'year'
			$interval = 'year';
			// if month is provided, set default interval as 'month'
			if (!is_null($month)) {

				$interval = 'month';

				// if day is provided, set default interval as 'day'
				if (!is_null($day)) {

					$interval = 'day';
				}
			}

			// default month, day to 1 if not provided
//                      $year = is_null($year) ? 1 : $year;
			$month = is_null($month) ? 1 : $month;
			$day = is_null($day) ? 1 : $day;

			$startDate = "$year-$month-$day";

			// note: str_to_date() is mandatory, to convert string to correct date format, it works in normal SQL but for some reason DOES NOT work in Codeigniter
			// str_to_date() is also required to convert month=1 to month=01, date search acts different for 2012-03-20 and 2012-3-20
			$this->db->where("post.blog_post_created BETWEEN STR_TO_DATE('$startDate','%Y-%m-%d') AND DATE_ADD( STR_TO_DATE('$startDate','%Y-%m-%d'), interval 1 $interval )", null, false );
//			$this->db->where("post.blog_post_created BETWEEN '$startDate' AND DATE_ADD( '$startDate', interval 1 $interval )", null, false );
		} else {

			// get latest posts
			// DO NOTHING, posts will auto align in desending order
		}

		// to group posts for category list and tag list
		$this->db->group_by('post.blog_post_id');

		$this->db->order_by('post.blog_post_created', 'DESC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

//		$sql = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$posts = $query->result_array();

			// strip slashes
			foreach ($posts as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}

			// record current queries total row count for pagination
			$this->_record_total_rows(__FUNCTION__);
		}

		return $posts;
	}

	/**
	 * Gets a list of year, month, postCount in decending or ascending order
	 *
	 * @param bool $isLatestFirst if ture--> displays result in anti-chronological order, else chronological order
	 * @param int|null $limit max number of results to display at a time
	 * @offset int|null $offset offset of row to start results from
	 *
	 * @return array|null
	 */
	public function get_archives($isLatestFirst = true, $limit = null, $offset = null) {

		$data = null;

		$select = "
                        year( blog_post_created )       AS year,
                        monthname( blog_post_created )  AS month,
                        count( * )                      AS count
                        ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_posts']);
		// only published posts
		$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);

		// to group posts for category list and tag list

		$this->db->group_by('year');
		$this->db->group_by('month');

		if ($isLatestFirst) {

			$this->db->order_by('blog_post_created', 'DESC');
		} else {
			$this->db->order_by('blog_post_created', 'ASC');
		}
//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			// strip slashes
			foreach ($data as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}
		}

		return $data;
	}

	/**
	 * Gets all categories in db ( that have posts associated with it )
	 * gets category info, number of posts for each category
	 */
	public function get_categories($limit = null, $offset = null, $allowHiddenPosts = false, $allowHiddenCategs = false, $allowSpecial = true) {

		/**
		  SELECT
		  SQL_CALC_FOUND_ROWS
		  categ.blog_categ_id                   AS id,
		  categ.blog_categ_name                 AS name,
		  categ.blog_categ_slug                 AS slug,
		  categ.blog_categ_description          AS body,
		  categ.blog_categ_created              AS created,
		  categ.blog_categ_modified             AS updated,
		  categ.blog_categ_is_visible           AS is_visible,
		  categ.blog_categ_is_comments          AS is_comments,
		  categ.blog_categ_order                AS `order`,
		  categ.blog_categ_is_special           AS is_special,

		  count( post_rel.blog_post_categ_categ_slug ) AS count

		  FROM blog_categories AS categ
		  LEFT JOIN blog_post_categs AS post_rel ON categ.blog_categ_slug = post_rel.blog_post_categ_categ_slug

		  LEFT JOIN blog_posts AS post ON post.blog_post_id = post_rel.blog_post_categ_post_id

		  WHERE
		  categ.blog_categ_is_special = 0
		  AND post.blog_post_status = 'published'

		  GROUP BY post_rel.blog_post_categ_categ_slug

		  ORDER BY categ.blog_categ_slug  ASC
		 */
		$data = null;

		$select = "
			SQL_CALC_FOUND_ROWS
                        categ.blog_categ_id                   AS id,
                        categ.blog_categ_name                 AS name,
                        categ.blog_categ_slug                 AS slug,
                        categ.blog_categ_description          AS body,
                        categ.blog_categ_created              AS created,
                        categ.blog_categ_modified             AS updated,
                        categ.blog_categ_is_visible           AS is_visible,
                        categ.blog_categ_is_comments          AS is_comments,
                        categ.blog_categ_order                AS `order`,
                        categ.blog_categ_is_special           AS is_special,

			count( post_rel.blog_post_categ_categ_slug ) AS count
                        ";

		$this->db->select($select, false);
		$this->db->from($this->tables['blog_categories'] . " AS categ");
		// categ + post_rel
		$this->db->join($this->tables['blog_post_categs'] . " AS post_rel", 'categ.blog_categ_slug = post_rel.blog_post_categ_categ_slug', 'left');
		// post + post_rel
		$this->db->join($this->tables['blog_posts'] . " AS post", 'post.blog_post_id = post_rel.blog_post_categ_post_id', 'left');

		if ($allowHiddenPosts === false) {
			// ONLY published posts
			$this->db->where('post.blog_post_status', BLOG_POST_STATUS_PUBLISHED);
		}

		if ($allowHiddenCategs === false) {
			// ONLY visible categs
			$this->db->where('categ.blog_categ_is_visible', true);
		}

		if ($allowSpecial === false) {
			// ONLY NON special categories
			$this->db->where('categ.blog_categ_is_special', false);
		}

		// group categories for count
//		$this->db->group_by('post_rel.blog_post_categ_categ_slug');
		$this->db->group_by('categ.blog_categ_slug');

		$this->db->order_by('categ.blog_categ_is_special', 'DESC'); // display special categs on top
		$this->db->order_by('categ.blog_categ_order', 'ASC');
		$this->db->order_by('categ.blog_categ_slug', 'ASC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

//		$sql = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			// strip slashes
			foreach ($data as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}

			$this->_record_total_rows(__FUNCTION__);
		}

		return $data;
	}

	/**
	 * Gets all tags in db ( that have posts associated with it )
	 * gets tag info, number of posts for each tag
	 */
	public function get_tags($limit = null, $offset = null, $allowHiddenPosts = false) {

		/**
		  SELECT
		  SQL_CALC_FOUND_ROWS
		  tag.blog_tag_id AS id,
		  tag.blog_tag_name AS name,
		  tag.blog_tag_slug AS slug,
		  tag.blog_tag_description AS body,
		  count( tag_rel.blog_post_tag_tag_slug ) AS count

		  FROM blog_post_tags AS tag_rel

		  LEFT JOIN blog_posts AS post ON post.blog_post_id = tag_rel.blog_post_tag_post_id

		  LEFT JOIN blog_tags AS tag ON tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug

		  WHERE
		  post.blog_post_status = 'published'

		  GROUP BY tag_rel.blog_post_tag_tag_slug

		  ORDER BY tag.blog_tag_slug ASC
		 */
		$data = null;

		$select = "
			SQL_CALC_FOUND_ROWS
                        tag.blog_tag_id AS id,
                        tag.blog_tag_name AS name,
                        tag.blog_tag_slug AS slug,
                        tag.blog_tag_description AS body,

			count( tag_rel.blog_post_tag_tag_slug ) AS count
                        ";

		$this->db->select($select, false);
//		$this->db->from($this->tables['blog_post_tags'] . " AS tag_rel");
		$this->db->from($this->tables['blog_tags'] . " AS tag");
		// tag + tag_rel
		$this->db->join($this->tables['blog_post_tags'] . " AS tag_rel", 'tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug', 'left');
		// post + post_rel
		$this->db->join($this->tables['blog_posts'] . " AS post", 'post.blog_post_id = tag_rel.blog_post_tag_post_id', 'left');

//		$this->db->join($this->tables['blog_tags'] . " AS tag", 'tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug', 'left');

		if ($allowHiddenPosts === false) {
			// ONLY published posts
			$this->db->where('post.blog_post_status', BLOG_POST_STATUS_PUBLISHED);
		}


		// group categories for count
		$this->db->group_by('tag.blog_tag_slug');
//		$this->db->group_by('tag_rel.blog_post_tag_tag_slug');

		// order by tag slug, asc
		$this->db->order_by('tag_rel.blog_post_tag_tag_slug', 'ASC');

//                $sql_before = $this->db->_compile_select();

		$query = $this->db->get('', $limit, $offset);

		if ($query->num_rows() > 0) {

			$data = $query->result_array();

			// strip slashes
			foreach ($data as & $row) {

				foreach ($row as $key => $value)
					$row[$key] = $this->stripslashes($value);
			}
			$this->_record_total_rows(__FUNCTION__);
		}

		return $data;
	}

	/**
	 * gets previous AND next post data for pagination ( single post )
	 * note: runs 2 queries,
	 *      1. to get id and also to verify if current post exists
	 *      2. to get prev and next post
	 * result may be prev OR next post OR both depending on availability
	 * uses union to get both queries.
	 *
	 * @param string $postId post id
	 * @param string $postSlug post slug
	 *
	 * @return null|array if null returned, either current post does not exist OR prev,next post does not exist
	 */
	public function get_pre_next_post($postId, $postSlug) {

		/*

		  SET @curr_id:=100;

		  (
		  SELECT

		  'prev' AS position,
		  post.`blog_post_id` AS id,
		  post.`blog_post_author_username` AS author,
		  post.`blog_post_title` AS title,
		  post.`blog_post_slug` AS slug,
		  post.`blog_post_created` AS created,

		  categ_rel.blog_post_categ_categ_slug AS category,
		  tag_rel.blog_post_tag_tag_slug AS tag

		  FROM
		  `blog_posts` AS post
		  LEFT JOIN blog_post_categs AS categ_rel ON categ_rel.blog_post_categ_post_id = post.`blog_post_id`
		  LEFT JOIN blog_post_tags AS tag_rel ON tag_rel.blog_post_tag_post_id = post.`blog_post_id`

		  WHERE
		  post.blog_post_id < @curr_id
		  AND post.`blog_post_status` = 'published'

		  ORDER BY
		  post.blog_post_id DESC,
		  -- ,categ_rel.blog_post_categ_categ_slug ASC
		  -- tag_rel.blog_post_tag_tag_slug ASC

		  LIMIT 1
		  )

		  UNION

		  (
		  SELECT

		  'next' AS position,
		  post.`blog_post_id` AS id,
		  post.`blog_post_author_username` AS author,
		  post.`blog_post_title` AS title,
		  post.`blog_post_slug` AS slug,
		  post.`blog_post_created` AS created,

		  categ_rel.blog_post_categ_categ_slug AS category,
		  tag_rel.blog_post_tag_tag_slug AS tag

		  FROM
		  `blog_posts` AS post
		  LEFT JOIN blog_post_categs AS categ_rel ON categ_rel.blog_post_categ_post_id = post.`blog_post_id`
		  LEFT JOIN blog_post_tags AS tag_rel ON tag_rel.blog_post_tag_post_id = post.`blog_post_id`

		  WHERE
		  post.blog_post_id > @curr_id
		  AND post.`blog_post_status` = 'published'

		  ORDER BY
		  post.blog_post_id ASC
		  -- ,categ_rel.blog_post_categ_categ_slug ASC,
		  -- tag_rel.blog_post_tag_tag_slug ASC

		  LIMIT 1
		  );

		 */

		$data = null;

		$postId = $this->addslashes($postId);
		$postSlug = $this->addslashes($postSlug);

		// check if valid id and slug provided
		if (!is_null($postId) OR !is_null($postSlug)) {

			$this->db->select("blog_post_id AS id");
			$this->db->from($this->tables['blog_posts']);
			$this->db->where('blog_post_id', $postId);
			$this->db->or_where('blog_post_slug', $postSlug);
			$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);
			$this->db->limit(1);

			$preQuery = $this->db->get();

			if ($preQuery->num_rows() > 0) {

				// post exists, get postId, and get prev, next post

				$post = $preQuery->row_array();
				$postId = $post['id'];
				// clear db resources for first query
				$preQuery->free_result();

				// new query to generate union of prev + next post data
				$sql = "

                                (
                                        SELECT

                                        'prev' AS position,
                                        post.blog_post_id AS id,
                                        post.blog_post_author_username AS author,
                                        post.blog_post_title AS title,
                                        post.blog_post_slug AS slug,
                                        post.blog_post_created AS created,

                                        categ_rel.blog_post_categ_categ_slug AS category,
                                        tag_rel.blog_post_tag_tag_slug AS tag

                                        FROM
                                        " . $this->tables['blog_posts'] . " AS post
                                        LEFT JOIN " . $this->tables['blog_post_categs'] . " AS categ_rel ON categ_rel.blog_post_categ_post_id = post.blog_post_id
                                        LEFT JOIN " . $this->tables['blog_post_tags'] . " AS tag_rel ON tag_rel.blog_post_tag_post_id = post.blog_post_id

                                        WHERE
                                        post.blog_post_id < $postId
                                        AND post.blog_post_status = 'published'

                                        ORDER BY
                                        post.blog_post_id DESC

                                        LIMIT 1
                                )

                                UNION

                                (
                                        SELECT

                                        'next' AS position,
                                        post.blog_post_id AS id,
                                        post.blog_post_author_username AS author,
                                        post.blog_post_title AS title,
                                        post.blog_post_slug AS slug,
                                        post.blog_post_created AS created,

                                        categ_rel.blog_post_categ_categ_slug AS category,
                                        tag_rel.blog_post_tag_tag_slug AS tag

                                        FROM
                                        " . $this->tables['blog_posts'] . " AS post
                                        LEFT JOIN " . $this->tables['blog_post_categs'] . " AS categ_rel ON categ_rel.blog_post_categ_post_id = post.blog_post_id
                                        LEFT JOIN " . $this->tables['blog_post_tags'] . " AS tag_rel ON tag_rel.blog_post_tag_post_id = post.blog_post_id

                                        WHERE
                                        post.blog_post_id > $postId
                                        AND post.blog_post_status = 'published'

                                        ORDER BY post.blog_post_id ASC

                                        LIMIT 1
                                );
                                ";

				$query = $this->db->query($sql, false);

				// check if pre, next post found
				if ($query->num_rows() > 0) {

					$data = $query->result_array();

					// strip slashes
					foreach ($data as & $row) {

						foreach ($row as $key => $value)
							$row[$key] = $this->stripslashes($value);
					}
				} else {
					// neither prev NOR next post found
					$data = null;
				}
			} else {
				// error post NOT found
				$data = null;
			}
		} else {
			// cannot query as $postId and $postSlug are null
			$data = null;
		}

		return $data;
	}

//	// ------ testing END -------------
//	// NOTE: THIS MODEL ( get_posts_data ) MAY NOT BE USED. CHECK AND DELETE IF NOT IN USE
//	public function get_posts_data($groupType, $conditions, $limit = null, $offset = null, $showHidden = false) {
//
//		$data = null;
//		$success = false;
//
//		$select = "
//                        posts.blog_post_id            AS id,
//                        posts.blog_post_title         AS title,
//                        posts.blog_post_slug          AS slug,
//                        posts.blog_post_body          AS body,
//                        posts.blog_post_created       AS created,
//                        posts.blog_post_modified      AS updated,
//                        posts.blog_post_is_comments   AS is_comments,
//
//                        author.user_auth_username      AS author_username,
//                        author.user_auth_name          AS author_name,
//
//                        GROUP_CONCAT( DISTINCT categs.blog_post_categ_categ_slug SEPARATOR ',' )  AS categories,
//                        GROUP_CONCAT( DISTINCT tags.blog_post_tag_tag_slug SEPARATOR ',' )  AS tags
//                        ";
//
//		$this->db->select($select, $escape = false);
//
//		switch ($groupType) {
//
//			case BLOG_GROUP_TYPE_AUTHOR:
//
//				// check if author provided, if not error
//				if (isset($conditions['author'])) {
//
//					$author = $conditions['author'];
//
//					$this->db->from($this->tables['author'] . " AS author");
//					$this->db->join($this->tables['blog_posts'] . " AS posts", "posts.blog_post_author_username = author.user_auth_username", 'left');
//					$this->db->join($this->tables['blog_post_categs'] . " AS categs", "categs.blog_post_categ_post_id = posts.blog_post_id", 'left');
//					$this->db->join($this->tables['blog_post_tags'] . " AS tags", "tags.blog_post_tag_post_id = posts.blog_post_id", 'left');
//					$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);
//					$this->db->where('blog_post_author_username', $author);
//					$this->db->group_by('posts.blog_post_id');
//
//
//					$success = true;
//				} else {
//					$success = false;
//				}
//
//				break;
//
//			case BLOG_GROUP_TYPE_CATEGORY:
//
//				// check if category  provided, if not error
//				if (isset($conditions['category'])) {
//
//					$category = $conditions['category'];
//
////                                        $this->db->from($this->tables['author'] . " AS author");
//					$this->db->from($this->tables['blog_post_categs'] . " AS categs");
//					$this->db->join($this->tables['blog_posts'] . " AS posts", "posts.blog_post_id = categs.blog_post_categ_post_id", 'left');
//					$this->db->join($this->tables['author'] . " AS author", "posts.blog_post_author_username = author.user_auth_username", 'left');
//					$this->db->join($this->tables['blog_post_tags'] . " AS tags", "tags.blog_post_tag_post_id = posts.blog_post_id", 'left');
//					$this->db->where('blog_post_status', BLOG_POST_STATUS_PUBLISHED);
//					$this->db->where('blog_post_author_username', $category);
//					$this->db->group_by('posts.blog_post_id');
//
//
//					$success = true;
//				} else {
//					$success = false;
//				}
//
//				break;
//
//			case BLOG_GROUP_TYPE_TAG:
//
//				break;
//
//			case BLOG_GROUP_TYPE_DATE:
//
//
//				break;
//
//
//			default:
//				$success = false;
//				break;
//		}
//
//		if ($success) {
//
////                        $sql = $this->db->_compile_select();
//
//			$query = $this->db->get('', $limit, $offset);
//
////                        $sql_after = $this->db->last_query();
//
//			if ($query->num_rows() > 0) {
//
//				$data = $query->result_array();
//
//				// strip slashes
//				foreach ($data as & $row) {
//
//					foreach ($row as $key => $value)
//						$row[$key] = stripslashes($value);
//				}
//
//				$success = true;
//			}
//		} else {
//			$data = null;
//		}
//
//
//
//
//		return $data;
//	}
}

?>
