<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of module_import_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class module_import_model extends Site_Model {

        private $tables;

        public function  __construct() {
                parent::__construct();

                $this->config->load('database_tables');

                $this->tables['settings'] = $this->config->item('TBL_SETTINGS');
                $this->tables['modules'] = $this->config->item('TBL_MODULES');
                $this->tables['menus_admin'] = $this->config->item('TBL_MENUS_ADMIN');

        }

        public function run_sql( $sql ) {

                //explode it in an array
                $file_array = explode(';', $sql);

                $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

                //execute the exploded text content
                foreach($file_array as $query)
                        $this->db->query($query);

                $this->db->query("SET FOREIGN_KEY_CHECKS = 1"); 

        }

}


/* End of file module_import_model.php */
/* Location: ./application/.... module_import_model.php */
?>
