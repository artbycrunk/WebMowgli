<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of import
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
/* * ************** Settings Start *************************************** */

define('IMPORT_SITE_MAX_ZIP_SIZE', '5120'); // 5120 KB = 5 MB
define('IMPORT_SITE_FILETYPE', 'zip');
define('INSTALL_MODULE_FILETYPE', 'zip');

/* * ************** Settings End *************************************** */

//define('WM_TEMPLATE_TYPE_PAGE', 'page');
//define('WM_TEMPLATE_TYPE_INCLUDE', 'includes');


define('FILETYPE_DELIMITER', '|');
//define('ALLOWED_FILETYPES', "css|js|png|gif|jpeg|jpg|tpl|html|swf|php"); // null for all files
define('ALLOWED_FILETYPES', null); // null for all files
define('PAGE_FILETYPES', 'html|htm|php');
define('UNZIP_PRESERVE_PATH', true);

define('FILE_ARRAY_FILENAME', 'filename');
define('FILE_ARRAY_FILENAME_FULL', 'filename_full');
define('FILE_ARRAY_FILETYPE', 'extension');
define('FILE_ARRAY_FILEPATH', 'filepath');
define('FILE_ARRAY_FILE_HTML', 'file_html');

//define('MODULE_INCLUDES', 'includes');

class Import extends Admin_Controller {

        private $configFile = "import_config";
        private $module = "import";

        public function __construct() {
                parent::__construct();


                $this->config->load($this->configFile, true, true, $this->module);

                $this->load->library('notifications');
                $this->load->library('import/import_site_lib');

                !defined('ALLOW_IMPORT_ERRORS') ? define('ALLOW_IMPORT_ERRORS', $this->config->item('allow_import_errors', $this->configFile)) : null;
                !defined('TAG_META_TITLE') ? define('TAG_META_TITLE', $this->config->item('tag_meta_title', $this->configFile)) : null;
                !defined('TAG_META_DESCRIPTION') ? define('TAG_META_DESCRIPTION', $this->config->item('tag_meta_description', $this->configFile)) : null;
                !defined('TAG_META_KEYWORDS') ? define('TAG_META_KEYWORDS', $this->config->item('tag_meta_keywords', $this->configFile)) : null;

                !defined('MODULE_INCLUDES') ? define('MODULE_INCLUDES', $this->config->item('module_includes', $this->configFile)) : null;
        }

        /* display view ot import site (zip file) */

        public function import_site() {

                $pageName = "Import Site";

                $this->load->helper('form');
                $this->parseData['admin:error_message'] = validation_errors();

                $tabHtml = $this->parser->parse('import/import_site', $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $tabHtml);
        }

        /**
         * Imports templates AND Pages for entire site from zip upload
         * uploads the zip file provided to temporary dump folder, validates for max size, filetype (zip)
         * Unzips zip file if successfully uploaded, copies allowedfile types/all to specified resources folder.
         * Note: overwrites existing files.
         * seperates resources (css, js, images . . etc) from html page types (htm, html, php)
         * writes resourcce types to database (for future records)
         * processes html file types to add mandatory tags(title, description, keywords),
         *  modifies resource links, extracts content replaces with suitable parseTag
         * creates templates (and pages if checked) in database.
         */
        public function import_site_do() {

                $pageName = "Import Site";

                $defaultFile = $this->input->post('default_file');
                $isCreatePage = $this->input->post('create-page');
                $finalOutput = null;

                /* get destination path where files will be unzipped */
                $destPath = $this->config->item('site_resource_path');
                // get dump Path, where zip file will be dumped temporarily
                $dumpPath = $this->config->item('dump_path');

                /* Set configuration for file upload */
                $config['upload_path'] = $dumpPath;
                $config['allowed_types'] = IMPORT_SITE_FILETYPE;
                $config['max_size'] = IMPORT_SITE_MAX_ZIP_SIZE; // KB
                $config['remove_spaces'] = true;

                $this->load->library('upload', $config);

                // check if folder locations exist, uploaded file is valid
                if ( file_exists($dumpPath) AND file_exists( $destPath ) AND $this->upload->do_upload('zip-file') === true) {

                        /* valid upload file, Also file is successfully uploaded */

                        /* Get details of zip file uploaded */
                        $fileData = $this->upload->data();
                        $zipFile = $fileData['full_path'];


                        /* allowedFiletypes = null ==> all filetypes allowed
                         * allowedFiletypes = array of filetypes ==> only mentioned filetypes */
                        $allowedFiletypes = ( is_null(ALLOWED_FILETYPES) ? ALLOWED_FILETYPES : explode(FILETYPE_DELIMITER, ALLOWED_FILETYPES) );

                        /* perform unzip, get list of all unzipped (valid) files in array with full paths */
                        $this->load->library('unzip');
                        $fileLocations = $this->unzip->unzip_file($zipFile, $destPath, $allowedFiletypes, UNZIP_PRESERVE_PATH);

                        /* delete zip file */
                        unlink($zipFile);

                        /* check if successfully unzipped */
                        if ($fileLocations != false) {

                                /* unzip successfull */

                                /* Seperates page/template types from resource types.
                                 * returns array( 'pages', 'resources' ) with file details */
                                $fileDetails = $this->import_site_lib->seperate_pages_resources($fileLocations);

                                $pageFilesArray = $fileDetails['pages'];
                                $resourceFilesArray = $fileDetails['resources'];

                                /**
				 * *********************** Actual Importing Begins *****************************
				 */

                                $this->load->library('domparser');
                                $this->load->model('import/import_model');
                                $db = & $this->import_model->get_db();

                                $this->import_model->transaction_strict(!ALLOW_IMPORT_ERRORS);

                                $resourcesDb = array();

                                // process resources
                                foreach ($resourceFilesArray as $resource) {

                                        $tempDestPath = realpath($destPath);
                                        $relativePath = substr_replace($resource[FILE_ARRAY_FILEPATH], '', 0, strlen($tempDestPath) + 1);
                                        $rootRelativePath = $this->config->item('site_resource_uri') . "/$relativePath";
                                        $rootRelativePath = str_replace("\\", '/', $rootRelativePath);
//                                        $resourceUri = site_url( $rootRelativePath );
                                        $resourceUri = $rootRelativePath;

                                        $resourcesDb[] = array(
                                            //'resource_id' => null,
                                            'resource_name' => $resource[FILE_ARRAY_FILENAME_FULL],
                                            'resource_filetype' => $resource[FILE_ARRAY_FILETYPE],
                                            //  'resource_full_path' => $resource[ FILE_ARRAY_FILEPATH ], // Commented -- do not store full paths in db
                                            'resource_relative_path' => $rootRelativePath,
                                            'resource_uri' => $resourceUri,
                                            'resource_modified' => get_gmt_time() // current GMT time
                                        );
                                }

                                // insert in database, also ignore if duplicate values or errors found
                                count($resourcesDb) > 0 ? $this->import_model->create_resources($resourcesDb, true) : null;


                                // shift default file ( if exists ) to top of array to be processed first
                                $defaultFile = realpath("$destPath/$defaultFile");
                                if (file_exists($defaultFile)) {

                                        foreach ($pageFilesArray as $key => $file) {

                                                if ($file[FILE_ARRAY_FILEPATH] == $defaultFile) {

                                                        // backup default file value
                                                        $tempValue = $pageFilesArray[$key];
                                                        // remove default file value from array
                                                        unset($pageFilesArray[$key]);
                                                        // add default file to start of array
                                                        array_unshift($pageFilesArray, $tempValue);
                                                        break;
                                                }
                                        }
                                }



                                // Run through all files, perform importing for each
                                foreach ($pageFilesArray as $file) {

                                        $this->import_model->transaction_begin();

                                        // get DOM of current file
                                        $dom = $this->domparser->file_get_html($file[FILE_ARRAY_FILEPATH]);

                                        $tempData = array(
                                            'temp_id' => null,
                                            'temp_name' => $file[FILE_ARRAY_FILENAME],
                                            'temp_type' => WM_TEMPLATE_TYPE_PAGE, // TEMPLATE_TYPE = page
                                            'temp_html' => $dom->save(), // created later in import_site_lib->import() function
                                            'temp_created' => get_gmt_time(),
                                            //    'temp_modified' => null,
                                            'temp_description' => null, // null - since template imported from zip file
                                            'temp_is_visible' => true
                                        );

                                        $dom->clear();
                                        unset($dom);

                                        // process current file for importing.
                                        $importSuccess = $this->import_site_lib->import($tempData, $tempData['temp_html'], $db, $isCreatePage);

                                        if ($importSuccess == true) {

                                                $this->import_model->transaction_commit();
                                                $finalOutput = "<p>Site successfully imported</p>";
                                        } else {

                                                $this->import_model->transaction_rollback();

                                                $this->notifications->add('error', "Template '" . $tempData['temp_name'] . "' - NOT imported. Some errors have occured");

                                                $finalOutput = "<p>Some errors occurred while importing site</p>";
                                        }
                                } // end foreach


                                /*                                 * ********************************* End of importing ****************************************************************** */
                        } else {
                                /* NOT successfully unzipped, call back form, parse error message */
                                $error = "uploaded file could not be extracted";

                                $this->notifications->add('error', $error);
                                $this->parseData['admin:error_message'] = $error;
                                $finalOutput = $this->parser->parse('import/import_site', $this->parseData, true);
                        }
                } else {

                        /* file NOT valid */
                        //$this->set_error( $this->upload->display_errors() );
                        $this->notifications->add('error', $this->upload->display_errors());
                        $this->notifications->add('error', "Some error occured while trying to upload file");

                        $this->parseData['admin:error_message'] = $this->upload->display_errors();
                        $finalOutput = $this->parser->parse('import/import_site', $this->parseData, true);
                }

                $this->notifications->save();
                $messages = $this->notifications->get('all', true);

                /**
                 * @todo convert notification messages to json, send to front end
                 */
                /*                 * ***** TEMPORARY FOR TESTING ************* */
                foreach ($messages as $type => $list) {
                        $finalOutput .= "<br/><br/><b>Message Type -- $type</b><br/>";
                        foreach ($list as $message) {
                                $finalOutput .= "<br/>$message";
                        }
                }
                /*                 * ******************************************* */

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $finalOutput);
        }

        /**
         * Displays form to add new page
         */
        public function add_template() {

                $pageName = "Add Template";

                $this->_set_add_template_form_values();
//                echo $this->parser->parse('import/add_template', $this->parseData, true );


                $finalOutput = $this->parser->parse('import/add_template', $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $finalOutput);
        }

        /**
         * Imports single template
         * processes html file types to add mandatory tags(title, description, keywords),
         *  modifies resource links, extracts content replaces with suitable parseTag
         * creates/(or edits existing) template in database.
         */
        public function add_template_do() {

                $tempName = $this->security->xss_clean($this->input->post('name'));
                $tempHtml = $this->input->post('html');

                /* Form Validations
                 * ' // $this->form_validation->CI = & $this;; ' is required due to Modular Extensions  */
                $this->load->library('form_validation');
                // $this->form_validation->CI = & $this;;

                /* Form Validation - Rules */
                $this->form_validation->set_rules('name', 'Name', 'required');
                $this->form_validation->set_rules('html', 'Template Code', 'required');

                if ($this->form_validation->run()) {
                        $this->load->library('import/import_site_lib');

                        $fileArray = array();
                        $fileArray[] = array(
                            FILE_ARRAY_FILENAME => $tempName,
                            FILE_ARRAY_FILE_HTML => $tempHtml,
                            FILE_ARRAY_FILENAME_FULL => null,
                            FILE_ARRAY_FILEPATH => null,
                            FILE_ARRAY_FILETYPE => null
                        );

                        if ($this->import_site_lib->import_site_templates($fileArray)) {
                                // template import successfull
                                /**
                                 * @todo parse suitable view on SUCCESS after Template created
                                 */
                                echo 'Import TEMPLATE SUCCESS';
                        } else {
                                // template NOT imported
                                /**
                                 * @todo parse suitable view on errors found and templated NOT CREATED
                                 */
                                echo "import TEMPLATE FAILED";
                        }

//$this->show_notifications(); /** REMOVE */
                } else {
//            echo "this is in add_template_do -- but called directly OR with validation errors";
                        // form NOT valid Callback form
                        $this->_set_add_template_form_values();
                        $this->parser->parse('import/add_template', $this->parseData);
                }
        }

        public function install_module() {

                $pageName = "Install Module";

                $this->parseData['action_url'] = site_url('admin/import/install_module_do');

                $this->_set_install_module_form_values();
                $tabHtml = $this->parser->parse('import/install_module', $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $tabHtml);
        }

        public function install_module_do() {

                $pageName = "Install Module";

                $finalOutput = null;

                /* Set configuration for file upload */
                $config['upload_path'] = $this->config->item('dump_path');
                $config['allowed_types'] = INSTALL_MODULE_FILETYPE;     // zip
                $config['max_size'] = IMPORT_SITE_MAX_ZIP_SIZE;         // KB
                $config['remove_spaces'] = true;

                $this->load->library('upload', $config);
                $this->load->library('notifications');


                /* check if uploaded file is valid */
                if ($this->upload->do_upload('zip-file') == true) {

                        /* valid upload file, Also file is successfully uploaded */

                        /* Get details of zip file uploaded */
                        $fileData = $this->upload->data();
                        $zipFile = $fileData['full_path'];

                        $this->load->library('import/import_module_lib');
//
//                        /* Set value for unzipping */
//                        $dumpPath = $this->config->item( 'dump_path' );
//                        $unzipPath = "$dumpPath/install-module-" . time();        // temporary, deleted later
//                        $unzipPath = mkdir( $unzipPath ) == false ? null : $unzipPath;
//
//                        $allowedFiletypes = null;       // allow all types
//                        $unzipPreservePath = true;      // true
//
//                        /* Unzip file */
//                        $this->load->library( 'unzip' );
//                        $fileLocations = $this->unzip->unzip_file( $zipFile, $unzipPath, $allowedFiletypes, $unzipPreservePath );

                        /* check if successfully unzipped */
//                        if( $fileLocations != false ) {
//
//                                /* unzip successfull */
//
//                                /* Seperates page/template types from resource types.
//                                 * returns array( 'pages', 'resources' ) with file details */
//                                $fileDetails = $this->import_site_lib->seperate_pages_resources( $fileLocations );
//
//                                $pageFilesArray = $fileDetails['pages'];
//                                $resourceFilesArray = $fileDetails['resources'];
//
//                                /************************* Actual Importing Begins *****************************************************************************/
//
//                                $this->load->library('domparser');
//                                $this->load->model('import/import_model');
//                                $db = & $this->import_model->get_db();
//
//                                $this->import_model->transaction_strict( ! ALLOW_IMPORT_ERRORS );
//
//                                $resourcesDb = array();
//
//                                foreach ($resourceFilesArray as $resource ) {
//
//                                        $tempDestPath = realpath( $destPath );
//                                        $relativePath = substr_replace( $resource[ FILE_ARRAY_FILEPATH ], '', 0, strlen( $tempDestPath ) + 1 );
//                                        $rootRelativePath = $this->config->item('site_resource_uri') . "/$relativePath";
//                                        $resourceUri = site_url() . str_replace( "\\", '/', $rootRelativePath); // change '\' to '/'
//
//                                        $resourcesDb[] = array(
//                                                'resource_id' => null,
//                                                'resource_name' => $resource[ FILE_ARRAY_FILENAME_FULL ],
//                                                'resource_filetype' => $resource[ FILE_ARRAY_FILETYPE ],
//                                                'resource_full_path' => $resource[ FILE_ARRAY_FILEPATH ],
//                                                'resource_relative_path' => $rootRelativePath,
//                                                'resource_uri' => $resourceUri,
//                                                'resource_modified' => get_gmt_time() // current GMT time
//                                        );
//                                }
//                                // insert in database, also ignore if duplicate values or errors found
//                                count( $resourcesDb ) > 0 ? $this->import_model->create_resources( $resourcesDb, true ) : null;
//
//                                // Run through all files, perform importing for each
//                                foreach ($pageFilesArray as $file) {
//
//                                        $this->import_model->transaction_begin();
//
//                                        $dom = $this->domparser->file_get_html( $file[ FILE_ARRAY_FILEPATH ] );
//
//                                        $tempData = array(
//                                                'temp_id' => null,
//                                                'temp_name' => $file[ FILE_ARRAY_FILENAME ],
//                                                'temp_type' => WM_TEMPLATE_TYPE_PAGE, // TEMPLATE_TYPE = page
//                                                'temp_html' => $dom->save() , // created later in import_site_lib->import() function
//                                                'temp_created' => get_gmt_time(),
//                                                //    'temp_modified' => null,
//                                                'temp_description' => null, // null - since template imported from zip file
//                                                'temp_is_visible' => true
//                                        );
//
//                                        $dom->clear();
//                                        unset ($dom);
//
//                                        $importSuccess = $this->import_site_lib->import( $tempData, $tempData['temp_html'], $db, $isCreatePage );
//
//                                        if ( $importSuccess == true ) {
//
//                                                $this->import_model->transaction_commit();
//                                                $finalOutput = "<p>Site successfully imported</p>";
//
//                                        }
//                                        else {
//
//                                                $this->import_model->transaction_rollback();
//
//                                                $this->notifications->add('error', "Template '" . $tempData['temp_name'] . "' - NOT imported. Some errors have occured" );
//
//                                                $finalOutput = "<p>Some errors occurred while importing site</p>";
//                                        }
//
//                                } // end foreach
//
//
//                                /*********************************** End of importing *******************************************************************/
//
//                        }
//                        else {
//                                /* NOT successfully unzipped, call back form, parse error message */
//                                $error = "uploaded file could not be extracted";
//
//                                $this->notifications->add('error', $error );
//                                $this->parseData['admin:error_message'] = $error;
//                                $finalOutput = $this->parser->parse('import/import_site', $this->parseData, true );
//                        }
                } else {

                        /* file NOT valid */
                        //$this->set_error( $this->upload->display_errors() );
                        $this->notifications->add('error', $this->upload->display_errors());

                        $this->parseData['admin:error_message'] = $this->upload->display_errors();
                        $finalOutput = $this->parser->parse('import/import_site', $this->parseData, true);
                }

                $this->notifications->save();
                $messages = $this->notifications->get('all', true);

                /**
                 * @todo convert notification messages to json, send to front end
                 */
                /*                 * ***** TEMPORARY FOR TESTING ************* */
                foreach ($messages as $type => $list) {
                        $finalOutput .= "<br/><br/><b>Message Type -- $type</b><br/>";
                        foreach ($list as $message) {
                                $finalOutput .= "<br/>$message";
                        }
                }
                /*                 * ******************************************* */

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $finalOutput);
        }

        /*         * ******************* Private methods ******************************************************** */

        private function _set_add_template_form_values() {

                $this->load->helper('form');

                $this->parseData['add_template:name'] = set_value('name');
                $this->parseData['add_template:create-page'] = ( $this->input->post('create-page') == true ? 'checked' : '' );
                $this->parseData['add_template:page-name'] = $this->input->post('page-name');
                $this->parseData['add_template:html'] = set_value('html');
                $this->parseData['add_template:description'] = $this->input->post('description');

                $this->parseData['admin:error_message'] = validation_errors();
        }

        private function _set_install_module_form_values() {

                $this->load->helper('form');
                $this->parseData['admin:error_message'] = validation_errors();
        }

}

?>
