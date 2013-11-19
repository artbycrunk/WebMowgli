<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Gallery';
//$config['menu_icon'] = 'images/icon.png';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Manage site gallery';

$config['sub_menu'] =  array(

        array( 'name' => 'manage', 'uri' => site_url( 'admin/gallery/manage' ), 'description' => 'manage categories and images'  ),
        array( 'name' => 'zip upload', 'uri' => site_url( 'admin/gallery/upload_images' ), 'description' => 'upload images from a zip file'  ),
        array( 'name' => 'import theme', 'uri' => site_url( 'admin/gallery/import_theme' ), 'description' => 'import a gallery theme'  ),
        array( 'name' => 'settings', 'uri' => site_url( 'admin/gallery/settings' ), 'description' => 'edit gallery settings'  )

);

?>
