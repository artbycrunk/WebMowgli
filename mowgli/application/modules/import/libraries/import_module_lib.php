<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of import_module_lib
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class import_module_lib {

        private $module = "import";

        private $ci;
        private $tempPaths = array();
        private $undoPaths = array();

        private $configFile = "import_config";

        public function  __construct() {

                $this->ci = & get_instance();

                $this->ci->config->load( $this->configFile, true, true, $this->module );
        }

        public function install_module( $zipFile ) {

                $this->ci->load->library('notifications');

                /* Set value for unzipping */
                $dumpPath = $this->ci->config->item( 'dump_path' );
                $unzipPath = "$dumpPath/install-module-" . time();        // temporary, deleted later
                $unzipPath = mkdir( $unzipPath ) == false ? null : $unzipPath;
                $this->_add_temp_path( $zipFile );
                $this->_add_temp_path( $unzipPath );


                $allowedFiletypes = null;       // allow all types
                $unzipPreservePath = true;      // true

                /* Unzip file */
                $this->ci->load->library( 'unzip' );
                $fileLocations = $this->ci->unzip->unzip_file( $zipFile, $unzipPath, $allowedFiletypes, $unzipPreservePath );

                if( $fileLocations != false ) {

                        /* unzip successfull */

                        
                        /* Logic
                         *
                         copy resources
                         copy module files
                         call install function set in config file, insert into db, register functions with hooks
                         Run sql
                         Clear temporary folders

                         *
                        */

                        // add unzipped files for deletion later
                        $this->_add_temp_path( $fileLocations );

                        Module_installer::move_files( $unzipPath );

                        $this->_add_undo_path( Module_installer::get_module_path() );
                        $this->_add_undo_path( Module_installer::get_resource_path() );
                        
                        /*
                         * check if module_specific installer is existing and callable
                         * load library
                         * run install()
                         *
                         * if true -->
                         *
                         */


//                        $this->ci->load->library('config_reader');
//
//
//                        /* get config filename */
//                        $configFileName = $this->ci->config->item( 'config_filename', $this->configFile );
//
//                        /* get path of the config file */
//                        $configFile = "$unzipPath/$configFileName";
//
//                        /* read config.ini file, suppress errors or warnings */
//                        $config = $this->ci->config_reader->read( $configFile );

//                        /* Check if config correctly read */
//                        if( ! is_null( $config ) ) {
//
//                                // no errors with config file
//
//
//
//
//                        }
//                        else {
//
//                                // error reading config file
//
//                                $errors = $this->config_reader->get_notifications( 'error' );
//
//
//                                foreach ( $errors as $msg ) {
//
//                                        $this->ci->notifications->add( 'error', $msg );
//
//                                }
//
//
//                        }


                }
                else {
                        /* NOT successfully unzipped, call back form, parse error message */
                        $error = "uploaded file could not be extracted";

                        $this->ci->notifications->add('error', $error );
                        $this->ci->parseData['admin:error_message'] = $error;
                        $finalOutput = $this->ci->parser->parse('import/import_site', $this->parseData, true );
                }

                $this->ci->notifications->save();

        }


        /**
         * Adds single file(string) OR array of files to tempPath variable
         *
         * @param string|array $files single string OR array of string paths
         *
         * @return void
         */
        private function _add_temp_path( $files ) {

                if( is_array( $files ) ){

                        $this->tempPaths = array_merge( $this->tempPaths, $files );

                }
                elseif( is_string( $files ) ){

                        $this->tempPaths[] = $files;
                }
                
        }

        /**
         * Adds single file(string) OR array of files to undoPaths variable
         *
         * @param string|array $files single string OR array of string paths
         *
         * @return void
         */
        private function _add_undo_path( $files ) {

                if( is_array( $files ) ){

                        $this->undoPaths = array_merge( $this->undoPaths, $files );

                }
                elseif( is_string( $files ) ){

                        $this->undoPaths[] = $files;
                }

        }

        /**
         * Deletes files listed in $this->tempPaths,
         * removes file from list if deletion successfull
         * if deletion NOT successfull,
         *      keep file in list
         *      return false
         *
         * @return bool true if ALL files deleted, FALSE if atleast one failure
         */
        private function _delete_temp_paths() {

                $success = true;

                if( ! is_null( $this->tempPaths ) AND is_array( $this->tempPaths )) {

                        $this->load->helper('file');

                        foreach ( $this->tempPaths as $key => $file ) {

                                if( file_exists( $file ) AND $file != '.' AND $file != '..' AND $file != '/' ) {

                                        // check if file to be deleted is a directory or a regular file
                                        if( is_dir( $file ) ) {

                                                // Delete inner files and directories
                                                if( delete_files( $file, TRUE ) ) {

                                                        rmdir( $file );

                                                        unset ( $this->tempPaths[ $key ] );

                                                }
                                                else {

                                                        $success = false;

                                                }

                                        }
                                                /* unlink regular file */
                                        else {

                                                if( unlink( $file ) ) {

                                                        unset ( $this->tempPaths[ $key ] );

                                                }
                                                else {
                                                        $success = false;
                                                }

                                        }

                                }

                        }

                }

                return $success;

        }
}


/* End of file import_module_lib.php */
/* Location: ./application/.... import_module_lib.php */
?>
