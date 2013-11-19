<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Site_Model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

//define('TBL_TAGS', 'tags');

class Site_Model extends MY_Model {

    public function  __construct() {
        parent::__construct();

        $this->config->load('database_tables');
        
    }
    



}
?>