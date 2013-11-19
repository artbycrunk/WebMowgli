<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

$config['menu_visible'] = true;
$config['menu_name'] = 'Blog';
//$config['menu_icon'] = '#';
$config['menu_uri'] = '#';
$config['menu_description'] = 'Manage site blog';

$config['sub_menu'] = array(
    array('name' => 'add post', 'uri' => site_url('admin/blog/add_post'), 'description' => 'add a new post'),
    array('name' => 'posts', 'uri' => site_url('admin/blog/manage_posts'), 'description' => 'manage blog posts'),
    array('name' => 'categories', 'uri' => site_url('admin/blog/manage_categories'), 'description' => 'manage blog categories'),
    array('name' => 'tags', 'uri' => site_url('admin/blog/manage_tags'), 'description' => 'manage blog tags'),
    array('name' => 'settings', 'uri' => site_url('admin/blog/settings'), 'description' => 'blog settings')

);
?>
