<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of page_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//define( 'TBL_PAGE_BLOCKS', 'page_blocks');

class Page_Model extends Site_Model {

        private $module = "page";
//        private $config_database = "page_config_database";
//        private $config_settings = "blog_config_settings";

        protected $tables = array();

        public function __construct() {
                parent::__construct();

                $this->config->load('database_tables');
//                define( 'TBL_PAGE_BLOCKS', $this->config->item('TBL_PAGE_BLOCKS') );
//                define( 'TBL_PAGES', $this->config->item('TBL_PAGES') );
//                define( 'TBL_TEMPLATES', $this->config->item('TBL_TEMPLATES') );
//                define( 'TBL_TAGS', $this->config->item('TBL_TAGS') );
//                define( 'TBL_BLOCKS', $this->config->item('TBL_BLOCKS') );

                $this->tables["page_blocks"] = $this->config->item("TBL_PAGE_BLOCKS");
                $this->tables["pages"] = $this->config->item("TBL_PAGES");
                $this->tables["templates"] = $this->config->item("TBL_TEMPLATES");
                $this->tables["tags"] = $this->config->item("TBL_TAGS");
                $this->tables["blocks"] = $this->config->item("TBL_BLOCKS");
        }

        /**
         * Add a page to the database
         *
         * @param array $pageDB
         * @return int page id
         */
        public function add_page_from_template($pageDB, $tempId) {

                $pageDB = $this->addslashes($pageDB);
                $this->db->insert($this->tables["pages"], $pageDB);

                $pageId = $this->db->insert_id();

                $success = $this->_create_page_blocks_from_template($pageId, $tempId);
                return $success;
        }

        private function _create_page_blocks_from_template($pageId, $tempId) {

                /*
                  INSERT INTO `page_blocks`

                  (
                  `page_blocks_id`,
                  `page_blocks_page_id`,
                  `page_blocks_temp_id`,
                  `page_blocks_block_id`,
                  `page_blocks_tag_id`
                  )

                  ( SELECT NULL, $pageId, $tempId, block_id, NULL FROM blocks WHERE block_temp_id = $tempId )
                 */

                $sql = "
                        INSERT INTO " . $this->tables['page_blocks'] . "

                        (
                                `page_blocks_id`,
                                `page_blocks_page_id`,
                                `page_blocks_temp_id`,
                                `page_blocks_block_id`,
                                `page_blocks_tag_id`
                        )

                        (
                                SELECT NULL, $pageId, $tempId, block_id, NULL FROM blocks WHERE block_temp_id = $tempId
                        )
                        ";

                $success = $this->db->query($sql);
                return $success;
        }

        /**
         * Checks if page with given uri exists.
         * If exists returns page data (id, temp_id, title, description, keywords, html)
         * also returns HTML
         *
         * @param string $pageUri uri of page
         * @return array|false returns result on success, false on no result
         */
        public function get_page_details($pageSlug) {

                $pageDetails = false;

                $pageSlug = $this->addslashes($pageSlug);

                /*
                  SELECT
                  page_id AS id,
                  page_temp_id AS temp_id,
                  page_title AS title,
                  page_description AS description,
                  page_keywords AS keywords,
                  temp_html AS html

                  FROM pages

                  -- LEFT JOIN page_blocks ON page_id = page_blocks_page_id
                  -- LEFT JOIN templates ON page_blocks_temp_id = temp_id
                  LEFT JOIN templates ON page_temp_id = temp_id

                  WHERE page_slug = '$pageUri' AND page_is_visible = 1
                 */

                $select = "page_id AS id,
                            temp_id,
                            page_title AS title,
                            page_description AS description,
                            page_keywords AS keywords,
                            temp_html AS html";

                $this->db->select($select);
                $this->db->from($this->tables["pages"]);
                //$this->db->join( $this->tables["page_blocks"], 'page_id = page_blocks_page_id', 'left');
                //$this->db->join( $this->tables["templates"], 'page_blocks_temp_id = temp_id', 'left');
                $this->db->join($this->tables["templates"], 'page_temp_id = temp_id', 'left');
                $this->db->where('page_slug', $pageSlug);
                $this->db->where('page_is_visible', 1);
                $this->db->limit(1);

                $query = $this->db->get();

		$sql = $this->db->last_query();

                if ($query->num_rows() > 0) {

                        $pageDetails = $query->row_array();

                        // strip slashes
                        foreach ($pageDetails as $key => $value) {
                                $pageDetails[$key] = stripslashes($value);
                        }
                }

                return $pageDetails;
        }

        /**
         * gets page details for given page Id
         *
         * @param string $pageUri uri of page
         * @return array|false returns result on success, false on no result
         */
//        public function get_page_meta($pageId) {
//
//                $pageDetails = null;
//
//                $pageId = addslashes($pageId);
//
//                /*
//                  SELECT
//                  page_id AS id,
//                  page_name AS name,
//                  page_slug AS slug,
//                  page_redirect AS redirect,
//                  page_title AS title,
//                  page_description AS description,
//                  page_keywords AS keywords
//
//                  FROM pages
//
//                  WHERE page_id = $pageid
//                 */
//
//                $select = "page_id AS id,
//                page_name AS name,
//                page_slug AS slug,
//                page_redirect AS redirect,
//                page_title AS title,
//                page_description AS description,
//                page_keywords AS keywords";
//
//                $this->db->select($select);
//                $this->db->from($this->tables['pages']);
//                $this->db->where('page_id', $pageId);
//                $this->db->limit(1);
//
//                $query = $this->db->get();
//
//                if ($query->num_rows() > 0) {
//
//                        $pageDetails = $query->row_array();
//
//                        // strip slashes
//                        foreach ($pageDetails as $key => $value) {
//                                $pageDetails[$key] = stripslashes($value);
//                        }
//                }
//
//                return $pageDetails;
//        }

        public function get_pages_meta_data($pageId = null, $limit = null, $offset = 0) {

                /**
                  SELECT
                  SQL_CALC_FOUND_ROWS
                  page_id AS id,
                  page_name AS name,
                  page_slug AS slug,
                  page_redirect AS redirect,
                  page_title AS title,
                  page_description AS description,
                  page_keywords AS keywords,

                  temp_id AS temp_id,
                  temp_name AS template,

                  page_modified AS modified,
                  page_is_visible AS published

                  FROM

                  pages
                  LEFT JOIN templates ON page_temp_id = temp_id

                  -- WHERE page_id = $pageId

                 */
                $pages = null;

                $select = "
                SQL_CALC_FOUND_ROWS
                page_id AS id,
                page_name AS name,
                page_slug AS slug,
                page_redirect AS redirect,
                page_title AS title,
                page_description AS description,
                page_keywords AS keywords,
                page_is_visible AS is_visible,

                temp_id AS temp_id,
                temp_name AS template,

                page_modified AS modified,
                page_is_visible AS is_visible";

                $this->db->select($select, false);
                $this->db->from($this->tables['pages']);

                $singlerow = false;
                if (!is_null($pageId)) {
                        $singlerow = true;
                        $this->db->where('page_id', $pageId);
                        $this->db->limit(1);
                }
                $this->db->join($this->tables['templates'], 'page_temp_id = temp_id', 'left');
                $this->db->order_by('page_name', 'asc');

                $this->db->limit($limit, $offset);
                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $pages = $query->result_array();

                        foreach ($pages as & $row) {
                                // strip slashes
                                foreach ($row as $key => $value) {
                                        $row[$key] = stripslashes($value);
                                }
                        }

                        // if only single row required --> use only first row values
                        if ($singlerow AND isset($pages[0])) {
                                $pages = $pages[0];
                        }
                }

                return $pages;
        }

        public function check_page_exists($pageSlug) {

                $pageSlug = $this->addslashes($pageSlug);

                $select = "page_slug AS slug";

                $this->db->select($select);
                $this->db->from($this->tables["pages"]);
                $this->db->where('page_slug', $pageSlug);
                $this->db->where('page_is_visible', 1);
                $this->db->limit(1);

                $query = $this->db->get();

                return $query->num_rows() > 0 ? true : false;
        }

        /**
         * Gets [ temp_id, block, tag, module, html ] of block
         * for given pageId.
         * if block does not have a template, then html = null
         * Returns false if no results found
         *
         * @param int $pageId page id
         * @return array|false returns result array on success, false if no results found
         */
        public function get_blocks_for_page($pageId) {
                /*
                  SELECT
                  temp_id,
                  block_name AS block,
                  tag_keyword AS tag,
                  tag_module_name AS module,
                  temp_html AS html
                  -- ( if ( temp_id = page_blocks_temp_id, NULL, temp_html ) ) AS html

                  FROM page_blocks

                  LEFT JOIN blocks ON page_blocks_block_id = block_id
                  LEFT JOIN tags ON page_blocks_tag_id = tag_id
                  LEFT JOIN templates ON tag_temp_id = temp_id

                  WHERE page_blocks_page_id = $pageId

                  ORDER BY tag_module_name
                 */

                $blocks = false;

                $select = "
            temp_id,
            block_name AS block,
            tag_keyword AS tag,
            tag_module_name AS module,
            temp_html AS html ";
                //( if ( temp_id = page_blocks_temp_id, NULL, temp_html ) ) AS html "; // note: for page id use ==> page_blocks_page_id AS pageId,

                $this->db->select($select, false); // note false --> to prevent from escaping query automatically
                $this->db->from($this->tables["page_blocks"]);
                $this->db->join($this->tables["blocks"], 'page_blocks_block_id = block_id', 'left');
                $this->db->join($this->tables["tags"], 'page_blocks_tag_id = tag_id', 'left');
                $this->db->join($this->tables["templates"], 'tag_temp_id = temp_id', 'left');
                $this->db->where('page_blocks_page_id', $pageId);
                $this->db->order_by('tag_module_name', 'asc');

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $blocks = $query->result_array();

                        foreach ($blocks as & $row) {
                                // strip slashes
                                foreach ($row as $key => $value) {
                                        $row[$key] = stripslashes($value);
                                }
                        }
                }

                return $blocks;
        }

        /**
         * Returns [ temp_id, block_temp_id, block, tag, module, html ] of ALL blocks inside given template id or list of template Ids together
         *
         * @param array $tempIds List of all template ids (int) to search for
         * @return array|false returns result OR False if no result found
         */
        public function get_blocks_for_templates($tempIds) {

                /*
                  SELECT
                  t1.temp_id AS temp_id,
                  t2.temp_id AS block_temp_id,
                  block_name AS block,
                  tag_keyword AS tag,
                  tag_module_name AS module,
                  t2.temp_html AS html

                  FROM templates t1

                  RIGHT JOIN blocks ON temp_id = block_temp_id
                  LEFT JOIN tags ON block_tag_id = tag_id
                  LEFT JOIN templates t2 ON tag_temp_id = t2.temp_id

                  WHERE t1.temp_id in (2, 3, 4, 5)

                  ORDER BY temp_id
                 */

                $blocks = false;

                $select = "
            t1.temp_id AS temp_id,
            t2.temp_id AS block_temp_id,
            block_name AS block,
            tag_keyword AS tag,
            tag_module_name AS module,
            t2.temp_html AS html";

                $this->db->select($select);
                $this->db->from($this->tables["templates"] . " AS t1");
                $this->db->join($this->tables["blocks"], 'temp_id = block_temp_id', 'right'); // note RIGHT JOIN --> will remove includes that do not have blocks
                $this->db->join($this->tables["tags"], 'block_tag_id = tag_id', 'left');
                $this->db->join($this->tables["templates"] . " AS t2", 'tag_temp_id = t2.temp_id', 'left');
                $this->db->where_in('t1.temp_id', $tempIds);
                $this->db->order_by('t1.temp_id', 'asc');

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $blocks = $query->result_array();

                        foreach ($blocks as & $row) {
                                // strip slashes
                                foreach ($row as $key => $value) {
                                        $row[$key] = stripslashes($value);
                                }
                        }
                }

                return $blocks;
        }

        /* @return bool */

        public function edit_page_meta($id, $data) {

                $id = $this->addslashes($id);
                $data = $this->addslashes($data);

                $this->db->where('page_id', $id);
                $return = $this->db->update($this->tables["pages"], $data);
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
        public function check_is_page_slug_unique($slug, $excludeId = null) {

                $isUnique = false;

                $slug = $this->addslashes($slug);
                $select = "count(*) as count";
                $this->db->select($select);
                $this->db->from($this->tables['pages']);
                $this->db->where('page_slug', $slug);


                // exclude this id from check ( to test in case of editing )
                if (!is_null($excludeId) AND $excludeId != '')
                        $this->db->where('page_id !=', $excludeId);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $row = $query->row_array();
                        $isUnique = $row['count'] > 0 ? false : true;
                }

                return $isUnique;
        }

}

?>
