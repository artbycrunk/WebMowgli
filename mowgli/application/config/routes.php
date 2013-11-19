<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	http://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There area two reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router what URI segments to use if those provided
  | in the URL cannot be matched to a valid route.
  |
 */
$route['default_controller'] = "page";

$route['404_override'] = '';

// routes for installation
$route['_install'] = '_install';
$route['_install/(:any)'] = '_install/$1';


/**
 * To reroute all other requests to default controller
 * @author Encube
 * @link http://encube.co.in
 */
$route['admin'] = 'admin';
$route['admin/(:any)'] = 'admin/$1';



// routes for login controllers
$route['login'] = 'user/login';
$route['logout'] = 'user/logout';

// 'user' module override
$route['user'] = 'user';
$route['user/(:any)'] = 'user/$1';


/* * ******** Custom overrde routes *********** */

// Add any custom routes here if needed.

/* * ******** END Custom overrde routes *********** */


// Override default page controller IF application/controllers defined

$controllerDir = APPPATH . "controllers";
$controllers = scandir($controllerDir);
if ($controllers !== false AND is_array($controllers)) {

        foreach ($controllers as $file) {

                // check if file is a php file
                if ((bool) preg_match("/(\.php)$/i", $file)) {

                        // file is a php file
                        // remove .php from end and create route
                        $file = substr_replace(strtolower($file), "", - strlen(".php"));

                        // for default method ( index )
                        $route[$file] = $file;

                        // for other methods
                        $route["$file/(:any)"] = "$file/$1";
                }
                // check if file is a directory, YES --> directly add to routes
                elseif (is_dir("$controllerDir/$file") AND $file !== '.' AND $file !== '..' ) {

                        $route["$file/(:any)"] = "$file/$1";
                }
        }
}
unset($controllerDir);
unset($controllers);

// Default Page Controller
$route['(:any)'] = 'page/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */