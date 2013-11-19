<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of blog_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Blog_Model extends Site_Model {

	protected $module = "blog";
	protected $configFile = 'blog_config';
	protected $config_database = "blog_config_database";
//        private $config_settings = "blog_config_settings";

	protected $tables = array();

	public function __construct() {

		parent::__construct();

		// load config files
		$this->config->load('database_tables');
		$this->config->load($this->configFile, true, true, $this->module);
		$this->config->load($this->config_database, true, true, $this->module);

		// load tables
		$this->tables["blog_posts"] = $this->config->item("tbl_blog_posts", $this->config_database);
		$this->tables["blog_categories"] = $this->config->item("tbl_blog_categories", $this->config_database);
		$this->tables["blog_tags"] = $this->config->item("tbl_blog_tags", $this->config_database);
		$this->tables["blog_post_categs"] = $this->config->item("tbl_blog_post_categs", $this->config_database);
		$this->tables["blog_post_tags"] = $this->config->item("tbl_blog_post_tags", $this->config_database);
		$this->tables["blog_post_meta"] = $this->config->item("tbl_blog_post_meta", $this->config_database);
		$this->tables["post_revisions"] = $this->config->item("tbl_blog_post_revisions", $this->config_database);

		$this->tables['author'] = $this->config->item("TBL_USERS_AUTH");

		!defined('BLOG_DEFAULT_CATEG_SLUG') ? define('BLOG_DEFAULT_CATEG_SLUG', $this->config->item('category_uncategorized', $this->configFile)) : null; // uncategorized
		!defined('BLOG_POST_STATUS_DRAFT') ? define('BLOG_POST_STATUS_DRAFT', $this->config->item("post_status_draft", $this->configFile)) : null;
		!defined('BLOG_POST_STATUS_PUBLISHED') ? define('BLOG_POST_STATUS_PUBLISHED', $this->config->item("post_status_published", $this->configFile)) : null;
	}

	/**
	 * Add a post to the database
	 *
	 * @param array $postDB
	 * @return int post id
	 */
	public function add_post($postDB) {

		$postDB = $this->addslashes($postDB);
		$this->db->insert($this->tables["blog_posts"], $postDB);
		return $this->db->insert_id();
	}

	/**
	 * Add a category to the database
	 *
	 * @param array $categDB
	 * @return int categ id
	 */
	public function add_categ($categDB) {

		$categDB = $this->addslashes($categDB);
		$this->db->insert($this->tables["blog_categories"], $categDB);
		return $this->db->insert_id();
	}

	/* @return bool */

	public function edit_categ($id, $data) {

		$id = $this->addslashes($id);
		$data = $this->addslashes($data);

		$this->db->where('blog_categ_id', $id);
		$this->db->update($this->tables["blog_categories"], $data);
//                return $return == false ? false : true;
		return $this->db->affected_rows() != -1 ? true : false;
	}

	public function get_tag_details($tagId) {

		$tagDetails = null;

		$tagId = $this->addslashes($tagId);

		/*
		  SELECT
		  blog_tag_id                   AS id,
		  blog_tag_name                 AS name,
		  blog_tag_slug                 AS slug,
		  blog_tag_description          AS description

		  FROM blog_tags

		  WHERE blog_tag_id = $tagId

		  LIMIT 1
		 */

		$select = "
                  blog_tag_id                   AS id,
		  blog_tag_name                 AS name,
		  blog_tag_slug                 AS slug,
		  blog_tag_description          AS description
                ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_tags']);
		$this->db->where('blog_tag_id', $tagId);

		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$tagDetails = $query->row_array();

			// strip slashes
			foreach ($tagDetails as $key => $value) {

				$tagDetails[$key] = $this->stripslashes($value);
			}
		}

		return $tagDetails;
	}

	/**
	 * Add a tag to the database
	 *
	 * @param array $tagDB
	 * @return int tag id
	 */
	public function add_tag($tagDB) {

		$tagDB = $this->addslashes($tagDB);
		$this->db->insert($this->tables["blog_tags"], $tagDB);
		return $this->db->insert_id();
	}

	/* @return bool */

	public function edit_tag($id, $data) {

		$id = $this->addslashes($id);
		$data = $this->addslashes($data);

		$this->db->where('blog_tag_id', $id);
		$this->db->update($this->tables["blog_tags"], $data);
//                return $return == false ? false : true;
		return $this->db->affected_rows() != -1 ? true : false;
	}

	/**
	 * gets Categ details for given categ_Id
	 *
	 * @param int $categId categ id
	 * @param bool $allowHidden
	 * @param bool $allowSpecial
	 *
	 * @return array|null
	 */
	public function get_categ_details($categId, $allowHidden = false, $allowSpecial = true) {

		$categDetails = null;

		$categId = $this->addslashes($categId);

		/*
		  SELECT
		  blog_categ_id                    AS id,
		  blog_categ_parent_id             AS parent_id,
		  blog_categ_name                 AS name,
		  blog_categ_slug                  AS slug,
		  blog_categ_description                  AS description  ,
		  blog_categ_created               AS created,
		  blog_categ_modified              AS modified,
		  blog_categ_is_visible              AS is_visible,
		  blog_categ_is_comments           AS is_comments,
		  blog_categ_order                      AS `order`,
		  blog_categ_is_special         AS is_special

		  FROM blog_categories

		  WHERE blog_categ_id = $categId
		  -- AND blog_categ_is_visible = 0
		  -- AND blog_categ_is_special = 1

		  LIMIT 1
		 */

		$select = "

                                blog_categ_id                    AS id,
                                blog_categ_parent_id             AS parent_id,
                                blog_categ_name                 AS name,
                                blog_categ_slug                  AS slug,
                                blog_categ_description                  AS description  ,
                                blog_categ_created               AS created,
                                blog_categ_modified              AS modified,
                                blog_categ_is_visible              AS is_visible,
                                blog_categ_is_comments           AS is_comments,
                                blog_categ_order                      AS `order`,
                                blog_categ_is_special         AS is_special
                ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_categories']);
		$this->db->where('blog_categ_id', $categId);

		// add condition for is_visible categ
		if (!$allowHidden) {
			$this->db->where('blog_categ_is_visible', true);
		}
		// add condition for special categ
		if (!$allowSpecial) {
			$this->db->where('blog_categ_is_special', false);
		}

		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$categDetails = $query->row_array();

			// strip slashes
			foreach ($categDetails as $key => $value) {
				$categDetails[$key] = $this->stripslashes($value);
			}
		}

		return $categDetails;
	}

	/**
	 * gets all categories with a checked=true/false depending on values in db OR false if new post
	 *
	 * If postId NOT provided ( get all categs, with checked = false )
	 * if postId provided --> get all categories, with appropriate checked values as set by user
	 *
	 * @param int $postId post id
	 *
	 * @return array ( name, slug, checked )
	 */
	public function get_categ_post_assoc($postId = null) {

		$categories = null;

		$postId = $this->addslashes($postId);

		/*
		  SELECT
		  blog_categ_name AS name,
		  blog_categ_slug AS slug,
		  (
		  SELECT '1'
		  FROM blog_post_categs
		  WHERE
		  blog_post_categ_categ_slug = blog_categ_slug AND
		  blog_post_categ_post_id = 5
		  ) AS 'checked'

		  FROM
		  blog_categories

		  ORDER BY
		  blog_categ_is_special DESC,
		  blog_categ_order ASC,
		  blog_categ_slug ASC
		 */

		// if postId NOT provided all categs will get checked = null
		$post_id = is_null($postId) ? 'NULL' : $postId;

		$select = "blog_categ_name AS name,
                           blog_categ_slug AS slug,
                        (
                                SELECT '1'
                                FROM blog_post_categs
                                WHERE
                                blog_post_categ_categ_slug = blog_categ_slug AND
                                blog_post_categ_post_id = $post_id
                        ) AS 'checked'";

		$this->db->select($select);
		$this->db->from($this->tables['blog_categories']);
		$this->db->order_by('blog_categ_is_special', 'desc');
		$this->db->order_by('blog_categ_order', 'asc');
		$this->db->order_by('blog_categ_slug', 'asc'); // if order is same, arrange in alphabetical order

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$categories = $query->result_array();

			// strip slashes
			foreach ($categories as & $categ) {

				$categ['name'] = stripslashes($categ['name']);
				$categ['slug'] = stripslashes($categ['slug']);
				$categ['checked'] = (bool) $categ['checked'];
			}
		}

		return $categories;
	}

	/* @return bool */

	public function edit_post($id, $data) {

		$id = $this->addslashes($id);
		$data = $this->addslashes($data);

		$this->db->where('blog_post_id', $id);
		$this->db->update($this->tables["blog_posts"], $data);
//                return $return == false ? false : true;
		return $this->db->affected_rows() != -1 ? true : false;
	}

	/**
	 * Checks if a slug is already present in database
	 * returns false if exists
	 * returns true if DOES NOT exist
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function check_is_post_slug_unique($slug) {

		$slug = $this->addslashes($slug);
		$sql = "SELECT count(*) as count FROM " . $this->tables['blog_posts'] . " WHERE blog_post_slug = '$slug'";
		return ( $this->db->query($sql)->row()->count > 0 ) ? false : true;
	}

	/**
	 * Checks if a slug is already present in database
	 * returns false if exists
	 * returns true if DOES NOT exist
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function check_is_categ_slug_unique($slug, $id) {

		$slug = $this->addslashes($slug);
		$id = $this->addslashes($id);

//                $sql = "SELECT count(*) as count FROM " . $this->tables['blog_categories'] . " WHERE blog_categ_slug = '$slug' AND blog_categ_id != $id";
		$this->db->select("blog_categ_id AS id");
		$this->db->from($this->tables['blog_categories']);
		$this->db->where("blog_categ_slug", $slug);

		// exclude current id
		$this->db->where("blog_categ_id !=", $id);

		$query = $this->db->get();

		return ( $query->num_rows() > 0 ) ? false : true;
	}

	/**
	 * Checks if a slug is already present in database
	 * returns false if exists
	 * returns true if DOES NOT exist
	 *
	 * @param string $slug
	 * @return bool
	 */
	public function check_is_tag_slug_unique($slug, $id) {

		$slug = $this->addslashes($slug);
		$id = $this->addslashes($id);

//                $sql = "SELECT count(*) as count FROM " . $this->tables['blog_tags'] . " WHERE blog_tag_slug = '$slug' AND blog_tag_id != $id";
		$this->db->select("blog_tag_id AS id");
		$this->db->from($this->tables['blog_tags']);
		$this->db->where("blog_tag_slug", $slug);

		// exclude current id
		$this->db->where("blog_tag_id !=", $id);

		$query = $this->db->get();

		return ( $query->num_rows() > 0 ) ? false : true;
	}

	/**
	 * assigns a post under a given category
	 * - deletes existing associations ( if any )
	 * - creates new associations.
	 *
	 * @param int $postId
	 * @param array $categSlugs this is a list of category slug values that the post should be associated with
	 *
	 * @return bool will return success/failure of transaction status
	 */
	public function set_post_categ_assoc_BACKUP($postId, $categSlugs) {

		$success = false;

		$postId = $this->addslashes($postId);
		$categSlugs = $this->addslashes($categSlugs);

		// start transaction
		$this->transaction_strict(true);
		$this->transaction_start();

		//delete existing associations
		$this->delete_post_categ_assoc($postId);

		// create new associations
		foreach ($categSlugs as $slug) {

			$categPostDb = array(
			    'blog_post_categ_categ_slug' => $slug,
			    'blog_post_categ_post_id' => $postId
			);

			$this->db->insert($this->tables['blog_post_categs'], $categPostDb);
		}

		// end transaction
		$this->transaction_complete();

		$success = $this->transaction_status();

		return $success;
	}

	public function delete_post_categ_assoc($postId) {

		$postId = $this->addslashes($postId);

		$this->db->where('blog_post_categ_post_id', $postId);
		$this->db->delete($this->tables['blog_post_categs']);

		return $this->db->affected_rows();
	}

	public function insert_tags($tagsDb) {

		$return = null;

		$tagsDb = $this->addslashes($tagsDb);

		// ignore the insert if already exists ( note this is a custom feature, NOT originally available with CodeIgniter )
		$this->db->ignore(true);
		$return = $this->db->insert_batch($this->tables["blog_tags"], $tagsDb);
		$this->db->ignore(false); // reset

		return $return;
	}

	/**
	 * Deletes Categories
	 *
	 * @param array $ids list of IDs to delete
	 * @param bool $isFixOrphans if orphan posts ( NOT assigned to any category ) should be assigned to default category ( 'uncategorized' )
	 *
	 * @return bool
	 */
	public function delete_tags($ids) {

		$ids = $this->addslashes($ids);

		$this->db->where_in('blog_tag_id', $ids);
		$success = $this->db->delete($this->tables['blog_tags']);

		return $success;
	}

	/**
	 * associates a post under given tags
	 * - deletes existing associations ( if any )
	 * - creates new associations.
	 *
	 * @param int $postId
	 * @param array $tagSlugs this is a list of category slug values that the post should be associated with
	 *
	 * @return bool will return success/failure of transaction status
	 */
	public function set_post_tag_assoc_BACKUP($postId, $tagSlugs) {

		$success = false;

		$postId = $this->addslashes($postId);
		$tagSlugs = $this->addslashes($tagSlugs);

		// start transaction
		$this->transaction_strict(true);
		$this->transaction_start();

		//delete existing associations
		$this->delete_post_tag_assoc($postId);

		// create new associations
		foreach ($tagSlugs as $slug) {

			$postTagDb = array(
			    'blog_post_tag_tag_slug' => $slug,
			    'blog_post_tag_post_id' => $postId
			);

			$this->db->insert($this->tables['blog_post_tags'], $postTagDb);
		}

		// end transaction
		$this->transaction_complete();

		$success = $this->transaction_status();

		return $success;
	}

	/*	 * **  Testing ************* */

	public function set_post_categ_assoc($postId, $categSlugs) {

//                $success = true;

		$postId = $this->addslashes($postId);
		$categSlugs = $this->addslashes($categSlugs);

		//delete existing associations
		$this->delete_post_categ_assoc($postId);

		// create new associations
		foreach ($categSlugs as $slug) {

			$categPostDb = array(
			    'blog_post_categ_categ_slug' => $slug,
			    'blog_post_categ_post_id' => $postId
			);

			$this->db->insert($this->tables['blog_post_categs'], $categPostDb);
		}

		return true;
	}

	public function set_post_tag_assoc($postId, $tagSlugs) {

//                $success = true;

		$postId = $this->addslashes($postId);
		$tagSlugs = $this->addslashes($tagSlugs);

		//delete existing associations
		$this->delete_post_tag_assoc($postId);

		// create new associations
		foreach ($tagSlugs as $slug) {

			$postTagDb = array(
			    'blog_post_tag_tag_slug' => $slug,
			    'blog_post_tag_post_id' => $postId
			);

			$this->db->insert($this->tables['blog_post_tags'], $postTagDb);
		}

		return true;
	}

	/*	 * ******* End Testing *********** */

	public function delete_post_tag_assoc($postId) {

		$postId = $this->addslashes($postId);

		$this->db->where('blog_post_tag_post_id', $postId);
		$this->db->delete($this->tables['blog_post_tags']);

		return $this->db->affected_rows();
	}

	/**
	 * gets Post details for given Post_Id
	 *
	 * @param int $postId post id
	 * @param string $status usually 'draft' OR 'published', if null --> DO NOT apply status condition
	 * @return array|false returns result on success, false on no result
	 */
	public function get_post_details($postId, $status = null) {

		$postDetails = null;

		$postId = $this->addslashes($postId);

		/*
		  SELECT
		  blog_post_id                    AS id,
		  blog_post_author_username       AS username,
		  blog_post_title                 AS title,
		  blog_post_slug                  AS slug,
		  blog_post_body                  AS body,
		  blog_post_created               AS created,
		  blog_post_modified              AS modified,
		  blog_post_status                AS status,
		  blog_post_is_comments           AS is_comments

		  FROM blog_post_status

		  WHERE blog_post_id = $postId
		  -- AND WHERE blog_post_status = '$status'

		 */

		$select = "
                        blog_post_id                    AS id,
                        blog_post_author_username       AS username,
                        blog_post_title                 AS title,
                        blog_post_slug                  AS slug,
                        blog_post_body                  AS body,
                        blog_post_created               AS created,
                        blog_post_modified              AS modified,
                        blog_post_status                AS status,
                        blog_post_is_comments           AS is_comments
                ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_posts']);
		$this->db->where('blog_post_id', $postId);

		// add status in where clause if condition provided
		if (!is_null($status))
			$this->db->where('blog_post_status', $status);

		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$postDetails = $query->row_array();

			// strip slashes
			foreach ($postDetails as $key => $value) {
				$postDetails[$key] = stripslashes($value);
			}
		}

		return $postDetails;
	}

	/**
	 * Gets tags related to a particular post
	 *
	 * @param int $postId
	 * @return array|null
	 */
	public function get_post_tags_assoc($postId) {

		$postId = $this->addslashes($postId);

		$tags = null;

		$select = "
                        blog_tag_name AS name,
                        blog_tag_slug AS slug
                ";

		$this->db->select($select);
		$this->db->from($this->tables['blog_post_tags']);
		$this->db->join($this->tables['blog_tags'], 'blog_post_tag_tag_slug = blog_tag_slug', 'left');
		$this->db->where('blog_post_tag_post_id', $postId);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$tags = $query->result_array();

			// strip slashes
			foreach ($tags as & $tag) {

				foreach ($tag as $key => $value) {

					$tag[$key] = stripslashes($value);
				}
			}
		}

		return $tags;
	}

	/**
	 * Sets Post Status
	 * can handle single OR multiple rows
	 *
	 * @param int|array $ids
	 * @param string $status ( 'draft' OR 'published' )
	 *
	 *  @return bool
	 */
	public function set_posts_status($ids, $status) {

		$ids = $this->addslashes($ids);
		$status = strtolower($this->addslashes($status));

		$success = false;

		if ($status == BLOG_POST_STATUS_DRAFT OR $status == BLOG_POST_STATUS_PUBLISHED) {

			$this->db->set('blog_post_status', $status);
			$this->db->where_in('blog_post_id', $ids);
			$return = $this->db->update($this->tables["blog_posts"]);
			$success = $this->db->affected_rows() > 0 ? true : false;
		} else {
			// invalid status provided
			$success = false;
		}


		return $success;
	}

	/**
	 * Deletes Posts
	 *
	 * @param array $ids list of IDs to delete
	 *
	 * @return bool
	 */
	public function delete_posts($ids) {

		$ids = $this->addslashes($ids);

		$this->db->where_in('blog_post_id', $ids);
		return $this->db->delete($this->tables['blog_posts']);
	}

	/**
	 * Deletes Categories
	 *
	 * @param array $ids list of IDs to delete
	 * @param bool $isFixOrphans if orphan posts ( NOT assigned to any category ) should be assigned to default category ( 'uncategorized' )
	 *
	 * @return bool
	 */
	public function delete_categs($ids, $isFixOrphans = true) {

		$ids = $this->addslashes($ids);

		$this->db->where('blog_categ_is_special', false);
		$this->db->where_in('blog_categ_id', $ids);
		$success = $this->db->delete($this->tables['blog_categories']);

		if ($isFixOrphans) {

			$success = $this->fix_orphan_posts();
		}

		return $success;
	}

	/**
	 * toggles visibility OR sets visibility ( if visibility provided )
	 * can handle single OR multiple rows
	 *
	 * if $visibile is NOT provided, --> toggle mode
	 * if provided --> set mode
	 *
	 * @param int|array $ids
	 * @param bool $visible
	 *
	 *  @return bool
	 */
	public function toggle_categs_visibility($ids, $visible = null) {

		$ids = $this->addslashes($ids);
		$visible = $this->addslashes($visible);

		if (!is_null($visible)) {

			// set to provided visibility status
			$this->db->set('blog_categ_is_visible', $visible);
		} else {
			// toggle current state
			$this->db->set('blog_categ_is_visible', " ! blog_categ_is_visible", false);
		}

		$this->db->where_in('blog_categ_id', $ids);
		$return = $this->db->update($this->tables["blog_categories"]);
		return $this->db->affected_rows() != -1 ? true : false;
	}

	/**
	 * toggles is_comments OR sets is_comments ( if is_comments provided )
	 * can handle single OR multiple rows
	 *
	 * if $is_comments is NOT provided, --> toggle mode
	 * if provided --> set mode
	 *
	 * @param int|array $ids
	 * @param bool $is_comments
	 *
	 * @return bool
	 */
	public function toggle_categs_comments($ids, $is_comments = null) {

		$ids = $this->addslashes($ids);
		$is_comments = $this->addslashes($is_comments);

		if (!is_null($is_comments)) {

			// set to provided visibility status
			$this->db->set('blog_categ_is_comments', $is_comments); // do not escape
		} else {
			// toggle current state
			$this->db->set('blog_categ_is_comments', " ! blog_categ_is_comments", false); // do not escape
		}

		$this->db->where_in('blog_categ_id', $ids);
		$return = $this->db->update($this->tables["blog_categories"]);
		return $this->db->affected_rows() != -1 ? true : false;
	}

	/**
	 * Updates post-categ relationship
	 * if a particular post is NOT assigned to any category it is assigned by default to 'uncategorized' category
	 *
	 * @return bool
	 */
	public function fix_orphan_posts() {

		$sql = "
			INSERT INTO " . $this->tables['blog_post_categs'] . "

			(`blog_post_categ_categ_slug`, `blog_post_categ_post_id`)

			SELECT
			'" . BLOG_DEFAULT_CATEG_SLUG . "', blog_post_id

			FROM
			" . $this->tables['blog_posts'] . "

			WHERE
			`blog_post_id` NOT IN ( SELECT DISTINCT blog_post_categ_post_id FROM " . $this->tables['blog_post_categs'] . " )
		";

		return $this->db->query($sql);
		;
	}

}

?>
