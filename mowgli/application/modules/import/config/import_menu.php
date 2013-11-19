<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$config['menu_visible'] = true;
$config['menu_name'] = 'Import';
//$config['menu_icon'] = 'images/icon.png';
$config['menu_uri'] = 'admin/import';
$config['menu_description'] = 'no description';

$config['sub_menu'] =  array(
    
            array( 'name' => 'import site', 'uri' => 'admin/import/import_site', 'description' => ''  ),
            array( 'name' => 'add template', 'uri' => 'admin/import/add_template', 'description' => ''  )
        );

?>
