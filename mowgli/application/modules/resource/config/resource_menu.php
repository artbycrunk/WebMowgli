<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$config['menu_visible'] = true;
$config['menu_name'] = 'Resources';
//$config['menu_icon'] = 'images/icon.png';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Manage Resources for site';

$config['sub_menu'] =  array(
            array( 'name' => 'Manage', 'uri' => "admin/resource/manage", 'description' => 'Manage Resources'  ),
            array( 'name' => 'Edit', 'uri' => "admin/resource/edit", 'description' => 'Edit Resources'  )
        );

?>
