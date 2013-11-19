<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of discography_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Discography_Model extends MY_Model {

        private $module = "discography";
        private $config_database = "discography_config_database";
//        private $config_settings = "discography_config_settings";

        protected $tables = array();

        public function  __construct() {

                parent::__construct();

                $this->config->load( $this->config_database, true, true, $this->module );
//                $this->config->load( $this->config_settings, true, true, $this->module );

                $this->tables["categs"] = $this->config->item( "tbl_categs", $this->config_database );
                $this->tables["items"] = $this->config->item( "tbl_items", $this->config_database );


        }

        public function get_categ_max_order() {

                $maxOrder = null;

                $select = "MAX( discography_categ_order ) AS `max`";
                $this->db->select( $select );
                $this->db->from( $this->tables['categs'] );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if ( $query->num_rows() > 0 ) {

                        $row = $query->row_array();

                        $maxOrder = $row['max'];
                }

                return $maxOrder;
        }

        public function get_item_max_order( $categId ) {

                $maxOrder = null;

                $categId = $this->addslashes( $categId );

                $select = "MAX( discography_item_order ) AS `max`";
                $this->db->select( $select );
                $this->db->from( $this->tables["items"] );
                $this->db->where( 'discography_item_parent_id', $categId );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if ( $query->num_rows() > 0 ) {

                        $row = $query->row_array();

                        $maxOrder = $row['max'];
                }

                return $maxOrder;
        }

        public function get_first_categ_id() {

                $id = null;

                $select = "MIN( discography_categ_id ) AS `min`";
                $this->db->select( $select );
                $this->db->from( $this->tables['categs'] );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if ( $query->num_rows() > 0 ) {

                        $row = $query->row_array();

                        $id = $row['min'];
                }

                return $id;
        }

        /*
         * Inserts Album
         * @return id
        */
        public function create_categ( $dataDB ) {

                $dataDB = $this->addslashes( $dataDB );
                $this->db->insert( $this->tables["categs"], $dataDB );
                return $this->db->insert_id();
        }

        /*
         * Inserts Song
         * @return id
        */
        public function create_item( $dataDB ) {

                $dataDB = $this->addslashes( $dataDB );
                $this->db->insert( $this->tables["items"], $dataDB );
                return $this->db->insert_id();
        }


        /* @return bool */
        public function delete_categs( $ids ) {

                $ids = $this->addslashes( $ids );

                $this->db->where_in( 'discography_categ_id', $ids );
                return $this->db->delete( $this->tables["categs"] );
        }

        /* @return bool */
        public function delete_items( $ids ) {

                $ids = $this->addslashes( $ids );

                $this->db->where_in( 'discography_item_id', $ids );
                return $this->db->delete( $this->tables["items"] );
        }

        /* @return bool */
        public function edit_categ( $id, $data ) {

                $id = $this->addslashes( $id );
                $data = $this->addslashes( $data );

                $this->db->where( 'discography_categ_id', $id );
                $return = $this->db->update( $this->tables["categs"], $data);
//                return $return == false ? false : true;
                return $this->db->affected_rows() != -1 ? true : false;
        }

        /* @return bool */
        public function edit_item( $id, $data ) {

                $id = $this->addslashes( $id );
                $data = $this->addslashes( $data );

                $this->db->where( 'discography_item_id', $id );
                $return = $this->db->update( $this->tables["items"], $data);
//                return $return == false ? false : true;
                return $this->db->affected_rows() != -1 ? true : false;
        }

        /**
         * returns categs data
         * get specific categs OR all categs
         * limit can be set to specified number, else returns all
         *
         * @param array $ids = list of ids to retreive. ( if not specified or null --> return all )
         * @param bool $onlyVisible display categs that are marked as visible OR display all categs
         * @param int $limit
         *
         * @return array|null returns null if error
         */
        public function get_categs( $ids = null, $onlyVisible = false, $limit = null ) {

                $ids = $this->addslashes( $ids );
                $limit = $this->addslashes( $limit );

                $categs = null;

                $select = "
                        discography_categ_id                AS id,
                        discography_categ_name              AS name,
                        discography_categ_slug              AS slug,
                        discography_categ_buy_url           AS buy_url,
                        discography_categ_download_url      AS download_url,
                        discography_categ_image_url         AS image_url,
                        discography_categ_description       AS description,
                        discography_categ_is_visible        AS is_visible,
                        discography_categ_created           AS created,
                        discography_categ_order             AS `order`,

                        ( SELECT COUNT( * )
                                FROM " . $this->tables["items"] . " AS t3
                                WHERE t3.discography_item_parent_id = discography_categ_id ) AS count
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["categs"] );
//                $this->db->join( $this->tables["items"] . " AS items", 'discography_categ_id = discography_item_parent_id', 'left');

                // apply necessary where conditions
                if( ! is_null( $ids ) ) $this->db->where_in( 'discography_categ_id', $ids );
                if( $onlyVisible ) $this->db->where( 'discography_categ_is_visible', true );
                if( ! is_null( $limit ) ) $this->db->limit( $limit );

                $this->db->order_by( 'discography_categ_order', 'ASC' ); // order categs in given order

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $categs = $query->result_array();

                        foreach ( $categs as & $categ ) {

                                foreach ( $categ as $key => $value) {

                                        $categ[ $key ] = stripslashes( $value );
                                }

                        }

                }

                return $categs;

        }

        /**
         * Same as get_categs
         * only for single category instead of several
         */
        public function get_categ( $id, $onlyVisible = false, $limit = null ) {

                $return = null;

                if( ! is_null( $id ) ) {

                        $categs = $this->get_categs( $id, $onlyVisible, $limit );

                        // get first element of category list
                        if( ! is_null( $categs ) AND isset ( $categs[0] ) ) {
                                $return = $categs[0];
                        }
                }


                return $return;

        }

        /**
         * returns list of categ ids and correcponding categ names, in key value format
         *
         * @return array|null
         */
        public function get_categ_list() {

                // will contain key, value ( i.e. categ_id ==> categ name )
                $list = null;

                $select = "
                        discography_categ_id                AS id,
                        discography_categ_name              AS name
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["categs"] );

                $this->db->order_by( 'discography_categ_id', 'ASC' ); // order categs in given order

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $categs = $query->result_array();

                        foreach ( $categs as & $categ ) {

                                $categ['id'] = stripslashes( $categ['id'] );
                                $categ['name'] = stripslashes( $categ['name'] );

                                $list[ $categ['id'] ] = $categ['name'];

                        }

                }

                return $list;

        }

        /**
         * returns items data
         * get specific items OR all items
         * limit can be set to specified number, else returns all
         *
         * @param array $ids = list of ids to retreive. ( if not specified or null --> return all )
         * @param bool $onlyVisible display items that are marked as visible OR display all items
         * @param int $limit
         *
         * @return array|null returns null if error
         */
        public function get_items( $categIds = null, $ids = null, $onlyVisible = false, $limit = null ) {

                $categIds = $this->addslashes( $categIds );
                $ids = $this->addslashes( $ids );
                $limit = $this->addslashes( $limit );

                $items = null;

                $select = "
                        discography_item_id                AS id,
                        discography_item_parent_id         AS parent_id,
                        categs.discography_categ_name             AS categ_name,
                        categs.discography_categ_slug             AS categ_slug,
                        discography_item_name              AS name,
                        discography_item_slug              AS slug,
                        discography_item_description       AS description,
                        discography_item_order             AS `order`,
                        discography_item_is_visible        AS is_visible
                        ";

                $this->db->select( $select );
//                $this->db->from( $this->tables["categs"] . " AS categs" );
//                $this->db->join( $this->tables["items"] . " AS items", 'discography_categ_id = discography_item_parent_id', 'left');
                $this->db->from( $this->tables["items"] . " AS items" );
                $this->db->join( $this->tables["categs"] . " AS categs", 'discography_categ_id = discography_item_parent_id', 'left');

                // apply necessary where conditions
                if( ! is_null( $categIds ) ) $this->db->where_in( 'categs.discography_categ_id', $categIds );
                if( ! is_null( $ids ) ) $this->db->where_in( 'items.discography_item_id', $ids );
                if( $onlyVisible ) $this->db->where( 'items.discography_item_is_visible', true );
                if( ! is_null( $limit ) ) $this->db->limit( $limit );

                $this->db->order_by( 'categs.discography_categ_order', 'ASC' ); // order items in given order
                $this->db->order_by( 'items.discography_item_order', 'ASC' ); // order items in given order

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $items = $query->result_array();

                        foreach ( $items as & $item ) {

                                foreach ( $item as $key => $value) {

                                        $item[ $key ] = stripslashes( $value );
                                }

                        }

                }

                return $items;

        }

	/**
         * Same as get_items
         * only for single Item instead of several
         */
        public function get_item( $id, $onlyVisible = false, $limit = 1 ) {

                $return = null;

                if( ! is_null( $id ) ) {

                        $items = $this->get_items( null, $id, $onlyVisible, $limit );

                        // get first element of item list
                        if( ! is_null( $items ) AND isset ( $items[0] ) ) {
                                $return = $items[0];
                        }
                }


                return $return;

        }

        /**
         * This is a specific model for front end display on the music page
         * returns only specific items data ( i.e. columns )
         * limit can be set to specified number, else returns all
         *
         * @param array $categIds list of categories to display, if not mentioned displays all
         * @param array $ids = list of ids to retreive. ( if not specified or null --> return all )
         * @param bool $onlyVisible display items that are marked as visible OR display all items
         * @param int $limit
         *
         * @return array|null returns null if error
         */
        public function get_items_front_display( $categIds = null, $ids = null, $onlyVisible = false, $limit = null ) {

                $categIds = $this->addslashes( $categIds );
                $ids = $this->addslashes( $ids );
                $limit = $this->addslashes( $limit );

                $items = null;

                $select = "
                        discography_item_id                AS id,
                        discography_item_parent_id         AS parent_id,
                        categs.discography_categ_name             AS categ_name,
                        categs.discography_categ_slug             AS categ_slug,
                        discography_item_name              AS name,
                        discography_item_slug              AS slug
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["categs"] . " AS categs" );
                $this->db->join( $this->tables["items"] . " AS items", 'discography_categ_id = discography_item_parent_id', 'left');
//                $this->db->from( $this->tables["items"] . " AS items" );
//                $this->db->join( $this->tables["categs"] . " AS categs", 'discography_categ_id = discography_item_parent_id', 'left');

                // apply necessary where conditions
                if( ! is_null( $categIds ) ) $this->db->where_in( 'categs.discography_categ_id', $categIds );
                if( ! is_null( $ids ) ) $this->db->where_in( 'items.discography_item_id', $ids );
                if( $onlyVisible )
                {
                        $this->db->where( 'categs.discography_categ_is_visible', true );
                        $this->db->where( 'items.discography_item_is_visible', true );
                }
                if( ! is_null( $limit ) ) $this->db->limit( $limit );

                $this->db->order_by( 'categs.discography_categ_order', 'ASC' ); // order categs in given order
                $this->db->order_by( 'items.discography_item_order', 'ASC' ); // order items in given order

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $items = $query->result_array();

                        foreach ( $items as & $item ) {

                                foreach ( $item as $key => $value) {

                                        $item[ $key ] = stripslashes( $value );
                                }

                        }

                }

                return $items;

        }

        public function reorder_categs( $oldId, $newId ){

                $tableName = $this->tables["categs"];
                $orderColumnName = "discography_categ_order";
                $oldOrder = $this->get_categ_order_by_id( $oldId );
                $newOrder = $this->get_categ_order_by_id( $newId );
                $whereConditions = null;

                $success = parent::reorder($tableName, $orderColumnName, $oldOrder, $newOrder, $whereConditions);
                return $success;
        }

        public function reorder_items( $oldId, $newId ){

                $tableName = $this->tables["items"];
                $orderColumnName = "discography_item_order";
                $oldOrder = $this->get_item_order_by_id( $oldId );
                $newOrder = $this->get_item_order_by_id( $newId );

                // get category id ( parent id )
                $categId = $this->get_item_parent_by_id( $oldId );

                $whereConditions = "discography_item_parent_id = $categId";

                $success = parent::reorder( $tableName, $orderColumnName, $oldOrder, $newOrder, $whereConditions );
                return $success;
        }

        // return array|null
        public function get_item_id_by_slug( $slug ){

                $slug = $this->addslashes($slug);

                $this->db->select( 'discography_item_id AS `id`' );
                $this->db->from( $this->tables["items"] );
                $this->db->where( 'discography_item_slug', $slug );
                $this->db->limit(1);

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                return ( ( $query->num_rows() > 0 ) ? $query->row()->id : null );

        }

        private function get_categ_order_by_id( $id ){

                $id = $this->addslashes($id);

                $this->db->select( 'discography_categ_order AS `order`' );
                $this->db->from( $this->tables["categs"] );
                $this->db->where( 'discography_categ_id', $id );
                $this->db->limit(1);

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                return ( ( $query->num_rows() > 0 ) ? $query->row()->order : FALSE );

        }

        private function get_item_order_by_id( $id ){

                $id = $this->addslashes($id);

                $this->db->select( 'discography_item_order AS `order`' );
                $this->db->from( $this->tables["items"] );
                $this->db->where( 'discography_item_id', $id );
                $this->db->limit(1);

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                return ( ( $query->num_rows() > 0 ) ? $query->row()->order : FALSE );

        }



        private function get_item_parent_by_id( $id ){

                $id = $this->addslashes($id);

                $this->db->select( 'discography_item_parent_id AS `parent_id`' );
                $this->db->from( $this->tables["items"] );
                $this->db->where( 'discography_item_id', $id );
                $this->db->limit(1);

//                $sql = $this->db->_compile_select();

                $query = $this->db->get();

                return ( ( $query->num_rows() > 0 ) ? $query->row()->parent_id : FALSE );

        }


}
?>
