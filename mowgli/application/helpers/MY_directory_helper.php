<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

if (!function_exists('create_dir_path')) {

        function create_dir_path($path) {

                $success = true;

                $separator = '/';

                $path = str_replace('\\', $separator, $path);

                $dirList = explode($separator, $path);

                $tempDirArray = $dirList;
                $pathsToCreate = array();

                for ($count = count($dirList) - 1; $count >= 0; $count--) {

                        $tempPath = implode($separator, $tempDirArray);

                        if (file_exists($tempPath)) {

                                break;
                        } else {
                                $pathsToCreate[] = $tempPath;
                                unset($tempDirArray[$count]);
                        }
                }

                if (count($pathsToCreate) > 0) {

                        $pathsToCreate = array_reverse($pathsToCreate);

                        foreach ($pathsToCreate as $createPath) {

                                if (!file_exists($createPath)) {

                                        if (mkdir($createPath) == false) {

                                                $success = false;
                                                break;
                                        } else {

                                                $success = true;
                                        }
                                }
                        }
                }


                return $success;
        }

}


if (!function_exists('directory_copy')) {

        /**
         * Copy a whole Directory
         *
         * Copy a directory recrusively ( all file and directories inside it )
         *
         * @access    public
         * @param    string    path to source dir
         * @param    string    path to destination dir
         * @return    array
         */
        function directory_copy($sourceDir, $destDir) {
                //preparing the paths
                $sourceDir = rtrim($sourceDir, '/');
                $destDir = rtrim($destDir, '/');

                //creating the destenation directory
                if (!is_dir($destDir))
                        mkdir($destDir);

                //Mapping the directory
                $dir_map = directory_map($sourceDir);

                foreach ($dir_map as $object_key => $object_value) {
                        if (is_numeric($object_key))
                                copy($sourceDir . '/' . $object_value, $destDir . '/' . $object_value); //This is a File not a directory
                        else
                                directory_copy($sourceDir . '/' . $object_key, $destDir . '/' . $object_key); //this is a dirctory
                }
        }

}


if (!function_exists('get_module_dir_list')) {

        /**
         * Gets a list of module names ( folder names ) under the 'application/modules/' directory
         * the modules are sorted in alphabetical order
         *
         * @return array|null returns list of names on success, OR returns null if sorting fails.
         */
        function get_module_dir_list() {

                $modulesDir = module_path();
                $directory_depth = 1;   // get files only from root dir

                $modules = directory_map($modulesDir, $directory_depth);

                // to remove NON directories from list of files
                foreach ($modules as $key => $filename) {

                        // remove if not a directory
                        if (!is_dir("$modulesDir/$filename"))
                                unset($modules[$key]);
                }

                $success = sort($modules);

                // return sorted modules on success, else return null
                return ( $success ) ? $modules : null;
        }

}
?>
