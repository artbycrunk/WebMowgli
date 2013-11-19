<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

$config['module'] = 'blog';

// to allow users to create templates for Blog module
$config[WM_HAS_TEMPLATES] = true;

// to allow module to alter requested page depending on URL
$config[WM_MODULE_HAS_UNIQUE_URLS] = true;

// these pages are used specifically for blog module
// they will not be accessible directly
$config[ WM_MODULE_SPECIAL_PAGES ] = array( 'blog', 'blog-group', 'blog-post' );


$config['post_status_draft'] = 'draft';
$config['post_status_published'] = 'published';

// special categories
$config['category_uncategorized'] = 'uncategorized';
$config['category_featured'] = 'featured';

// Separators
$config['separator_categs'] = ', ';
$config['separator_tags'] = ', ';

// excerpt word count
$config['excerpt_word_count'] = 100;

// data type ( of each block )
$config['data_type_summary'] = 'summary';
$config['data_type_group'] = 'group';
$config['data_type_post'] = 'post';
$config['data_type_widget'] = 'widget';
$config['data_type_custom'] = 'custom';
$config['allowed_data_types'] = array('summary', 'group', 'post', 'widget', 'custom');

// this will map viewKeyword with data type
// note: this is used to map 'default_view' ( from blog settings ) with a group type
// if changes are made to blog settings 'default_view' it should be appropriately reflected here
$config['map_default_viewkeyword_type'] = array(
    'summary' => $config['data_type_summary'],
    'featured_posts' => $config['data_type_group'],
    'latest_posts' => $config['data_type_group'],
    'archives' => $config['data_type_widget'],
    'archives_categorized' => $config['data_type_widget']
);



// group types ( if type=group )
$config['data_group_type_author'] = 'author';
$config['data_group_type_category'] = 'category';
$config['data_group_type_tag'] = 'tag';
$config['data_group_type_date'] = 'date';
$config['allowed_group_types'] = array('author', 'category', 'tag', 'date');

// conditions that can be generated from uri provided
$config['allowed_conditions'] = array('author', 'category', 'tag', 'year', 'month', 'day', 'post', 'post_id', 'part');
foreach ($config['allowed_conditions'] as $key) {

        $config["condition_key_$key"] = $key;
}

// valid permalink parts for url structure for posts
$config['allowed_perma_parts'] = array('%author%', '%category%', '%tag%', '%year%', '%month%', '%day%', '%post%', '%post_id%', '%part%');

// block tags keys allowed in block tags
$config['allowed_tag_keys'] = array('view', 'template', 'category', 'tag', 'author', 'year', 'month', 'day', 'post', 'post_id', 'part');
foreach ($config['allowed_tag_keys'] as $keys) {

        $config["tag_key_$keys"] = $keys;
}


// list of default views ( keywords ) available in system
$config['view_keyword_list'] = array(
    'main',
    'summary',
    'category_posts', 'tag_posts', 'author_posts', 'date_posts', 'single_post',
    'featured_posts', 'latest_posts', 'related_posts', 'popular_posts', 'archives', 'archives_categorized', 'categories',
    'latest_comments', 'tags', 'about_author', 'pagination',
);

// view keywords for different default views ( eg. Summary, Single Post, Category Posts . . etc )
// generate using $config['view_keyword_list']
foreach ($config['view_keyword_list'] as $keyword) {

        $config["view_$keyword"] = $keyword;
}



// setting keys in database
$config['settings_keyword_list'] = array(
    'permalink', 'excerpt_word_count', 'limit',     // text
    'default_view',                                                     // drop down
    'default_header', 'default_footer', 'ad_script',    // textarea
    'allow_comments'      // checkbox ( single )
     );
foreach ($config['settings_keyword_list'] as $keyword) {

        $config["setting_$keyword"] = $keyword;
}

// shortcodes in post, categ, tags etc
$config['shortcodes_ad'] = 'ad';

// allowed meta keys for individual posts
$config['allowed_post_meta_keys'] = array( 'header', 'footer', 'featured_image','featured_image_caption' );

?>
