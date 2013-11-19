<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */



/**
 * Set default path settings needed for admin panel views
 *
 * @author Encube
 * @link http://encube.co.in
 */

$config['admin_uri'] = 'admin';
$config['static_uri'] = 'static';


//$config['module_path'] = APPPATH . 'modules'; OLD BACKUP
$config['site_path'] = FCPATH;
$config['module_path'] = FCPATH . APPPATH . 'modules';

/* uri */

// upload directory ( write permissions )
$config['upload_uri'] = 'uploads';
$config['upload_module_uri'] = $config['upload_uri'] . '/modules';

// dump folder
$config['dump_uri'] = $config['upload_uri'] .'/dump';

// site templates & resources ( uploaded by user )
$config['site_resource_uri'] = $config['upload_uri'] . '/site';

// module resources ( for admin )
$config['module_resource_root_uri'] = $config['static_uri'] . '/modules';

// admin panel resources
$config['admin_resource_uri'] = $config['module_resource_root_uri'] . '/admin';


// path
$config['site_resource_path'] = realpath( FCPATH . $config['site_resource_uri'] );
$config['module_resource_root_path'] = realpath( FCPATH . $config['module_resource_root_uri'] );
$config['admin_resource_path'] = realpath( FCPATH . $config['admin_resource_uri'] );
$config['dump_path'] = realpath( FCPATH . $config['dump_uri'] );


?>
