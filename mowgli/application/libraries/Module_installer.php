<?php

/**
 * Description of Installer
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
abstract class Module_installer {

        private $moduleName;
        private $ci;
        private $resourceDirName = "resources";
        private $moduleDirName = "module";
        private $sqlFileName = "scripts.sql";

        public function  __construct( $moduleName ) {

                $this->moduleName = $moduleName;
                $this->ci = & get_instance();

                $this->ci->load->model('module_import_model');

        }

        abstract public function install();

        final public static function move_files( $tempRootDir ) {

                $success = false;

                $moduleDir = $tempRootDir . '/' . $this->moduleDirName;
                $resourceDir = $tempRootDir . '/' . $this->resourceDirName;

                if( is_dir( $moduleDir ) AND is_dir( $resourceDir ) ) {

                        // copy dirs to respective locations

                        $destModuleDir = self::get_module_path();
                        $destResourceDir = self::get_resource_path();

                        $this->ci->load->helper('directory');
                        $this->ci->directory->directory_copy( $moduleDir, $destModuleDir );     // copy module files
                        $this->ci->directory->directory_copy( $resourceDir, $destResourceDir ); // copy resources

                        $success = true;
                }
                else {

                        // ERROR: required directories not provided in module
                        $success = false;

                }

                return $success;

        }

        final public static function uninstall() {

        }

        final public static function enable() {

        }

        final public static function disable() {

        }

        final public static function get_resource_path() {
                return module_resource_path( $this->moduleName );
        }

        final public static function get_module_path() {
                return module_path( $this->moduleName );
        }

        final public static function run_sql( $rootPath ){

                $sqlFile = "$rootPath/" . $this->sqlFileName ;
                                //read the file
                $sql = $this->ci->load->file( $sqlFile, true);
                
                $this->ci->module_import_model->run_sql();

        }
}


/* End of file Installer.php */
/* Location: ./application/.... Installer.php */
?>
