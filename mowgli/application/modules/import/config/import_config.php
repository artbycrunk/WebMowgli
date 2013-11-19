<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$config['import_resource_uri'] = "import";
//$config['import_upload_uri'] = $config['import_resource_uri'] . "/uploads";   // General module upload folder

// Importing OR page template addition
$config['allow_import_errors'] = true;
$config['tag_meta_title'] = "{page:title}";
$config['tag_meta_description'] = "{page:description}";
$config['tag_meta_keywords'] = "{page:keywords}";

$config['module_includes'] = "includes";        // includes module

///* Uploading */
//
//$config['zip_max_size'] = "20480"; // KB // note this is corrected if exceeds ini_get('upload_max_filesize')
//$config['zip_filetype'] = "zip";
//$config['unzip_preserve_path'] = true;  // true = maintain structure for unzip files, false = store all files in root folder
//$config['allowed_filetypes'] = null; // Eg. js|css|png . . . etc OR null for all
//
///* Config */
//$config['config_filename'] = "config.ini";                // contains configuration for the import
//
///* processing relative href(s) and src(s) */
//$config['search_tag_list'] = "img|link|script|input";           // List of tags for which hrefs and srcs need to be modified
//$config['search_external_prefix_list'] = "http|https|www";      // list of start strings for external urls
//$config['search_attrib_list'] = "href|src";                     // attributes to search for href, srcs
//
///* if max upload size exceeds server max size, then set max-size as server size */
//$upload_max_filesize = 1024 * (int) ini_get( 'upload_max_filesize' ); // value in KB
//$config['zip_max_size'] = ( $upload_max_filesize <= $config['zip_max_size'] ) ? $upload_max_filesize : $config['zip_max_size'];


?>