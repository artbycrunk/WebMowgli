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

class Page_Edit_Model extends Site_Model {

        private $tables;

    public function  __construct() {

        parent::__construct();

//        define( 'TBL_PAGES', $this->config->item('TBL_PAGES') );
//        define( 'TBL_TEMPLATES', $this->config->item('TBL_TEMPLATES') );

        $this->tables['pages'] = $this->config->item('TBL_PAGES');
        $this->tables['templates'] = $this->config->item('TBL_TEMPLATES');
    }



    public function get_pages_list(){

        $pageListDetails = null;

        /*
        SELECT
        page_id AS id,
        page_name AS name

        FROM pages
         
         */

        $select = "
            page_id AS id,
            page_name AS name";

        $this->db->select( $select );
        $this->db->from( $this->tables['pages'] );

        $query = $this->db->get();

        if( $query->num_rows() > 0 ){

            $pageListDetails = $query->row_array();

            // strip slashes
            foreach ( $pageListDetails as & $page )
            {
                foreach ( $page as $key => $value) {

                    $page[ $key ] = stripslashes( $value );
                    
                }

            }
        }

        return $pageListDetails;
    }
}
?>
