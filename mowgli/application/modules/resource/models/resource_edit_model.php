<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of resource_Edit_Model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

class Resource_Edit_Model extends Site_Model {

    public function  __construct() {

        parent::__construct();

        define( 'TBL_PAGES', $this->config->item('TBL_PAGES') );
        define( 'TBL_RESOURCES', $this->config->item('TBL_RESOURCES') );
    }

        /**
     * gets resource details for given page Id
     *
     * @param string $resourceId uri of page
     * @return array|false returns result on success, null on no result
     */
    public function get_resource_details( $resourceId ){

        /*
                SELECT
                resource_id AS id,
                resource_name AS name,
                resource_filetype AS filetype,
                resource_uri AS uri,
                resource_full_path AS path,
                resource_modified AS modified

                FROM resources

                WHERE resource_id = $resourceId
         */

        $resourceDetails = null;

        $resourceId = $this->addslashes( $resourceId );

        $select = "
                resource_id             AS id,
                resource_name           AS name,
                resource_filetype       AS filetype,
                resource_uri            AS uri,
                resource_relative_path  AS relative_path,
                resource_full_path      AS path,
                resource_modified       AS modified";

        $this->db->select( $select );
        $this->db->from( TBL_RESOURCES );
        $this->db->where( 'resource_id', $resourceId );
        $this->db->limit(1);

        $query = $this->db->get();

        if( $query->num_rows() > 0 ){

            $resourceDetails = $query->row_array();

            // strip slashes
            foreach ( $resourceDetails as $key => $value )
            {
               $resourceDetails[$key] = stripslashes( $value );
            }
        }

        return $resourceDetails;
    }

}
?>
