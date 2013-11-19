<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of admin_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Admin_Model extends MY_Model {

    public function  __construct() {
        parent::__construct();

        $this->config->load('database_tables');
        define( 'TBL_MODULES', $this->config->item('TBL_MODULES') );
    }

    public function get_modules_for_menu(){

            $modules = null;

            $this->db->select( "module_name AS module" );
            $this->db->from( TBL_MODULES );
            $this->db->where( "module_has_menu", 1, false );
            $this->db->order_by( "module_menu_order", 'asc' );

            $query = $this->db->get();

            if( $query->num_rows() > 0 ){

                $result = $query->result_array();

                foreach ( $result as & $row ) {
                    foreach ( $row as $module ) {
                        $modules[] = stripslashes( $module );
                    }
                }
            }

            return $modules;
    }

    /**
     * Deletes all entries in a given table
     * note: uses DELETE statement and NOT truncate statement ( since truncate does not work if fk exists in table )
     *
     * @param string $table table name to delete
     *
     * @return bool
     */
    public function truncate( $table ){

	    return $this->db->simple_query( "DELETE FROM $table WHERE 1" );

    }
}
?>
