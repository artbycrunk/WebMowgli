<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Discography';
$config['menu_icon'] = '#';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Manage music albums and songs';

$config['sub_menu'] =  array(

        array( 'name' => 'add album', 'uri' => 'admin/discography/add_categ', 'description' => 'Add album'  ),
        array( 'name' => 'add song', 'uri' => 'admin/discography/add_item', 'description' => 'Add songs'  ),
        array( 'name' => 'manage albums', 'uri' => 'admin/discography/manage_categs', 'description' => 'Manage albums'  ),
        array( 'name' => 'manage songs', 'uri' => 'admin/discography/manage_items', 'description' => 'Manage songs'  )

);

?>
