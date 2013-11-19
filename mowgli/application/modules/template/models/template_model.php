<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of template_model
 *
 * @author Lloyd
 */
class template_model extends Site_Model {

        private $module = "template";
        protected $tables = array();

        public function __construct() {

                parent::__construct();

                $this->tables["templates"] = $this->config->item('TBL_TEMPLATES');
                $this->tables["pages"] = $this->config->item('TBL_PAGES');
                $this->tables["blocks"] = $this->config->item('TBL_BLOCKS');
        }

        /**
         * gets page details for given page Id
         *
         * @param string $pageUri uri of page
         * @return array|false returns result on success, false on no result
         */
        public function get_template_details($tempId, $allowHidden = false) {

                $tempDetails = null;

                $tempId = $this->addslashes($tempId);

                /*
                  SELECT
                  temp_id AS id,
                  temp_module_name AS module_name,
                  temp_type AS type,
                  temp_name AS name,
                  temp_head AS head,
                  temp_html AS html

                  FROM templates

                  WHERE temp_id = $tempId
                 */

                $select = "
                        temp_id AS id,
                        temp_module_name AS module_name,
                        temp_type AS type,
                        temp_name AS name,
                        temp_head AS head,
                        temp_html AS html,
                        temp_is_visible AS is_visible";

                $this->db->select($select);
                $this->db->from($this->tables['templates']);
                $this->db->where('temp_id', $tempId);
                if ($allowHidden)
                        $this->db->where('temp_is_visible', 1);

                $this->db->limit(1);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $tempDetails = $query->row_array();

                        // strip slashes
                        foreach ($tempDetails as $key => $value) {
                                $tempDetails[$key] = stripslashes($value);
                        }
                }

                return $tempDetails;
        }

        /**
         * Gets basic info about templates based on temp Type and module name
         * 
         * @param string $tempType type of tempate Eg. ( page, includes, module )
         * @param string $module module name
         * @param bool $allowHidden whether to also include templates where temp_is_visible = false 
         * 
         * @return array|null
         */
        public function get_templates_list_by_group($tempType, $module = null, $allowHidden = false) {

                $tempDetails = null;

                $tempType = $this->addslashes($tempType);
                $module = $this->addslashes($module);

                /*
                  SELECT
                  temp_id AS id,
                  temp_module_name AS module,
                  temp_type AS type,
                  temp_name AS name

                  FROM templates

                  WHERE
                  temp_type = '$tempType'
                  -- AND temp_module_name = '$module'
                  -- AND temp_is_visible = $allowHidden
                 */

                $select = "
                        temp_id AS id,
                        temp_module_name AS module,
                        temp_type AS type,
                        temp_name AS name";

                $this->db->select($select);
                $this->db->from($this->tables['templates']);
                $this->db->where('temp_type', $tempType);
                if (!is_null($module))
                        $this->db->where('temp_module_name', $module);
                if ($allowHidden)
                        $this->db->where('temp_is_visible', 1);

                $this->db->order_by( 'temp_name', 'ASC' );
                
                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $tempDetails = $query->result_array();

                        // strip slashes
                        foreach ($tempDetails as & $temp) {
                         
                                foreach ($temp as $key => $value) {
                                        $temp[$key] = stripslashes($value);
                                }
                        }
                }

                return $tempDetails;
        }

        /**
         * gets template details for given module, tempName, tempType
         *
         * @param string $module module name
         * @param string $tempName template name
         * @param string $tempType type ( page, includes OR module )
         * @param bool $allowHidden
         * 
         * @return array|null returns result on success, null on no result
         */
        
        /**
         * adds template to database
         * @param array data array
         * @return int|bool returns (int)number of rows affected on success, false on failure
         */
        public function create_template( $dataArray ) {

                $dataArray = $this->addslashes( $dataArray );

                $this->db->insert( $this->tables["templates"], $dataArray );

                return $this->db->insert_id();
        }
        
        public function get_template_by_values($module, $tempName, $tempType, $allowHidden = false) {

                $tempDetails = null;

                $module = $this->addslashes($module);
                $tempName = $this->addslashes($tempName);
                $tempType = $this->addslashes($tempType);

                /*
                  SELECT
                  temp_id AS id,
                  temp_module_name AS module_name,
                  temp_type AS type,
                  temp_name AS name,
                  temp_head AS head,
                  temp_html AS html

                  FROM templates

                  WHERE temp_id = $tempId
                 */

                $select = "
                        temp_id AS id,
                        temp_module_name AS module_name,
                        temp_type AS type,
                        temp_name AS name,
                        temp_head AS head,
                        temp_html AS html,
                        temp_is_visible AS is_visible";

                $this->db->select($select);
                $this->db->from($this->tables['templates']);
                $this->db->where('temp_module_name', $module);
                $this->db->where('temp_name', $tempName);
                $this->db->where('temp_type', $tempType);
                if ($allowHidden)
                        $this->db->where('temp_is_visible', 1);

                $this->db->limit(1);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $tempDetails = $query->row_array();

                        // strip slashes
                        foreach ($tempDetails as $key => $value) {
                                $tempDetails[$key] = stripslashes($value);
                        }
                }

                return $tempDetails;
        }

        public function get_pages_list() {

                $pageListDetails = null;

                /*
                  SELECT
                  temp_id AS id,
                  temp_name AS name

                  FROM pages

                 */

                $select = "
            temp_id AS id,
            temp_name AS name";

                $this->db->select($select);
                $this->db->from($this->tables['pages']);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $pageListDetails = $query->row_array();

                        // strip slashes
                        foreach ($pageListDetails as & $page) {
                                foreach ($page as $key => $value) {

                                        $page[$key] = stripslashes($value);
                                }
                        }
                }

                return $pageListDetails;
        }

        public function check_valid_template_name($tempType, $tempName, $moduleName = null) {

                $success = false;

                $tempType = $this->addslashes($tempType);
                $tempName = $this->addslashes($tempName);
                $moduleName = $this->addslashes($moduleName);

                $this->db->select('temp_name');
                $this->db->from($this->tables["templates"]);
                $this->db->where('temp_type', $tempType);
                $this->db->where('temp_name', $tempName);
                if (!is_null($moduleName) AND $moduleName != '')
                        $this->db->where('temp_module_name', $moduleName);

                $this->db->limit(1);

                $query = $this->db->get();

                // check if name exists
                if ($query->num_rows() == 0) {

                        // template with same name DOES NOT exist, hence validation PASS
                        $success = true;
                }

                return $success;
        }

}

?>
