<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Settings';
//$config['menu_icon'] = 'images/icon.png';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Edit site settings';

$config['sub_menu'] = array(
    array('name' => 'General', 'uri' => 'admin/settings/general', 'description' => 'edit general settings'),
    array('name' => 'Email', 'uri' => 'admin/settings/email', 'description' => 'edit email settings'),
    array('name' => 'Date & Time', 'uri' => 'admin/settings/datetime', 'description' => 'edit date & time settings'),
    array('name' => 'Url', 'uri' => 'admin/settings/url', 'description' => 'edit url settings')
);
?>
