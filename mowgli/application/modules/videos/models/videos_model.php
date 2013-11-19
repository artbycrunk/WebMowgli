<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of videos_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Videos_model extends MY_Model {

        private $module = "videos";
        private $config_database = "videos_config_database";

        protected $tables = array();

        public function  __construct() {

                parent::__construct();

                $this->config->load( $this->config_database, true, true, $this->module );

                $this->tables["videos"] = $this->config->item( "tbl_videos", $this->config_database );
        }



        /*
         * Inserts videos
         * @return id
        */
        public function create_video( $videoDB ) {

                $videoDB = $this->addslashes( $videoDB );
                $this->db->insert( $this->tables["videos"], $videoDB );
                return $this->db->insert_id();
        }

        /* @return bool */
        public function edit_video( $id, $data ) {

                $id = $this->addslashes( $id );
                $data = $this->addslashes( $data );

                $this->db->where( 'video_id', $id );
                $return = $this->db->update( $this->tables["videos"], $data );
//                return $return == false ? false : true;
                return $this->db->affected_rows() != -1 ? true : false;
        }

        /* @return bool */
        public function delete_videos( $ids ) {

                $ids = $this->addslashes( $ids );

                $this->db->where_in( 'video_id', $ids );
                return $this->db->delete( $this->tables["videos"] );
        }

        /**
         * returns single video data
         *
         * @param int $id
         *
         * @return array|null returns null if error
         */
        public function get_video( $id ) {

                $id = $this->addslashes( $id );

                $video = null;

                $select = "
                        video_id               AS id,
                        video_ref_id           AS ref_id,
                        video_title            AS title,
                        video_description      AS description,
                        video_image_url        AS image_url,
                        video_script             AS script,
                        video_order            AS `order`,
                        video_is_visible       AS is_visible
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["videos"] );
                $this->db->where( 'video_id', $id );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $video = $query->row_array();

                        foreach ( $video as $key => $value) {

                                $video[ $key ] = stripslashes( $value );
                        }

                }

                return $video;

        }


        /**
         * returns videos data
         * limit can be set to specified number, else returns all
         *
         * @param bool $onlyVisible display video that are marked as visible OR display all videos
         * @param int $limit
         *
         * @return array|null returns null if error
         */
        public function get_videos( $onlyVisible = false, $limit = null ) {

                $limit = $this->addslashes( $limit );

                $videos = null;

                $select = "
                        video_id               AS id,
                        video_ref_id           AS ref_id,
                        video_title            AS title,
                        video_description      AS description,
                        video_image_url        AS image_url,
                        video_script             AS script,
                        video_order            AS `order`,
                        video_is_visible       AS is_visible
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["videos"] );
                
                if( $onlyVisible ) $this->db->where( 'video_is_visible', true );
                if( ! is_null( $limit ) ) $this->db->limit( $limit );

                $this->db->order_by( 'video_order', 'ASC' ); // order videos in given order

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $videos = $query->result_array();

                        foreach ( $videos as & $video ) {

                                foreach ( $video as $key => $value) {

                                        $video[ $key ] = stripslashes( $value );
                                }

                        }

                }

                return $videos;

        }

        public function reorder_categs( $oldId, $newId ){

                $tableName = $this->tables["videos"];
                $orderColumnName = "video_order";
                $oldOrder = $this->get_order_by_id( $oldId );
                $newOrder = $this->get_order_by_id( $newId );
                $whereConditions = null;

                $success = parent::reorder($tableName, $orderColumnName, $oldOrder, $newOrder, $whereConditions);
                return $success;
        }

        public function get_order_by_id( $id ){

                $id = $this->addslashes($id);

                $this->db->select( 'video_order AS `order`' );
                $this->db->from( $this->tables["videos"] );
                $this->db->where( 'video_id', $id );
                $this->db->limit(1);

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                return ( ( $query->num_rows() > 0 ) ? $query->row()->order : FALSE );

        }

        public function get_max_order() {

                $maxOrder = null;

                $select = "MAX( video_order ) AS `max`";
                $this->db->select( $select );
                $this->db->from( $this->tables['videos'] );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if ( $query->num_rows() > 0 ) {

                        $row = $query->row_array();

                        $maxOrder = $row['max'];
                }

                return $maxOrder;
        }
}
?>
