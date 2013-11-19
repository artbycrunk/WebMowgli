<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Set default Site settings related constants and variables
 *
 * @author Encube
 * @link http://encube.co.in
 */


$config[''] = '';       // dummy -- remove if any config gets added to this file

// default number of rows to display in pagination for admin section
!defined('WM_PAGINATION_LIMIT') ? define('WM_PAGINATION_LIMIT', 10) : null;

// Admin Json Responses Status codes
!defined('WM_STATUS_SUCCESS') ? define('WM_STATUS_SUCCESS', 'success') : null;
!defined('WM_STATUS_ERROR') ? define('WM_STATUS_ERROR', 'error') : null;
!defined('WM_STATUS_WARNING') ? define('WM_STATUS_WARNING', 'warning') : null;
!defined('WM_STATUS_INFO') ? define('WM_STATUS_INFO', 'info') : null;


// Identifier for admin section, used in module/config/module_config.php to allow a users to create a template for that module or not
!defined('WM_HAS_TEMPLATES') ? define('WM_HAS_TEMPLATES', 'has_page_templates') : null;
// config key for special pages, this config will hold array of special page names for a module
!defined('WM_MODULE_SPECIAL_PAGES') ? define('WM_MODULE_SPECIAL_PAGES', 'special_pages') : null;
// to allow module to alter requested page depending on URL
!defined('WM_MODULE_HAS_UNIQUE_URLS') ? define('WM_MODULE_HAS_UNIQUE_URLS', 'has_unique_urls') : null;

define('WM_CACHE_PAGE_PREFIX', 'page_');

!defined('WM_TEMPLATE_TYPE_INCLUDE') ? define('WM_TEMPLATE_TYPE_INCLUDE', 'includes') : null;
!defined('WM_TEMPLATE_TYPE_PAGE') ? define('WM_TEMPLATE_TYPE_PAGE', 'page') : null;
!defined('WM_TEMPLATE_TYPE_MODULE') ? define('WM_TEMPLATE_TYPE_MODULE', 'module') : null;

// WM setting category keywords
!defined('WM_SET_CATEG_GENERAL') ? define('WM_SET_CATEG_GENERAL', 'general') : null;
!defined('WM_SET_CATEG_EMAIL') ? define('WM_SET_CATEG_EMAIL', 'email') : null;
!defined('WM_SET_CATEG_DATETIME') ? define('WM_SET_CATEG_DATETIME', 'datetime') : null;
!defined('WM_SET_CATEG_URL') ? define('WM_SET_CATEG_URL', 'url') : null;
!defined('WM_SET_CATEG_COMMENTS') ? define('WM_SET_CATEG_COMMENTS', 'comments') : null;
!defined('WM_SET_CATEG_INSTALLATION') ? define('WM_SET_CATEG_INSTALLATION', 'installation') : null;


?>
