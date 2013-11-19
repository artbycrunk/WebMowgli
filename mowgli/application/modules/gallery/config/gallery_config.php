<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['gallery_resource_uri'] = "gallery";
$config[ WM_HAS_TEMPLATES ] = true;       // to allow users to create templates for Gallery module


$config['themes_resource_uri'] = $config['gallery_resource_uri'] . "/themes";     // for theme resources
$config['gallery_images_uri'] = $config['gallery_resource_uri'] . "/images";       // for images, thumbnails of gallery


/* template types */
$config['gallery_type_galleries']       = "galleries";          // both categories and images, ( images inside categories )
$config['gallery_type_galleries_split']	= "galleries_split";    // both categories and images, but images NOT inserted in Categories
$config['gallery_type_categories']      = "categories";         // only category data
$config['gallery_type_images']          = "images";             // only images data



/* Uploading */
$config['image_max_size'] = "2048"; // 2*1024 KB // note this is corrected if exceeds ini_get('upload_max_filesize')
$config['zip_max_size'] = "20480"; // KB // note this is corrected if exceeds ini_get('upload_max_filesize')
$config['zip_filetype'] = "zip";
$config['unzip_preserve_path'] = true;  // true = maintain structure for unzip files, false = store all files in root folder
$config['allowed_filetypes'] = null; // Eg. js|css|png . . . etc OR null

/* Themes */
$config['theme_config_filename'] = "config.ini";                // contains configuration for the
//$config['theme_config_true'] = "true|yes|YES|Yes";
//$config['theme_config_false'] = "false|no|NO|No";
//$config['theme_config_null'] = "null|NULL|Null";

//$config['theme_config_array_keys'] = "name|version|main";
$config['theme_template_filetypes'] = "htm|html";
$config['theme_resource_root_parse_tag'] = "{theme:resource}";       // points to current gallery themes resource ( root ) folder

/* theme extraction */
$config['themes_extract_tag'] = "gallery";                      // extract tag for gallery Eg. <gallery>
$config['themes_extract_tag_type_ident'] = "type";              // tag attribute for type of extract
$config['themes_extract_type_script'] = "script";               // attribute value for script extracts
$config['themes_extract_tag_temp_ident'] = "template";          // tag identifier for template name
//$config['themes_extract_type_values'] = "categories|category|image";    // possible values for <gallery type='.....'>
$config['themes_extract_type_values'] = $config['gallery_type_categories'] . "|". $config['gallery_type_images'] . "|" . $config['gallery_type_galleries'];    // possible values for <gallery type='.....'>


/* processing relative href(s) and src(s) */
$config['search_tag_list'] = "img|link|script|input";           // List of tags for which hrefs and srcs need to be modified
$config['search_external_prefix_list'] = "http|https|www";      // list of start strings for external urls
$config['search_attrib_list'] = "href|src";                     // attributes to search for href, srcs

/* Images */
$config['images_allowed_filetypes'] = "png|jpg|JPG|jpeg|gif";
$config['images_thumbnail_prefix'] = "TH_";
//$config['items_default_category'] = "uncategorized";
//$config['items_db_type_category'] = "category";
//$config['items_db_type_image'] = "image";


/* Preprocess config values
 * ( convert string lists to arrays -- explode strings )*/

$config['allowed_filetypes'] = ( is_null( $config['allowed_filetypes'] ) ) ? null : explode( '|', $config['allowed_filetypes'] );
//$config['theme_config_array_keys'] = explode( '|', $config['theme_config_array_keys'] );
$config['themes_extract_type_values'] = explode( '|', $config['themes_extract_type_values'] );
$config['search_tag_list'] = explode( '|', $config['search_tag_list'] );
$config['search_external_prefix_list'] = explode( '|', $config['search_external_prefix_list'] );
$config['search_attrib_list'] = explode( '|', $config['search_attrib_list'] );

//$config['theme_config_true'] = explode( '|', $config['theme_config_true'] );
//$config['theme_config_false'] = explode( '|', $config['theme_config_false'] );
//$config['theme_config_null'] = explode( '|', $config['theme_config_null'] );

$config['images_allowed_filetypes'] = explode( '|', $config['images_allowed_filetypes'] );


/* if max upload size exceeds server max size, then set max-size as server size */
$upload_max_filesize = 1024 * (int) ini_get( 'upload_max_filesize' ); // value in KB
$config['zip_max_size'] = ( $upload_max_filesize <= $config['zip_max_size'] ) ? $upload_max_filesize : $config['zip_max_size'];
$config['image_max_size'] = ( $upload_max_filesize <= $config['image_max_size'] ) ? $upload_max_filesize : $config['image_max_size'];
?>
