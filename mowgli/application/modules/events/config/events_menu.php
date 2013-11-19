<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Events';
$config['menu_icon'] = '#';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Events module';

$config['sub_menu'] =  array(

        array( 'name' => 'add', 'uri' => 'admin/events/add', 'description' => 'Add a new event'  ),
        array( 'name' => 'manage', 'uri' => 'admin/events/manage', 'description' => 'Manage events'  )


);

?>
