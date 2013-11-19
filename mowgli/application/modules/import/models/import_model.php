<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of page_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//define('TBL_TEMPLATES', 'templates');
//define('TBL_BLOCKS', 'blocks');
//define('TBL_PAGES', 'pages');
//define('TBL_PAGE_BLOCKS', 'page_blocks');
//define('TBL_RESOURCES', 'resources');
// define('WM_TEMPLATE_TYPE_PAGE', 'page');


class Import_Model extends Site_Model {

    protected $tables = array();

    public function __construct() {
        parent::__construct();

        $this->config->load('database_tables');
//                define('TBL_PAGE_BLOCKS', $this->config->item('TBL_PAGE_BLOCKS') );
//                define('TBL_PAGES', $this->config->item('TBL_PAGES') );
//                define('TBL_TEMPLATES', $this->config->item('TBL_TEMPLATES') );
//                define('TBL_BLOCKS', $this->config->item('TBL_BLOCKS') );
//                define('TBL_RESOURCES', $this->config->item('TBL_RESOURCES') );
//                define('TBL_TAGS', $this->config->item('TBL_TAGS') );

        $this->tables["page_blocks"] = $this->config->item('TBL_PAGE_BLOCKS');
        $this->tables["pages"] = $this->config->item('TBL_PAGES');
        $this->tables["templates"] = $this->config->item('TBL_TEMPLATES');
        $this->tables["blocks"] = $this->config->item('TBL_BLOCKS');
        $this->tables["resources"] = $this->config->item('TBL_RESOURCES');
        $this->tables["tags"] = $this->config->item('TBL_TAGS');
    }

    public function create_resources($resourcesDb, $isInsertIgnore = false) {

        // Add slashes
        foreach ($resourcesDb as & $resource) {
            $resource = $this->addslashes($resource);
        }

        // ignore the insert if already exists ( note this is a custom feature, NOT originally available with CodeIgniter )
        $this->db->ignore($isInsertIgnore);
        $return = $this->db->insert_batch($this->tables["resources"], $resourcesDb);
        $this->db->ignore(false); // reset

        return $return;
    }

    public function create_tag($tagDb) {

        $this->load->model('tags_model');
        $this->tags_model->set_db($this->db);
        return $this->tags_model->create_tag($tagDb);
    }

    public function get_templates_starts_with($tempName, $tempType) {

        $tempName = $this->addslashes($tempName);
        $tempType = $this->addslashes($tempType);

        $temp_names = array();

        $this->db->select('temp_name');

        $where = " (
            temp_name = '" . $tempName . "' OR temp_name LIKE '" . $tempName . "%' )
            AND temp_type = '" . $tempType . "'";

        $this->db->where($where, null, false);

        $query = $this->db->get($this->tables["templates"]);

        foreach ($query->result_array() as $row) {
            $temp_names[] = stripslashes($row['temp_name']);
        }
        return $temp_names;
    }

    public function get_pages_starts_with($pageName, $pageSlug, $pageUri = null) {

        $pageName = $this->addslashes($pageName);
        $pageSlug = $this->addslashes($pageSlug);
        $pageUri = $this->addslashes($pageUri); // pageUri depricated

        $page_names = null;

        $this->db->select('page_name');
        $where = "page_name LIKE '$pageName%' OR page_slug = '$pageSlug'";
        // $where = "page_name LIKE '$pageName%' OR page_slug = '$pageSlug' OR page_uri = '$pageUri'";
        $this->db->where($where, null, false);

        $query = $this->db->get($this->tables["pages"]);

        foreach ($query->result_array() as $row) {
            $page_names[] = stripslashes($row['page_name']);
        }

        return $page_names;
    }

    /**
     * Get id for given templateName and templateType from templates table
     * if match NOT found --> return false
     */
    public function get_template_id_from_name($tempName, $tempType) {
        $tempName = $this->addslashes($tempName);
        $tempType = $this->addslashes($tempType);
        $this->db->select('temp_id');
        $where = "temp_name = '$tempName' AND temp_type = '$tempType'";
        $this->db->where($where, null, false);
        $this->db->limit(1);
        $query = $this->db->get($this->tables["templates"]);
        return ( ( $query->num_rows() > 0 ) ? $query->row()->temp_id : FALSE );
    }

    /**
     * adds template to database
     * @param array data array
     * @return int|bool returns (int)number of rows affected on success, false on failure
     */
    public function create_template($dataArray) {

        /* Add slashes to database entires */
//        foreach ($dataArray as $col => $value) {
//            if( is_string( $value ) ){
//                $dataArray[$col] = $this->addslashes( $value );
//            }
//
//        }
        $dataArray = $this->addslashes($dataArray);

        $this->db->insert($this->tables["templates"], $dataArray);

        return $this->db->insert_id();
    }

    public function create_blocks($blocksDb) {

        /* Add slashes */
//        foreach ($blocksDb as $block) {
//            foreach( $block as $col => $value ){
//                $block[$col] = $this->addslashes($value);
//            }
//        }
        foreach ($blocksDb as & $block) {
            $block = $this->addslashes($block);
        }

        return $this->db->insert_batch($this->tables["blocks"], $blocksDb);
    }

    public function edit_template_by_id($dataArray, $id) {

        /* Add slashes to database entires */
//        foreach ($dataArray as $col => $value) {
//            $dataArray[$col] = addslashes( $value );
//        }
        $dataArray = $this->addslashes($dataArray);

        $this->db->where('temp_id', $id);
        return $this->db->update($this->tables["templates"], $dataArray);
    }

    public function create_page($dataArray) {

        $dataArray = $this->addslashes($dataArray);
        $this->db->insert($this->tables["pages"], $dataArray);
        return $this->db->insert_id();
    }

    public function create_page_blocks_from_template($pageId, $tempId) {

        /**
         * @todo Check if dbprefix works for manual query
         */
        $sql = "INSERT INTO  " . $this->tables["page_blocks"] . "
                    (
                        `page_blocks_id` ,
                        `page_blocks_page_id` ,
                        `page_blocks_temp_id` ,
                        `page_blocks_block_id` ,
                        `page_blocks_tag_id`
                    )
                    SELECT
                            null ,  $pageId,  $tempId,  B1.block_id, B1.block_tag_id
                    FROM " . $this->tables["blocks"] . " AS `B1`
                    WHERE B1.block_temp_id = $tempId ";

        $isSuccess = $this->db->query($sql);

        return $isSuccess;
    }

    // return id if found, else return false
    public function get_page_id_from_slug($pageSlug) {
        $this->db->select('page_id');
        $this->db->where('page_slug', $pageSlug);
        $this->db->limit(1);
        $query = $this->db->get($this->tables["pages"]);
        return ( ( $query->num_rows() > 0 ) ? $query->row()->page_id : FALSE );
    }

    public function edit_page_by_id($data, $id) {
        $this->db->where('page_id', $id);
        return $this->db->update($this->tables["pages"], $data);
    }

    public function get_tag_id_for_include($includeId, $tagKeyword, $moduleName) {
        $this->db->select('tag_id');
        $this->db->where('tag_temp_id', $includeId);     // AND
        $this->db->where('tag_keyword', $tagKeyword);
        $this->db->where('tag_module_name', $moduleName);
        $this->db->limit(1);
        $query = $this->db->get($this->tables["tags"]);
        return ( ( $query->num_rows() > 0 ) ? $query->row()->tag_id : FALSE );
    }

    public function get_block_names($tempId) {

        $blockNames = null;

        $tempId = $this->addslashes($tempId);

        $this->db->select("block_name AS name");
        $this->db->from($this->tables['blocks']);
        $this->db->where('block_temp_id', $tempId);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $blocks = $query->result_array();

            // strip slashes
            foreach ($blocks as $block) {


                $blockNames[] = stripslashes($block['name']);
            }
        }

        return $blockNames;
    }

}

?>
