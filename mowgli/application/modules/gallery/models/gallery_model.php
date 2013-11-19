<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of gallery_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Gallery_Model extends Site_Model {

	private $module = "gallery";
	private $config_database = "gallery_config_database";
	protected $tables = array();

	/**
	 * Constructor
	 *
	 * @param array(
	 *      'module' => 'module name',
	 *      'database_config' => 'db config file name'
	 * )
	 *
	 */
	public function __construct() {

		parent::__construct();

		$this->config->load($this->config_database, true, true, $this->module);

		$this->tables["gallery_items"] = $this->config->item("tbl_gallery_items", $this->config_database);
		$this->tables["gallery_themes"] = $this->config->item("tbl_gallery_themes", $this->config_database);
		$this->tables["gallery_templates"] = $this->config->item("tbl_gallery_templates", $this->config_database);
		$this->tables["gallery_resources"] = $this->config->item("tbl_gallery_resources", $this->config_database);

		! defined('GALLERY_TYPE_CATEG') ? define('GALLERY_TYPE_CATEG', 'category') : null;
		! defined('GALLERY_TYPE_IMG') ? define('GALLERY_TYPE_IMG', 'image') : null;
	}

	/*	 * ****** Themes ************** */



	/*
	 * Inserts Theme
	 * @return id
	 */

	public function create_theme($themeDb) {

		$themeDb = $this->addslashes($themeDb);
		$this->db->insert($this->tables["gallery_themes"], $themeDb);
		return $this->db->insert_id();
	}

	public function create_tag($tagDb) {

		$this->load->model('tags_model');
		$this->tags_model->set_db($this->db);
		return $this->tags_model->create_tag($tagDb);
	}

	public function delete_themes($themeIds) {

		$themeIds = $this->addslashes($themeIds);

		$this->db->where_in('gallery_theme_id', $themeIds);
		$this->db->delete($this->tables["gallery_themes"]);
		return $this->db->affected_rows();
	}

	// returns true on success, false on failure
	public function create_resources($resources) {

		foreach ($resources as & $resource) {
			$resource = $this->addslashes($resource);
		}

		//$this->db->ignore( $isInsertIgnore );
		$return = $this->db->insert_batch($this->tables["gallery_resources"], $resources);
		//$this->db->ignore( false ); // reset

		return $return === true ? true : false;
	}

	// create batch of templates, if success return true, else return false
	public function create_templates($templates) {

		foreach ($templates as & $template) {
			$template = $this->addslashes($template);
		}

		$return = $this->db->insert_batch($this->tables["gallery_templates"], $templates);

		return $return === true ? true : false;
	}

	public function get_theme_template($theme, $type, $template = null) {

		$themeTempData = null;

		$theme = $this->addslashes($theme);
		$type = $this->addslashes($type);
		$template = $this->addslashes($template);

		/* 	AVAILABLE columns
		  theme.gallery_theme_id                AS theme_id,
		  theme.gallery_theme_name              AS theme_name,
		  theme.gallery_theme_resource_uri      AS theme_resource_uri,
		  theme.gallery_theme_scripts           AS theme_scripts,

		  temp.gallery_template_id             AS temp_id,
		  temp.gallery_template_theme_name     AS temp_theme,
		  temp.gallery_template_type           AS temp_type,
		  temp.gallery_template_name           AS temp_name,
		  temp.gallery_template_scripts        AS temp_scripts,
		  temp.gallery_template_html           AS temp_html,
		  temp.gallery_template_created        AS temp_created,
		  temp.gallery_template_modified       AS temp_modified,
		  temp.gallery_template_is_visible     AS temp_visible
		 */

		$select = "
                        theme.gallery_theme_name              AS theme_name,
                        theme.gallery_theme_resource_uri      AS theme_resource_uri,
                        theme.gallery_theme_scripts           AS theme_scripts,

                        temp.gallery_template_type           AS temp_type,
                        temp.gallery_template_name           AS temp_name,
                        temp.gallery_template_scripts        AS temp_scripts,
                        temp.gallery_template_html           AS temp_html

			";

		$this->db->select($select, false); // escape = false
		$this->db->from($this->tables['gallery_themes'] . " AS theme");
		$this->db->join($this->tables["gallery_templates"] . " AS temp", "temp.gallery_template_theme_name = theme.gallery_theme_name", 'left');
		$this->db->where('theme.gallery_theme_name', $theme);

		$this->db->where('temp.gallery_template_type', $type);
		$this->db->where('temp.gallery_template_is_visible', true);
		if (!is_null($template)) {

			$this->db->where('temp.gallery_template_name', $template);
		}
		$this->db->limit(1);

		$query = $this->db->get();

//		$sql = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$themeTempData = $query->row_array();

			// strip slashes
			foreach ($themeTempData as $key => $value) {

				$themeTempData[$key] = stripslashes($value);
			}
		}

		return $themeTempData;
	}

	public function get_theme_details($themeName) {

		$themeDetails = null;

		$themeName = $this->addslashes($themeName);

		$select = "
                        gallery_theme_id                AS id,
                        gallery_theme_name              AS name,
                        gallery_theme_resource_uri      AS resource_uri,
                        gallery_theme_scripts           AS scripts ";

		$this->db->select($select, false); // false --> do not escape string
		$this->db->from($this->tables['gallery_themes']);
		$this->db->where('gallery_theme_name', $themeName);
		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$themeDetails = $query->row_array();

			// strip slashes
			foreach ($themeDetails as $key => $value) {

				$themeDetails[$key] = stripslashes($value);
			}
		}

		return $themeDetails;
	}

	public function get_theme_names() {

		$themes = null;

		$select = "
                        gallery_theme_name              AS name";

		$this->db->select($select);
		$this->db->from($this->tables['gallery_themes']);
//		$this->db->where('gallery_theme_name', $themeName);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$themesData = $query->result_array();

			// strip slashes
			foreach ($themesData as $key => $row) {

				$themes[] = stripslashes($themesData[$key]['name']);
			}
		}

		return $themes;
	}

	// NOT USED, as of now BUT CAN BE USED to get ONLY gallery template data, better to use get_theme_template() instead
	public function get_template($theme, $type, $template = null) {

		$theme = $this->addslashes($theme);
		$type = $this->addslashes($type);
		$template = $this->addslashes($template);

		$template = null;

		$select = "
                        gallery_template_id             AS id,
                        gallery_template_theme_name     AS theme,
                        gallery_template_type           AS type,
                        gallery_template_name           AS name,
                        gallery_template_scripts        AS scripts,
                        gallery_template_html           AS html,
                        gallery_template_created        AS created,
                        gallery_template_modified       AS modified,
                        gallery_template_is_visible     AS visible
                        ";

		$this->db->select($select);
		$this->db->from($this->tables["gallery_templates"]);
		$this->db->where('gallery_template_theme_name', $theme);
		$this->db->where('gallery_template_type', $type);
		$this->db->where('gallery_template_is_visible', 1);
		if (!is_null($template))
			$this->db->where('gallery_template_name', $template);

		$this->db->limit(1);

		$query = $this->db->get();


		if ($query->num_rows() > 0) {

			$template = $query->row_array();

			foreach ($template as $key => $value) {

				$template[$key] = stripslashes($value);
			}
		}

		return $template;
	}

	/*	 * ******************** Categories & Images related *************************************** */

	/*
	 * returns ItemId ( category ) for provided categ name,
	 * if name not provided, returns id for default categ
	 *
	 * @param string $categName
	 * @return string|null
	 */

	public function get_categ_id($categName = null) {

		$categId = null;

		// check if categ name provided, if not use default
		if (is_null($categName)) {

			$categName = $this->config->item("items_categ_default", $this->config_database);
		}

		$categName = $this->addslashes($categName);

		$this->db->select('gallery_item_id');
		$this->db->from($this->tables["gallery_items"]);
		$this->db->where('gallery_item_name', $categName);
		$this->db->where('gallery_item_type', 'category');
		$this->db->limit(1);

		$query = $this->db->get();
		if ($query->num_rows() > 0) {

			$item = $query->row_array();
			$categId = $item['gallery_item_id'];
		}

		return $categId;
	}

	/*
	 * Inserts Items
	 * @return id
	 */

	public function create_item($itemDB) {

		$itemDB = $this->addslashes($itemDB);
		$this->db->insert($this->tables["gallery_items"], $itemDB);
		return $this->db->insert_id();
	}

	/**
	 * Returns max order from list of categories OR
	 * max value of image from particular category
	 *
	 * @param string $type 'category' OR 'image'
	 * @param int $categId id of parent category ( optional -- only needed if type = 'image' )
	 *
	 * @return int|null
	 */
	public function get_max_item_order($type, $categId = null) {

		$max = null;

		$typeCateg = $this->config->item("items_type_category", $this->config_database);
		$typeImage = $this->config->item("items_type_image", $this->config_database);

		/* Set max value as 0 if no result found */
		$select = "IF( ISNULL( MAX( gallery_item_order ) ), 0, MAX( gallery_item_order ) ) AS max";
		$this->db->select($select, false);
		$this->db->from($this->tables["gallery_items"]);

		switch ($type) {

			case $typeCateg:

				$this->db->where('gallery_item_type', $typeCateg);
				$query = $this->db->get();
				if ($query->num_rows() > 0) {

					$row = $query->row_array();
					$max = $row['max'];
				}
				break;

			case $typeImage:

				if (!is_null($categId)) {
					$this->db->where('gallery_item_parent_id', $categId);
					$this->db->where('gallery_item_type', $typeImage);
					$query = $this->db->get();
					if ($query->num_rows() > 0) {

						$row = $query->row_array();
						$max = $row['max'];
					}
				}

				break;

			default:
				$max = null;
				break;
		}

		return $max;
	}

	public function get_categories($imageRoot, $categIds = null, $onlyVisible = true, $showHiddenCoverImgs = true) {

		/**
		 *
		  SELECT

		  categ.gallery_item_id                 AS id,
		  categ.gallery_item_cover_id           AS cover_id,
		  categ.gallery_item_type               AS type,
		  categ.gallery_item_name               AS name,
		  categ.gallery_item_name_url           AS name_url,
		  ( SELECT COUNT( * )
		  FROM gallery_items AS t3
		  WHERE t3.gallery_item_parent_id = categ.gallery_item_id ) AS count,
		  categ.gallery_item_desc               AS description,
		  categ.gallery_item_alt                AS alt,
		  if( image.gallery_item_is_visible = 1 ,
		  CONCAT( @baseuri, image.gallery_item_uri ) ,
		  NULL
		  )                                  AS uri,
		  if( image.gallery_item_is_visible = 1 ,
		  CONCAT( @baseuri, image.gallery_item_uri_thumb ) ,
		  NULL
		  )                                   AS uri_thumb,
		  categ.gallery_item_order              AS `order`,
		  categ.gallery_item_created            AS created,
		  categ.gallery_item_modified           AS modified,
		  categ.gallery_item_is_visible           AS visible

		  FROM
		  gallery_items AS categ

		  LEFT JOIN gallery_items AS image ON categ.gallery_item_cover_id = image.gallery_item_id

		  WHERE
		  -- categ.gallery_item_id IN ( $categIds ) AND
		  categ.gallery_item_type = 'category' AND
		  categ.gallery_item_is_visible = 1

		  ORDER BY
		  categ.gallery_item_order ASC,
		  categ.gallery_item_name ASC

		 */
		$categories = null;

		/* get type for categories ( = 'category' ) */
		$type = $this->config->item("items_type_category", $this->config_database);

		$selectImageCount = "SELECT COUNT( * ) FROM " . $this->tables["gallery_items"] . " AS t3 WHERE t3.gallery_item_parent_id = categ.gallery_item_id";

		/* Set base uri for images and thumbnails, later concatinated with images */
		$this->db->query('SET @baseuri="' . $imageRoot . '"');

//		$select = "
//                        categ.gallery_item_id                 AS id,
//                        categ.gallery_item_cover_id           AS cover_id,
//                        categ.gallery_item_type               AS type,
//                        categ.gallery_item_name               AS name,
//                        categ.gallery_item_name_url           AS name_url,
//                        ( $selectImageCount )                 AS count,
//                        categ.gallery_item_desc               AS description,
//                        categ.gallery_item_alt                AS alt,
//                        if( image.gallery_item_is_visible = 1 ,
//                                CONCAT( @baseuri, image.gallery_item_uri ) ,
//                                NULL
//                           )                                  AS uri,
//                        if( image.gallery_item_is_visible = 1 ,
//                                CONCAT( @baseuri, image.gallery_item_uri_thumb ) ,
//                                NULL
//                          )                                   AS uri_thumb,
//                        categ.gallery_item_order              AS `order`,
//                        categ.gallery_item_created            AS created,
//                        categ.gallery_item_modified           AS modified,
//                        categ.gallery_item_is_visible           AS visible
//                        ";

		$select = "
                        categ.gallery_item_id                 AS id,
                        categ.gallery_item_cover_id           AS cover_id,
                        categ.gallery_item_type               AS type,
                        categ.gallery_item_name               AS name,
                        categ.gallery_item_name_url           AS name_url,
                        ( $selectImageCount )                 AS count,
                        categ.gallery_item_desc               AS description,
                        categ.gallery_item_alt                AS alt,
                        categ.gallery_item_order              AS `order`,
                        categ.gallery_item_created            AS created,
                        categ.gallery_item_modified           AS modified,
                        categ.gallery_item_is_visible           AS visible,

                        ";
		// add cover image logic
		if ($showHiddenCoverImgs === true) {
			$select .= "CONCAT( @baseuri, image.gallery_item_uri ) AS uri,
                        CONCAT( @baseuri, image.gallery_item_uri_thumb ) AS uri_thumb,";
		} else {
			$select .= "
			if( image.gallery_item_is_visible = 1 ,CONCAT( @baseuri, image.gallery_item_uri ), NULL) AS uri,
                        if( image.gallery_item_is_visible = 1 ,CONCAT( @baseuri, image.gallery_item_uri_thumb ), NULL) AS uri_thumb,";
		}

		$this->db->select($select, false); // false = do not escape
		$this->db->from($this->tables["gallery_items"] . " AS categ");
		$this->db->join($this->tables["gallery_items"] . " AS image", 'categ.gallery_item_cover_id = image.gallery_item_id', 'left');
		$this->db->where('categ.gallery_item_type', $type);


		/* Get specific Categories if $categIds defined */
		if (!is_null($categIds)) {

			$categIds = $this->addslashes($categIds);
			$this->db->where_in('categ.gallery_item_id', $categIds);
		}

		if ($onlyVisible) {

			$this->db->where('categ.gallery_item_is_visible', 1);
		}

		$this->db->order_by('categ.gallery_item_order', 'asc');
		$this->db->order_by('categ.gallery_item_name', 'asc');

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$categories = $query->result_array();

			// strip slashes
			foreach ($categories as & $row) {

				foreach ($row as $key => $value) {

					$row[$key] = stripslashes($value);
				}
			}
		}

		return $categories;
	}

	public function get_images_by_categories($imageRoot, $categIds = null, $onlyVisible = true, $orderOnlyByImages = false) {

		/**
		 *
		  SELECT

		  image.gallery_item_id          AS id,
		  image.gallery_item_parent_id   AS parent_id,
		  categ.gallery_item_name        AS category,
		  image.gallery_item_type        AS type,
		  image.gallery_item_name        AS name,
		  image.gallery_item_name_url    AS name_url,
		  image.gallery_item_desc        AS description,
		  image.gallery_item_alt         AS alt,
		  CONCAT( @baseuri, image.gallery_item_uri )         AS uri,
		  CONCAT( @baseuri, image.gallery_item_uri_thumb )   AS uri_thumb,
		  image.gallery_item_order       AS `order`,
		  image.gallery_item_created     AS created,
		  image.gallery_item_modified    AS modified,
		  image.gallery_item_is_visible  AS visible

		  FROM
		  gallery_items AS categ

		  LEFT JOIN gallery_items AS image ON image.gallery_item_parent_id = categ.gallery_item_id

		  WHERE
		  -- categ.gallery_item_id IN ( $categIds ) AND
		  categ.gallery_item_type = 'category'
		  -- categ.gallery_item_is_visible = 1 AND
		  image.gallery_item_is_visible = 1

		 */
		$images = null;

		/* get type for images ( = 'category' ) */
		$typeCateg = $this->config->item("items_type_category", $this->config_database);
		$typeImage = $this->config->item("items_type_image", $this->config_database);

		/* Set base uri for images and thumbnails, later concatinated with images */
		$this->db->query('SET @baseuri="' . $imageRoot . '"');


		/* OLD CODE
		  $select = "
		  image.gallery_item_id          AS id,
		  image.gallery_item_parent_id   AS parent_id,
		  categ.gallery_item_name        AS category,
		  image.gallery_item_type        AS type,
		  image.gallery_item_name        AS name,
		  image.gallery_item_name_url    AS name_url,
		  image.gallery_item_desc        AS description,
		  image.gallery_item_alt         AS alt,
		  image.gallery_item_uri         AS uri,
		  image.gallery_item_uri_thumb   AS uri_thumb,
		  image.gallery_item_order       AS `order`,
		  image.gallery_item_created     AS created,
		  image.gallery_item_modified    AS modified,
		  image.gallery_item_is_visible  AS visible
		  ";
		 */
		$select = "
                         image.gallery_item_id          AS id,
                         image.gallery_item_parent_id   AS parent_id,
                         categ.gallery_item_name        AS category,
                         image.gallery_item_type        AS type,
                         image.gallery_item_name        AS name,
                         image.gallery_item_name_url    AS name_url,
                         image.gallery_item_desc        AS description,
                         image.gallery_item_alt         AS alt,
                         CONCAT( @baseuri, image.gallery_item_uri )         AS uri,
                         CONCAT( @baseuri, image.gallery_item_uri_thumb )   AS uri_thumb,
                         image.gallery_item_order       AS `order`,
                         image.gallery_item_created     AS created,
                         image.gallery_item_modified    AS modified,
                         image.gallery_item_is_visible  AS visible
                ";

		$this->db->select($select);
		$this->db->from($this->tables["gallery_items"] . " AS categ");
		$this->db->join($this->tables["gallery_items"] . " AS image", 'image.gallery_item_parent_id = categ.gallery_item_id', 'left');
		$this->db->where('categ.gallery_item_type', $typeCateg);
		$this->db->where('image.gallery_item_type', $typeImage);


		if ($onlyVisible) {
			$this->db->where('categ.gallery_item_is_visible', true);
			$this->db->where('image.gallery_item_is_visible', true);
		}


		/* Get specific Categories if $categIds defined */
		if (!is_null($categIds)) {

			$categIds = $this->addslashes($categIds);
			$this->db->where_in('categ.gallery_item_id', $categIds);
		}


		// prevent regular ordering if ordering should be only by images
		if ($orderOnlyByImages === false) {

			// order by category first
			$this->db->order_by('categ.gallery_item_name', 'asc');
		}

		// order by order
		$this->db->order_by('image.gallery_item_order', 'asc');

		$query = $this->db->get();

		$sql = $this->db->last_query();

		if ($query->num_rows() > 0) {

			$images = $query->result_array();

			// strip slashes
			foreach ($images as & $row) {

				foreach ($row as $key => $value) {

					$row[$key] = stripslashes($value);
				}
			}
		}

		return $images;
	}

	public function get_images($imageRoot, $imageIds = null, $onlyVisible = true) {

		/**
		 *
		  SELECT

		  image.gallery_item_id          AS id,
		  image.gallery_item_parent_id   AS parent_id,
		  categ.gallery_item_name        AS category,
		  image.gallery_item_type        AS type,
		  image.gallery_item_name        AS name,
		  image.gallery_item_name_url    AS name_url,
		  image.gallery_item_desc        AS description,
		  image.gallery_item_alt         AS alt,

		  CONCAT( @baseuri, image.gallery_item_uri )         AS uri,
		  CONCAT( @baseuri, image.gallery_item_uri_thumb )   AS uri_thumb,

		  image.gallery_item_order       AS `order`,
		  image.gallery_item_created     AS created,
		  image.gallery_item_modified    AS modified,
		  image.gallery_item_is_visible  AS visible

		  FROM
		  gallery_items AS image

		  WHERE
		  image.gallery_item_id IN ( $imageIds ) AND
		  image.gallery_item_is_visible = 1

		 */
		$images = null;

		/* get type for images ( = 'category' ) */
//                $type = $this->config->item( "items_type_image", $this->config_database );

		/* Set base uri for images and thumbnails, later concatinated with images */
		$this->db->query('SET @baseuri="' . $imageRoot . '"');

		/* OLD CODE
		  $select = "
		  image.gallery_item_id          AS id,
		  image.gallery_item_parent_id   AS parent_id,
		  categ.gallery_item_name        AS category,
		  image.gallery_item_type        AS type,
		  image.gallery_item_name        AS name,
		  image.gallery_item_name_url    AS name_url,
		  image.gallery_item_desc        AS description,
		  image.gallery_item_alt         AS alt,
		  image.gallery_item_uri         AS uri,
		  image.gallery_item_uri_thumb   AS uri_thumb,
		  image.gallery_item_order       AS `order`,
		  image.gallery_item_created     AS created,
		  image.gallery_item_modified    AS modified,
		  image.gallery_item_is_visible  AS visible
		  ";
		 */
		$select = "
                         image.gallery_item_id          AS id,
                         image.gallery_item_parent_id   AS parent_id,
                         categ.gallery_item_name        AS category,
                         image.gallery_item_type        AS type,
                         image.gallery_item_name        AS name,
                         image.gallery_item_name_url    AS name_url,
                         image.gallery_item_desc        AS description,
                         image.gallery_item_alt         AS alt,

                         CONCAT( @baseuri, image.gallery_item_uri )         AS uri,
                         CONCAT( @baseuri, image.gallery_item_uri_thumb )   AS uri_thumb,

                         image.gallery_item_order       AS `order`,
                         image.gallery_item_created     AS created,
                         image.gallery_item_modified    AS modified,
                         image.gallery_item_is_visible  AS visible
                ";

		$this->db->select($select);
		$this->db->from($this->tables["gallery_items"] . " AS categ");
		$this->db->join($this->tables["gallery_items"] . " AS image", 'image.gallery_item_parent_id = categ.gallery_item_id', 'left');
//                $this->db->where( 'categ.gallery_item_type', $type );
//                $this->db->where( 'categ.gallery_item_is_visible', 1 );
//                $this->db->where( 'image.gallery_item_is_visible', 1 );

		/* Get specific Categories if $imageIds defined */
		if (!is_null($imageIds)) {

			$imageIds = $this->addslashes($imageIds);
			$this->db->where_in('image.gallery_item_id', $imageIds);
		}

		if ($onlyVisible) {
			$this->db->where('image.gallery_item_is_visible', 1);
		}

		$this->db->order_by('categ.gallery_item_name', 'asc');   // order by category
		$this->db->order_by('image.gallery_item_order', 'asc');   // order by order

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$images = $query->result_array();

			// strip slashes
			foreach ($images as & $row) {

				foreach ($row as $key => $value) {

					$row[$key] = stripslashes($value);
				}
			}
		}

		return $images;
	}

	/**
	 * Gets the path + thumbnail path of all images or all images under specific category mentioned in list.
	 * if 'category' --> get url, url_thumb for all images under categIds
	 * if 'image' --> get url, url_thumb for imageIds
	 * returns null if invalid type provided
	 *
	 * @param string $imageRoot base path upto images path stored in db
	 * @param array $ids list of ids
	 * @param string $type value 'category' OR 'image'
	 *
	 * @return array[] list of image paths
	 */
	public function get_image_paths($imageRoot, $ids, $type) {

//                $imageRoot = $this->addslashes( $imageRoot );
		$ids = $this->addslashes($ids);
		$type = $this->addslashes($type);

		$ItemTypeCateg = $this->config->item('items_type_category', $this->config_database);
		$ItemTypeImage = $this->config->item('items_type_image', $this->config_database);

		$result = null; // set to null if error
//                $this->db->query( 'SET @baseuri="' . $imageRoot . '"');
		// get type of items
//                $select = "
//                         CONCAT( @baseuri, gallery_item_uri )         AS uri,
//                         CONCAT( @baseuri, gallery_item_uri_thumb )   AS uri_thumb,
//                        ";

		$select = "
                         gallery_item_uri         AS uri,
                         gallery_item_uri_thumb   AS uri_thumb,
                        ";

		$this->db->select($select);
		$this->db->from($this->tables["gallery_items"]);

		// select appropriate where clause depending on type
		switch ($type) {

			case $ItemTypeCateg:
				// type = category
				$this->db->where_in("gallery_item_parent_id", $ids);
				break;

			case $ItemTypeImage:
				// type = image
				$this->db->where_in("gallery_item_id", $ids);
				break;

			default:
				// Incorrect type provided
				$result = null;
				$this->db->where("1 = 0"); // evalutes to false always
				break;
		}

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$imagePaths = $query->result_array();

			// strip slashes
			foreach ($imagePaths as & $row) {

				foreach ($row as $key => $value) {

//                                        $row[$key] = stripslashes( $value );
					$result[] = $imageRoot . stripslashes($value); // add path & thumb_path to same array as list
				}
			}
		}

		return $result;
	}

	/* @return bool */

	public function edit_item($id, $data) {

		$id = $this->addslashes($id);
		$data = $this->addslashes($data);

		$this->db->where('gallery_item_id', $id);
		$return = $this->db->update($this->tables["gallery_items"], $data);
//                return $return == false ? false : true;
		return $this->db->affected_rows() > 0 ? true : false;
	}

	/* @return bool */

	public function edit_item_visibility($ids, $visible) {

		$ids = $this->addslashes($ids);
		$visible = $this->addslashes($visible);

		$this->db->set('gallery_item_is_visible', $visible);
		$this->db->where_in('gallery_item_id', $ids);
		$return = $this->db->update($this->tables["gallery_items"]);
//                return $return == false ? false : true;
		return $this->db->affected_rows() > 0 ? true : false;
	}

	public function images_change_category($oldCategId, $newCategId, $imageIds) {

		$success = false;

		$oldCategId = $this->addslashes($oldCategId);
		$newCategId = $this->addslashes($newCategId);
		$imageIds = $this->addslashes($imageIds);

		$this->db->set('gallery_item_parent_id', $newCategId, true); // false ==> do not escape values
		$this->db->where_in('gallery_item_id', $imageIds);
		$this->db->update($this->tables["gallery_items"]);

		/* if success, check if moved images is set as cover for previous categ
		 * if yes --> set old categ cover as null
		 */
		if ($this->db->affected_rows() > 0) {

			/*
			  UPDATE gallery_items

			  SET
			  `gallery_item_cover_id` = NULL

			  WHERE
			  `gallery_item_id` = $oldCategId AND
			  `gallery_item_cover_id` IN ( $imageIds )
			 */
			$this->db->_reset_write();
			$this->db->set('gallery_item_cover_id', 'null', false);
			$this->db->where('gallery_item_id', $oldCategId);
			$this->db->where_in('gallery_item_cover_id', $imageIds);
			$this->db->update($this->tables["gallery_items"]);

			$success = true;
		}

		return $success;
	}

	/**
	 * Reorders all categories between oldOrder and newOrder
	 *
	 * @param $oldOrder old position
	 * @param $newOrder new position
	 *
	 * @return bool
	 *
	 */
	public function change_position($oldId, $newId) {

		$success = false;

		$ci = & get_instance();

		$oldId = $this->addslashes($oldId);
		$newId = $this->addslashes($newId);


		$select = "
                        gallery_item_id AS id,
                        gallery_item_parent_id AS parent_id,
                        gallery_item_type AS type,
                        gallery_item_order AS `order`
                        ";
		$this->db->select($select);
		$this->db->from($this->tables["gallery_items"]);
		$this->db->where_in('gallery_item_id', array($oldId, $newId));

		$query = $this->db->get();

		if ($query->num_rows() > 0) {

			$items = $query->result_array();
			$ci->load->helper('array');

			// rearrange array by id
			$items = rearrange_array($items, 'id');

			// check for errors while rearranging array
			if (!is_null($items)) {

				$oldOrder = $items[$oldId]['order'];
				$type = $items[$oldId]['type'];
				$parentId = $items[$oldId]['parent_id'];

				$newOrder = $items[$newId]['order'];

				// check if both of same type, AND have same parent id
				if (( $items[$oldId]['type'] == $items[$newId]['type'] ) AND
					( $items[$oldId]['parent_id'] == $items[$newId]['parent_id'] )) {

					$success = $this->_change_position($oldOrder, $newOrder, $type, $parentId);
				} else {
					// both ids, not of same type OR same parent
					$success = false;
				}
			} else {
				// error while rearrangine array by id
				$success = false;
			}
		} else {
			// error retreiving data from db
			$success = false;
		}

		return $success;
	}

	public function reorder_categs($oldPosition, $newPosition){
		return $this->_change_position($oldPosition, $newPosition, GALLERY_TYPE_CATEG );
	}
	public function reorder_images($categId, $oldPosition, $newPosition){
		return $this->_change_position($oldPosition, $newPosition, GALLERY_TYPE_IMG, $categId);
	}


	private function _change_position($oldOrder, $newOrder, $type, $parentId = null) {

		$tableName = $this->tables["gallery_items"];
		$columnName = "gallery_item_order";

		$whereConditions = is_null($parentId) ? ' ' : " gallery_item_parent_id = $parentId AND ";
		$whereConditions .= "gallery_item_type = '" . $type . "'";

		// calls general reorder function from MY_Model

		return $this->reorder($tableName, $columnName, $oldOrder, $newOrder, $whereConditions);
	}

	/* @return bool */

	public function delete_items($ids) {

		$ids = $this->addslashes($ids);

		$this->db->where_in('gallery_item_id', $ids);
		return $this->db->delete($this->tables["gallery_items"]);
	}

}

?>
