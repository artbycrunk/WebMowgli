<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of template_manage_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

class Template_Manage_Model extends Site_Model {

        public function  __construct() {
                parent::__construct();

                define( 'TBL_PAGES', $this->config->item('TBL_PAGES') );
                define( 'TBL_TEMPLATES', $this->config->item('TBL_TEMPLATES') );

        }

        public function get_templates( $tempType = 'all', $limit = null, $offset = 0  ) {

                /**
                 SELECT
                 SQL_CALC_FOUND_ROWS
                 temp_id AS id,
                 temp_type AS type,
                 temp_name AS name,
                 ( SELECT count( * ) FROM pages WHERE page_temp_id = temp_id ) AS usage,
                 temp_modified AS modified

                 FROM templates

                 WHERE temp_type = '$tempType'
                 -- OR no where clause if $tempType = 'all'

                 */

                $tempType = addslashes( $tempType );
                $templates = null;

                $select = "
                        SQL_CALC_FOUND_ROWS
                    temp_id         AS id,
                    temp_type       AS type,
                    temp_name       AS name,
		    temp_module_name      AS module,
                    ( SELECT count( * ) FROM " . TBL_PAGES . " WHERE page_temp_id = temp_id ) AS count,
                    temp_modified   AS modified";

                $this->db->select( $select, false );
                $this->db->from( TBL_TEMPLATES );

                if( $tempType != 'all' ) {

                        $this->db->where( 'temp_type', $tempType );

                }

                $this->db->order_by('temp_name', 'asc');
                if( ! is_null( $limit ) )
                        $this->db->limit( $limit, $offset );
//echo $this->db->_compile_select();
                $query = $this->db->get();

                if( $query->num_rows() > 0 ) {

                        $templates = $query->result_array();

                        foreach( $templates as & $row ) {
                                // strip slashes
                                foreach ( $row as $key => $value ) {
                                        $row[$key] = stripslashes( $value );
                                }
                        }
                }

                return $templates;

        }

        public function get_templates_count( $tempType = 'all' ){

                $this->db->select( "COUNT( * ) AS count", false );
                $this->db->from( TBL_TEMPLATES );
                if( $tempType != 'all' ) {

                        $this->db->where( 'temp_type', $tempType );

                }
                $query = $this->db->get();
                return $query->row()->count;
        }

        public function delete_templates( $tempIds ) {

                $tempIds = $this->addslashes( $tempIds );

                $this->db->where_in('temp_id', $tempIds );
                $this->db->delete( TBL_TEMPLATES );
                return $this->db->affected_rows();

        }


}
?>
