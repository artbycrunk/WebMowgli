<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of content_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

//define('TBL_CONTENTS', 'contents');

class Content_Model extends Site_Model {

    public function  __construct() {
        parent::__construct();

        //$this->config->load('database_tables');
        define('TBL_CONTENTS', 'contents');
    }

    public function create_content( $dbArray ){

            /* Add slashes to database entires */
//        foreach ($dbArray as $col => $value) {
//            $dbArray[$col] = addslashes( $value );
//        }
        $dbArray = $this->addslashes( $dbArray );

        $this->db->insert( TBL_CONTENTS, $dbArray );
        return $this->db->insert_id();
    }

    public function create_tag( $tagDb ){

            $this->load->model('tags_model');
            $this->tags_model->set_db( $this->db );
            return $this->tags_model->create_tag( $tagDb );
    }
    // returns [ id, type, data ] 
    public function get_contents( $contentIds ){

        $contents = false;

        $select = "
            content_id AS id,
            content_type AS type,
            content_data AS data";
        
        $this->db->select( $select );
        $this->db->from( TBL_CONTENTS );
        $this->db->where_in( 'content_id', $contentIds, false );

        $query = $this->db->get();

        if( $query->num_rows() > 0 ){

            $contents = $query->result_array();
            
            foreach ( $contents as & $row ) {
                foreach ( $row as $key => $value) {
                    $row[ $key ] = stripslashes( $value );
                }
            }
        }
        
        return $contents;
    }

    // updates content. returns true on success, false on failure
    public function update_content( $id, $content ){
        
        $id = addslashes( $id );
        $content = addslashes( $content );

        $sql = "UPDATE " . TBL_CONTENTS . " SET content_data = '$content' WHERE content_id = $id";
        
        return $this->db->query( $sql );
    }
}
?>
