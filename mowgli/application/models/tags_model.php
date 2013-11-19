<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of tags_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Tags_model extends Site_Model {

        private $tables;
        
        

        public function  __construct() {

                parent::__construct();

                $this->config->load('database_tables');
                $this->tables["tags"] = $this->config->item( "TBL_TAGS" );
        }

        /**
         * inserts tags in database, returns tag id
         */
        public function create_tag( $tagDb ) {

                /* Add slashes to database entires */

                $tagDb = $this->addslashes( $tagDb );
                $this->db->insert( $this->tables["tags"], $tagDb );
                return $this->db->insert_id();
        }
}
?>
