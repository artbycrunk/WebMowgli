<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of events_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Events_model extends MY_Model {

        private $module = "events";
        private $config_database = "events_config_database";

        protected $tables = array();

        private $currentTime = null;

        public function  __construct() {

                parent::__construct();

                $this->config->load( $this->config_database, true, true, $this->module );

                $this->tables["events"] = $this->config->item( "tbl_events", $this->config_database );

                $this->currentTime = $this->date_time->now();
        }



        /*
         * Inserts events
         * @return id
        */
        public function create_event( $eventDB ) {

                $eventDB = $this->addslashes( $eventDB );
                $this->db->insert( $this->tables["events"], $eventDB );
                return $this->db->insert_id();
        }

        /* @return bool */
        public function edit_event( $id, $data ) {

                $id = $this->addslashes( $id );
                $data = $this->addslashes( $data );

                $this->db->where( 'event_id', $id );
                $return = $this->db->update( $this->tables["events"], $data );
//                return $return == false ? false : true;
                return $this->db->affected_rows() != -1 ? true : false;
        }

        /* @return bool */
        public function delete_events( $ids ) {

                $ids = $this->addslashes( $ids );

                $this->db->where_in( 'event_id', $ids );
                return $this->db->delete( $this->tables["events"] );
        }

        /**
         * returns single event data
         *
         * @param int $id
         *
         * @return array|null returns null if error
         */
        public function get_event( $id ) {

                $id = $this->addslashes( $id );

                $event = null;

                $select = "
                        event_id                AS id,
                        event_name              AS name,
                        event_slug              AS slug,
                        event_description       AS description,
                        event_venue             AS venue,
                        event_start             AS start,
                        event_end               AS end
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["events"] );
                $this->db->where( 'event_id', $id );
                $this->db->limit( 1 );

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $event = $query->row_array();

                        foreach ( $event as $key => $value) {

                                $event[ $key ] = stripslashes( $value );
                        }
                        

                }

                return $event;

        }


        /**
         * returns events data
         * returns all data OR after start date
         * limit can be set to specified number, else returns all
         *
         * @param string(datetime) $start
         * @param int $limit
         *
         * @return array|null returns null if error
         */
        public function get_events( $start = null, $limit = null ) {

                // if start date not mentioned, default it to current date time string
                $start = is_null( $start ) ? $this->currentTime : $start;

                $start = $this->addslashes( $start );
                $limit = $this->addslashes( $limit );

                $events = null;

                $select = "
                        event_id                AS id,
                        event_name              AS name,
                        event_slug              AS slug,
                        event_description       AS description,
                        event_venue             AS venue,
                        event_start             AS start,
                        event_end               AS end
                        ";

                $this->db->select( $select );
                $this->db->from( $this->tables["events"] );

                if( ! is_null( $start ) ) $this->db->where( 'event_start >= ', $start );
                if( ! is_null( $limit ) ) $this->db->limit( $limit );

                $this->db->order_by( 'event_start', 'ASC' ); // order events in chronological order

                $query = $this->db->get();

                if( $query->num_rows() > 0  ) {

                        $events = $query->result_array();

                        foreach ( $events as & $event ) {

                                foreach ( $event as $key => $value) {

                                        $event[ $key ] = stripslashes( $value );
                                }

                        }

                }

                return $events;

        }

}
?>
