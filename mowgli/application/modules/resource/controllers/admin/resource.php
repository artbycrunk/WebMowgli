<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Resource
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
define("IMAGE_FILE_EXTENSION_LIST", "bmp|gif|jpg|png|psd|pspimage|thm|tif|yuv");
define("TEXT_FILE_EXTENSION_LIST", "css|js|json|xml|htm|html|xhtml|asp|cer|csr|jsp|php|rss");

class Resource extends Admin_Controller {

        public function __construct() {

                parent::__construct();

                $this->config->load('site_config');
                $this->load->library('pagination');

                $moduleResourceRootPath = site_url() . $this->config->item('module_resource_root_uri');
                $this->parseData['module:resource'] = "$moduleResourceRootPath/" . strtolower(__CLASS__);
        }

        public function manage($tabId = null, $pageNo = 0) {

                $pageName = "Manage Resources";
                $defaultTabName = 'css';
                $module = strtolower(__CLASS__);
                $method = strtolower(__FUNCTION__);
                $pageUrl = site_url("admin/$module/$method");
                $selectedTabLink = null;
                $mainHtml = null;
                $tabListData = null;

                $viewFile = "$module/$method/index";

                $tabId = is_null($tabId) ? strtolower($defaultTabName) : strtolower($tabId);
                $selectedTabLink = "$pageUrl/$tabId";
                $tabListData = array(
                    array('tab_name' => 'css', 'tab_link' => "$pageUrl/css"),
                    array('tab_name' => 'javascript', 'tab_link' => "$pageUrl/js"),
                    array('tab_name' => 'images', 'tab_link' => "$pageUrl/images"),
                    array('tab_name' => 'other', 'tab_link' => "$pageUrl/other")
                );

                $deleteControllerPath = site_url("admin/resource/delete/");
                $editControllerPath = site_url("admin/resource/edit/");

                // calculate pagination offset
                $offset = $this->pagination->get_offset($pageNo, WM_PAGINATION_LIMIT);

                $this->parseData['displayImages'] = false; // default value, must be overidden for images
                $this->parseData['displayFileType'] = false;

                switch ($tabId) {
                        case 'css':

                                $filetypes = array('css');

                                $this->load->model('resource/resource_manage_model');
                                $resources = $this->resource_manage_model->get_resources($filetypes, true, WM_PAGINATION_LIMIT, $offset); // true == search for css

                                // set pagination params
                                $config['base_url'] = "$pageUrl/$tabId/";
                                $config['total_rows'] = $this->resource_manage_model->total_row_count();

                                // initialize pagination & create pagination links
                                $this->pagination->initialize($config, $pageNo);
                                $paginationLink = $this->pagination->create_links();

                                if (!is_null($resources)) {

                                        $pageData = array();
                                        foreach ($resources as $row) {

                                                $pageData[] = array(
                                                    'resource:id' => $row['id'],
                                                    'resource:name' => $row['name'],
                                                    'resource:filetype' => $row['filetype'],
                                                    'resource:path' => $row['path'],
                                                    'resource:modified' => $row['modified'],
                                                    'resource:edit_link' => $editControllerPath . $row['id'],
                                                    'resource:delete_link' => $deleteControllerPath . $row['id']
                                                );
                                        }

//                                        $this->parseData['displayImages'] = false;
                                        $this->parseData['resource:type'] = $tabId;
                                        $this->parseData['resource:row'] = $pageData;
                                        $this->parseData['pagination'] = $paginationLink;

                                        // $mainHtml = $this->parser->parse("$module/$method/$tabId", $this->parseData, true);
                                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                                } else {
                                        $mainHtml = null;
                                }

                                break;

                        case 'js':

                                $filetypes = array('js');

                                $this->load->model('resource/resource_manage_model');
                                $resources = $this->resource_manage_model->get_resources($filetypes, true,  WM_PAGINATION_LIMIT, $offset); // true == search for js

                                // set pagination params
                                $config['base_url'] = "$pageUrl/$tabId/";
                                $config['total_rows'] = $this->resource_manage_model->total_row_count();

                                // initialize pagination & create pagination links
                                $this->pagination->initialize($config, $pageNo);
                                $paginationLink = $this->pagination->create_links();

                                if (!is_null($resources)) {

                                        $pageData = array();
                                        foreach ($resources as $row) {

                                                $pageData[] = array(
                                                    'resource:id' => $row['id'],
                                                    'resource:name' => $row['name'],
                                                    'resource:filetype' => $row['filetype'],
                                                    'resource:path' => $row['path'],
                                                    'resource:modified' => $row['modified'],
                                                    'resource:edit_link' => $editControllerPath . $row['id'],
                                                    'resource:delete_link' => $deleteControllerPath . $row['id']
                                                );
                                        }

//                                        $this->parseData['displayImages'] = false;
                                        $this->parseData['resource:type'] = $tabId;
                                        $this->parseData['resource:row'] = $pageData;
                                        $this->parseData['pagination'] = $paginationLink;

                                        // $mainHtml = $this->parser->parse("$module/$method/$tabId", $this->parseData, true);
                                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                                } else {
                                        $mainHtml = null;
                                }

                                break;

                        case 'images':

                                $filetypes = explode('|', IMAGE_FILE_EXTENSION_LIST);

                                $this->load->model('resource/resource_manage_model');
                                $resources = $this->resource_manage_model->get_resources($filetypes, true,  WM_PAGINATION_LIMIT, $offset); // true == search for images

                                // set pagination params
                                $config['base_url'] = "$pageUrl/$tabId/";
                                $config['total_rows'] = $this->resource_manage_model->total_row_count();

                                // initialize pagination & create pagination links
                                $this->pagination->initialize($config, $pageNo);
                                $paginationLink = $this->pagination->create_links();

                                if (!is_null($resources)) {

                                        $pageData = array();
                                        foreach ($resources as $row) {

                                                $pageData[] = array(
                                                    'resource:id' => $row['id'],
                                                    'resource:name' => $row['name'],
                                                    'resource:filetype' => $row['filetype'],
                                                    'resource:img:src' => site_url($row['uri']),
                                                    'resource:path' => $row['path'],
                                                    'resource:modified' => $row['modified'],
                                                    'resource:edit_link' => $editControllerPath . $row['id'],
                                                    'resource:delete_link' => $deleteControllerPath . $row['id']
                                                );
                                        }

                                        $this->parseData['displayImages'] = true;
                                        $this->parseData['resource:type'] = $tabId;
                                        $this->parseData['resource:row'] = $pageData;
                                        $this->parseData['pagination'] = $paginationLink;

                                        // $mainHtml = $this->parser->parse("$module/$method/$tabId", $this->parseData, true);
                                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                                } else {
                                        $mainHtml = null;
                                }

                                break;

                        case 'other':

                                $excludeFiletypes = array('css', 'js');
                                $ImageFiletypes = explode('|', IMAGE_FILE_EXTENSION_LIST);
                                $excludeFiletypes = array_merge($excludeFiletypes, $ImageFiletypes);

                                $this->load->model('resource/resource_manage_model');
                                $resources = $this->resource_manage_model->get_resources($excludeFiletypes, false); // true == search for css

                                // set pagination params
                                $config['base_url'] = "$pageUrl/$tabId/";
                                $config['total_rows'] = $this->resource_manage_model->total_row_count();

                                // initialize pagination & create pagination links
                                $this->pagination->initialize($config, $pageNo);
                                $paginationLink = $this->pagination->create_links();

                                if (!is_null($resources)) {

                                        $pageData = array();
                                        foreach ($resources as $row) {

                                                $pageData[] = array(
                                                    'resource:id' => $row['id'],
                                                    'resource:name' => $row['name'],
                                                    'resource:filetype' => $row['filetype'],
                                                    'resource:path' => $row['path'],
                                                    'resource:modified' => $row['modified'],
                                                    'resource:edit_link' => $editControllerPath . $row['id'],
                                                    'resource:delete_link' => $deleteControllerPath . $row['id']
                                                );
                                        }

//                                        $this->parseData['displayImages'] = false;
                                        $this->parseData['displayFileType'] = true;
                                        $this->parseData['resource:type'] = $tabId;
                                        $this->parseData['resource:row'] = $pageData;
                                        $this->parseData['pagination'] = $paginationLink;

                                        // $mainHtml = $this->parser->parse("$module/$method/$tabId", $this->parseData, true);
                                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                                } else {
                                        $mainHtml = null;
                                }

                                break;


                        default:

                                show_404();
                                break;
                }

                $mainHtml = !is_null($mainHtml) ? $mainHtml : "<p>No resources found</p>";

                $this->load->library('admin/admin_views');
                $tabListHtml = $this->admin_views->get_main_tab_list($this->parseData, $selectedTabLink, $tabListData);
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml, $tabId, $tabListHtml);
        }

        public function edit($resourceId = null) {

                $pageName = "Edit Resource";
                $viewFile = "resource/edit/index";
                $mainHtml = null;


                if (is_null($resourceId)) {

                        // populate pagelist,
                        $mainHtml = "<p>Please select a resource to edit.</p>";
                } else {

                        $this->load->model('resource/resource_edit_model');
                        $resourceDetails = $this->resource_edit_model->get_resource_details($resourceId);

                        if (!is_null($resourceDetails) AND is_array($resourceDetails)) {

                                $this->load->helper('file');

                                $validFileTypes = explode("|", TEXT_FILE_EXTENSION_LIST);

                                $validMimeType = 'text';
                                $mimeType = get_mime_by_extension($resourceDetails['name']); // $resourceDetails['name']
                                $difference = substr_compare($mimeType, $validMimeType, 0, strlen($validMimeType), true); // true --> caseinsensitive
                                // Check if mime is of type 'text'
                                if (in_array(strtolower($resourceDetails['filetype']), $validFileTypes) OR $difference == 0) { // note : different = 0 --> match found

                                        // mime is of type 'text' display data for editing

                                        $fileData = read_file($resourceDetails['relative_path']);

                                        $this->parseData['resource:id'] = $resourceDetails['id'];
                                        $this->parseData['resource:name'] = $resourceDetails['name'];
                                        $this->parseData['resource:code'] = $fileData;
                                        $this->parseData['resource:url'] = $resourceDetails['uri'];
                                        $this->parseData['resource:path'] = $resourceDetails['relative_path'];

                                        $this->parseData['resource:ajax_update_url'] = site_url() . 'admin/resource/edit_do/' . $resourceDetails['id'];

                                        // NOT SURE WHAT VALUES COME HERE
                                        $this->parseData['resource:upload_path'] = $someValueHere;

                                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                                } else {

                                        // invalid mime type selected
                                        $mainHtml = "<p>The selected resource cannot be edited</p>";
                                }
                        } else {

                                $mainHtml = "<p>The selected resource is not available</p>";
                        }
                }

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        /*         * ********************************* Action Controllers ************************************************************** */

        public function delete($resourceIds = array()) {

                $this->load->model('resource/resource_manage_model');
                $affectedRows = $this->resource_manage_model->delete_resources($resourceIds);

                if ($affectedRows > 0) {

                        echo "<p>$affectedRows resource(s) successfully deleted</p>";
                } else {

                        echo '<p>resource(s) not deleted</p>';
                }
        }

        public function edit_do($resourceId = null) {

                $output = null;

                if (!is_null($resourceId)) {

                        $fileName = $this->input->post('resource_name');
                        $filePath = $this->input->post('resource_path');
                        $fileCode = $this->input->post('resource_code');

                        $this->load->helper('file');

                        if (!write_file($filePath, $fileCode)) {

                                // unable to write to file
                                $output = "<p>Unable to write to file</p>";
                        } else {

                                $output = "<p>Resource successfully updated</p>";
                        }


//                        $resourceDB = array(
//                                'resource_id' => $resourceId,
//                                'resource_name' => $resourceId,
//                                'resource_modified' => $resourceId
//                        );
//                        $this->load->model('resource/resource_edit_model');
//
//                        if ( $this->resource_edit_model->update_resource( $resourceId ) ){
//
//                                // success
//                                $output = "<p>Resource successfully updated</p>";
//
//                        }
//                        else{
//
//                                $output = "<p>Unable to edit selected resource</p>";
//
//                        }
                } else {

                        // no id provided
                        $output = "<p>No file selected</p>";
                }

                echo $output;
        }

}

?>
