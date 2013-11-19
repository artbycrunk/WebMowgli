<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Set default Site settings related constants and variables
 *
 * @author Encube
 * @link http://encube.co.in
 */

//$config['admin_uri'] = 'admin';
//
//$config['dump_path'] = FCPATH . 'static/dump';
////$config['module_path'] = APPPATH . 'modules'; OLD BACKUP
//$config['site_path'] = FCPATH;
//$config['module_path'] = FCPATH . APPPATH . 'modules';

$config[''] = '';       // dummy -- remove if any config gets added to hthis file

// Data types for settings
! defined( "SETTING_TYPE_BOOL" ) ? define( "SETTING_TYPE_BOOL", "bool" ) : null;
! defined( "SETTING_TYPE_ARRAY" ) ? define( "SETTING_TYPE_ARRAY", "array" ) : null;
! defined( "SETTING_TYPE_STRING" ) ? define( "SETTING_TYPE_STRING", "string" ) : null;

// Delimiter for settings of 'array' type ( temporary, will not be needed once serialize/unserialize is used )
! defined( "SETTING_DELIMITER" ) ? define( "SETTING_DELIMITER", "|" ) : null;

?>
