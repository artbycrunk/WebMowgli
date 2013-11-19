<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Videos';
$config['menu_icon'] = '#';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Videos module';

$config['sub_menu'] =  array(
        
        array( 'name' => 'add', 'uri' => 'admin/videos/add', 'description' => 'Add a new video'  ),
        array( 'name' => 'manage', 'uri' => 'admin/videos/manage', 'description' => 'Manage videos'  )


);

?>
