<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Pages';
//$config['menu_icon'] = '';       // relative to module root
$config['menu_uri'] = '#';
$config['menu_description'] = 'Page Operations';

$config['sub_menu'] = array(
    array('name' => 'add', 'uri' => site_url() . 'admin/page/add', 'description' => 'Create new page'),
    array('name' => 'manage', 'uri' => site_url() . "admin/page/manage", 'description' => 'Manage pages')

        //array( 'name' => 'edit', 'uri' => site_url() . 'admin/page/edit', 'description' => 'Edit pages'  )
);
?>
