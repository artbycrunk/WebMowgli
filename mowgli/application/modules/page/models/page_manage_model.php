<?php

/**
 * Description of page_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

//define( 'TBL_PAGE_BLOCKS', 'page_blocks');

class Page_Manage_Model extends Site_Model {

    public function  __construct() {
        parent::__construct();
//
//        $this->config->load('database_tables');
//        define( 'TBL_PAGE_BLOCKS', $this->config->item('TBL_PAGE_BLOCKS') );
        define( 'TBL_PAGES', $this->config->item('TBL_PAGES') );
        define( 'TBL_TEMPLATES', $this->config->item('TBL_TEMPLATES') );
//        define( 'TBL_TAGS', $this->config->item('TBL_TAGS') );
//        define( 'TBL_BLOCKS', $this->config->item('TBL_BLOCKS') );
    }

//    public function get_pages( $pageId = null ){
//     
//        /**
//            SELECT
//            page_id AS id,
//            page_name AS name,
//            temp_name AS template,
//            temp_id AS temp_id,
//            page_modified AS modified,
//            page_uri as uri,
//            page_is_visible AS published
//
//            FROM
//
//            pages
//            LEFT JOIN templates ON page_temp_id = temp_id
//
//            -- WHERE page_id = $pageId
//
//         */
//
//        $pages = null;
//
//        $select = "
//            page_id         AS id,
//            temp_id         AS temp_id,
//            page_name       AS name,
//            temp_name       AS temp_name,
//            page_modified   AS modified,
//            page_slug        AS slug,
//            page_is_visible AS published";
//
//        $this->db->select( $select );
//        $this->db->from( TBL_PAGES );
//        if( !is_null( $pageId ) ) $this->db->where( 'page_id', $pageId );
//        $this->db->join( TBL_TEMPLATES, 'page_temp_id = temp_id', 'left');
//        $this->db->order_by('page_name', 'asc');
//
//        $query = $this->db->get();
//
//        if( $query->num_rows() > 0 ){
//
//            $pages = $query->result_array();
//
//            foreach( $pages as & $row ){
//                // strip slashes
//                foreach ( $row as $key => $value )
//                {
//                   $row[$key] = stripslashes( $value );
//                }
//            }
//        }
//
//        return $pages;
//        
//    }

    public function publish_toggle( $pageIds ){

        /*
            UPDATE pages

            SET page_is_visible = ! page_is_visible

            WHERE page_id IN (
                1, 2
            );
         
         */

        $pageIds = $this->addslashes( $pageIds );

        $escape = false;
        $this->db->set( 'page_is_visible', ' ! page_is_visible ', $escape ); // false ==> do not escape values
        $this->db->where_in('page_id', $pageIds );
        $this->db->update( TBL_PAGES );
        
        return $this->db->affected_rows();
//        return $this->db->affected_rows() != -1 ? true : false;
        
    }

    public function delete_pages( $pageIds ){

        $this->db->where_in('page_id', $pageIds );
        $this->db->delete( TBL_PAGES );
        return $this->db->affected_rows();
        
    }

}
?>
