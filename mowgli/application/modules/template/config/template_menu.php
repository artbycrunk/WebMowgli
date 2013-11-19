<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$config['menu_visible'] = true;
$config['menu_name'] = 'Templates';
//$config['menu_icon'] = '';
$config['menu_uri'] = '#';
$config['menu_description'] = 'no description';

$config['sub_menu'] = array(
    array('name' => 'Add', 'uri' => site_url() . "admin/template/add", 'description' => 'Add Templates'),
    array('name' => 'Manage', 'uri' => site_url() . "admin/template/manage", 'description' => 'Manage Templates')
        // array( 'name' => 'Edit', 'uri' => site_url() . "admin/template/edit", 'description' => 'Edit Templates'  )
);
?>
