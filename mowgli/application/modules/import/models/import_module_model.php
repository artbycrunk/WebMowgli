<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of Import_Module_Model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

class Import_Module_Model extends Site_Model {

        protected $tables = array();

        public function  __construct() {
                parent::__construct();

                $this->config->load('database_tables');

//                $this->tables["page_blocks"]    = $this->config->item('TBL_PAGE_BLOCKS');
//                $this->tables["pages"]          = $this->config->item('TBL_PAGES');
//                $this->tables["templates"]      = $this->config->item('TBL_TEMPLATES');
//                $this->tables["blocks"]         = $this->config->item('TBL_BLOCKS');
//                $this->tables["resources"]      = $this->config->item('TBL_RESOURCES');
//                $this->tables["tags"]           = $this->config->item('TBL_TAGS');
                
        }



}
?>
