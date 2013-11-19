<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of Resource_Manage_Model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */



class Resource_Manage_Model extends Site_Model {

        public function  __construct() {

                parent::__construct();

                define( 'TBL_RESOURCES', $this->config->item('TBL_RESOURCES') );

        }

        /**
         * @param array $resourceTypes list of resources
         * @param bool $includeThese if true searches for given types, if false excludes given types
         *
         * @return array|null
         */
        public function get_resources( $resourceTypes, $includeThese = true, $limit = null, $offset = null ) {

                /**
                 SELECT
                 SQL_CALC_FOUND_ROWS
                 resource_id         AS id,
                 resource_name       AS name,
                 resource_filetype   AS filetype,
                 resource_uri        AS uri,
                 resource_full_path  AS path,
                 resource_modified   AS modified

                 FROM resources

                 WHERE resource_type IN ( '$resourceType1', '$resourceType1' )
                 -- OR WHERE resource_type NOT IN ( '$resourceType1', '$resourceType1' )

                 ORDER BY resource_filetype ASC,
                 ORDER BY resource_name ASC,

                 */

                $resourceTypes = $this->addslashes( $resourceTypes );
                $resources = null;

                $select = "
                    SQL_CALC_FOUND_ROWS
                    resource_id         AS id,
                    resource_name       AS name,
                    resource_filetype   AS filetype,
                    resource_uri        AS uri,
                    resource_relative_path  AS relative_path,
                    resource_full_path  AS path,
                    resource_modified   AS modified";

                $this->db->select( $select, false );
                $this->db->from( TBL_RESOURCES );

                if( $includeThese ) {

                        $this->db->where_in( 'resource_filetype', $resourceTypes );
                }
                else {

                        $this->db->where_not_in( 'resource_filetype', $resourceTypes );

                }

                $this->db->order_by('resource_filetype', 'asc');
                $this->db->order_by('resource_name', 'asc');

                $this->db->limit( $limit, $offset );
                $query = $this->db->get();

                if( $query->num_rows() > 0 ) {

                        $resources = $query->result_array();

                        foreach( $resources as & $row ) {

                                // strip slashes
                                foreach ( $row as $key => $value ) {
                                        $row[$key] = stripslashes( $value );
                                }

                        }
                }

                return $resources;

        }

        public function delete_resources( $resourceIds ) {

                $resourceIds = $this->addslashes( $resourceIds );

                $this->db->where_in('resource_id', $resourceIds );
                $this->db->delete( TBL_RESOURCES );
                return $this->db->affected_rows();

        }


}
?>
