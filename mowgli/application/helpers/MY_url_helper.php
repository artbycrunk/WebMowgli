<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');


/**
 * gets admin resource uri
 * Eg. static/modules/<module_name>
 */
if (!function_exists('admin_resource_uri')) {

        function admin_resource_uri() {

                $ci = & get_instance();
                return $ci->config->item('admin_resource_uri');
        }

}

/**
 * gets admin resource url
 * Eg. http://sitename.com/static/modules/<module_name>
 */
if (!function_exists('admin_resource_url')) {

        function admin_resource_url() {

                return site_url(admin_resource_uri());
        }

}

if (!function_exists('admin_resource_path')) {

        function admin_resource_path() {

                $ci = & get_instance();
                return $ci->config->item('admin_resource_path');
        }

}


if (!function_exists('module_resource_path')) {

        function module_resource_path($moduleName) {

                $ci = & get_instance();
                return $ci->config->item('module_resource_root_path') . "/$moduleName";
        }

}

/**
 * gets module resource uri
 * Eg. static/modules/<module_name>
 */
if (!function_exists('module_resource_uri')) {

        function module_resource_uri($moduleName) {

                $ci = & get_instance();
                return $ci->config->item('module_resource_root_uri') . "/$moduleName";
        }

}

/**
 * gets module resource url
 * Eg. http://sitename.com/static/modules/<module_name>
 */
if (!function_exists('module_resource_url')) {

        function module_resource_url($moduleName) {

                return site_url(module_resource_uri($moduleName));
        }

}

if (!function_exists('module_path')) {

        function module_path($moduleName = null) {

                $ci = & get_instance();
                return $ci->config->item('module_path') . "/$moduleName";
        }

}

if (!function_exists('dump_path')) {

        function dump_path() {

                $ci = & get_instance();
                return $ci->config->item('dump_path');
        }

}

if (!function_exists('dump_uri')) {

        function dump_uri() {

                $ci = & get_instance();
                return $ci->config->item('dump_uri');
        }

}


if (!function_exists('site_path')) {

        /**
         * Gets full site path from root directory
         * Eg. c:/some/folder/structure/SITE
         *
         * @param string $uri uri to concatinate with sitepath
         *
         * @return string|false returns realpath on success, false on failure
         */
        function site_path($uri = null) {

                $ci = & get_instance();
                $path = $ci->config->item('site_path') . "/$uri";
                return realpath($path);
        }

}

if (!function_exists('site_resource_path')) {

        function site_resource_path( $uri = null ) {

                $ci = & get_instance();
                return $ci->config->item('site_resource_path') . "/$uri";
        }

}

if (!function_exists('relative_root_path')) {

        /**
         * returns relative path from $_SERVER['DOCUMENT_ROOT']
         * Eg. /some/folder/structure/SITE
         *
         * @param string $uri uri to concatinate with sitepath
         *
         * @return string|false returns realpath on success, false on failure
         */
        function relative_root_path($uri = null) {

//                $ci = & get_instance();

                $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
                $uriPath = realpath(site_path($uri));       // note realpath not required here

                $path = str_replace($docRoot, "", $uriPath);  // remove common paths between $docRoot and $uriPath
                $path = str_replace("\\", "/", $path);        // replace '\' with '/'

                return $path;
        }

}


if (!function_exists('get_page_name')) {

        /**
         * gets page name from db using current url
         *
         * @return string|null returns database page name on success, unull on failure
         */
        function get_page_name() {

                $ci = & get_instance();

//                $uri =
                return $path;
        }

}

if (!function_exists('upload_uri')) {

        function upload_uri($uri = "") {

                $ci = & get_instance();
                return $ci->config->item('upload_uri') . "/$uri";
        }

}

if (!function_exists('upload_module_uri')) {

        function upload_module_uri($uri = "") {

                $ci = & get_instance();
                return $ci->config->item('upload_module_uri') . "/$uri";
        }

}

if (!function_exists('get_module_prefix')) {

        /**
         * Get a particular modules url prefix value stored in settings ( database )
         *
         * @param $module module name
         *
         * @return string|null return module prefix if found else returns null
         */
        function get_module_prefix($module) {

                $ci = & get_instance();
                $ci->load->helper('settings');

                $modulePrefixes = get_settings(WM_SET_CATEG_URL, 'module_url_prefixes');

                return isset( $modulePrefixes[$module] ) ? $modulePrefixes[$module] : null;
        }

}

// // NOTE: function WORKS PERFECTLY, commented to avoid accidental overriting of prefix settings, all prefixes should be unique.
//if (!function_exists('set_module_prefix')) {
//
//
//        /**
//         * Adds/edits a module prefix to the settings
//         *
//         * @param string $module module name
//         * @param string $prefix url prefix for given module
//         *
//         * @return bool true on success, false on failure
//         */
//        function set_module_prefix($module, $prefix) {
//
//                $ci = & get_instance();
//                $ci->load->helper('settings');
//
//                // get existing mapping, between modules and prefixes
//                $modulePrefixes = get_settings(WM_SET_CATEG_URL, 'module_url_prefixes');
//
//                $modulePrefixes[$module] = $prefix;
//
//                return set_settings(WM_SET_CATEG_URL, 'module_url_prefixes', $modulePrefixes);
//        }
//
//}
?>
