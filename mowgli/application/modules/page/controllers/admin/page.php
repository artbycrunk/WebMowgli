<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Module_Import
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//
//require_once APPPATH.'libraries/pagebean.php';
//
//define('VIEW_METHOD', 'view');

class Page extends Admin_Controller {

        public function __construct() {
                parent::__construct();

                $this->config->load('site_config');

                // load libraries
                $this->load->library('date_time');
                $this->load->library('pagination');
                $this->load->library('form_response');
                $this->load->library('form_validation');
                // $this->form_validation->CI = & $this;;           // required for form validation to work with hmvc
                // load helpers
                $this->load->helper('admin_helper');

                // load models
                $this->load->model('page/page_model');
                $this->load->model('template/template_model');

                // Define Constants
                !defined('PAGE_ACTION_ADD') ? define('PAGE_ACTION_ADD', "add") : null;
                !defined('PAGE_ACTION_EDIT') ? define('PAGE_ACTION_EDIT', "edit") : null;

                $moduleResourceRootPath = site_url($this->config->item('module_resource_root_uri'));
                $this->parseData['module:resource'] = "$moduleResourceRootPath/" . strtolower(__CLASS__);
        }

        public function manage_BACKUP() {

                $pageName = "Manage Pages";
                $viewFile = "page/manage/index";
                $mainHtml = null;

                $this->load->model('page/page_manage_model');
                $pages = $this->page_model->get_pages_meta_data();
//                $pages = $this->page_manage_model->get_pages();

                if (!is_null($pages) AND is_array($pages)) {

                        $pageData = array();
                        foreach ($pages as $row) {

                                $pageData[] = array(
                                    'page:id' => $row['id'],
                                    'page:name' => $row['name'],
                                    'page:slug' => $row['slug'],
                                    'page:uri' => site_url() . $row['slug'],
                                    'page:edit_link' => site_url() . 'admin/page/edit/' . $row['id'],
                                    'page:delete_link' => site_url() . 'admin/page/delete/' . $row['id'],
                                    'template:edit_link' => site_url() . 'admin/template/edit/' . $row['temp_id'],
                                    'page:publish_toggle' => site_url() . 'admin/page/publish_toggle/' . $row['id'],
                                    'template:name' => $row['template'],
                                    'page:modified' => $row['modified'],
                                    'page:published_title' => ( $row['is_visible'] == '1' ? 'Unpublish' : 'Publish' ),
                                    'page:published_status' => ( $row['is_visible'] == '1' ? 'published' : 'unpublished' ),
                                    'page:not_published' => ( $row['is_visible'] == '0' ? " - (draft)" : '' )
                                );
                        }

                        $this->parseData['page:row'] = $pageData;
                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                } else {

                        $mainHtml = "<p>No pages have been created yet</p>";
                }

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        public function manage($pageNo = 0) {

                $pageName = "Manage Pages";
                $viewFile = "page/manage/index";
                $mainHtml = null;

                $offset = $this->pagination->get_offset($pageNo, WM_PAGINATION_LIMIT);

                $this->load->model('page/page_manage_model');
                $pages = $this->page_model->get_pages_meta_data(null, WM_PAGINATION_LIMIT, $offset);
//                $pages = $this->page_manage_model->get_pages();

                $config['base_url'] = site_url("admin/page/manage/");
                $config['total_rows'] = $this->page_model->total_row_count();

                $this->pagination->initialize($config, $pageNo);
                $paginationLink = $this->pagination->create_links();

                if (!is_null($pages) AND is_array($pages)) {

                        $pageData = array();
                        foreach ($pages as $row) {

                                $pageData[] = array(
                                    'page:id' => $row['id'],
                                    'page:name' => $row['name'],
                                    'page:slug' => $row['slug'],
                                    'page:uri' => site_url() . $row['slug'],
                                    'page:edit_link' => site_url() . 'admin/page/edit/' . $row['id'],
                                    'page:delete_link' => site_url() . 'admin/page/delete/' . $row['id'],
                                    'template:edit_link' => site_url() . 'admin/template/edit/' . $row['temp_id'],
                                    'page:publish_toggle' => site_url() . 'admin/page/publish_toggle/' . $row['id'],
                                    'template:name' => $row['template'],
                                    'page:modified' => $row['modified'],
                                    'page:published_title' => ( $row['is_visible'] == '1' ? 'Unpublish' : 'Publish' ),
                                    'page:published_status' => ( $row['is_visible'] == '1' ? 'published' : 'unpublished' ),
                                    'page:not_published' => ( $row['is_visible'] == '0' ? " - (draft)" : '' )
                                );
                        }

                        $this->parseData['pagination'] = $paginationLink;
                        $this->parseData['page:row'] = $pageData;
                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                } else {

                        $mainHtml = "<p>No pages have been created yet</p>";
                }

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        public function edit($pageId = null) {

                $title = "Edit Page";
                $mainHtml = null;
                $viewFile = "page/page_edit.php";

                $this->parseData['default-message'] = "Edit Page";
                $this->parseData['action_type'] = PAGE_ACTION_EDIT;

                $pageDetails = $this->page_model->get_pages_meta_data($pageId);

                if (is_null($pageId)) {

                        // populate pagelist,
                        $mainHtml = "<p>Please select a page to edit.</p>";
                } elseif (is_null($pageDetails)) {

                        $mainHtml = "<p>The page you have selected for editing is not present in the database.</p>";
                } elseif (is_array($pageDetails)) {

                        $this->parseData['page_id'] = $pageDetails['id'];
                        $this->parseData['page_name'] = $pageDetails['name'];
                        $this->parseData['page_slug'] = $pageDetails['slug'];
                        $this->parseData['page_redirect'] = $pageDetails['redirect'];
                        $this->parseData['page_title'] = $pageDetails['title'];
                        $this->parseData['page_description'] = $pageDetails['description'];
                        $this->parseData['page_keywords'] = $pageDetails['keywords'];

                        // create drop down list for Template Selection
                        $templateList = array(
                            $pageDetails['temp_id'] => $pageDetails['template']
                        );
//                        $templateDetails = $this->template_model->get_templates_list_by_group(WM_TEMPLATE_TYPE_PAGE);
//                        foreach ($templateDetails as $temp) {
//                                $templateList[$temp['id']] = $temp['name'];
//                        }
                        $attribs = " id='page_temp_id' ";
                        $attribs .= " class='form_select_fields' ";
                        $select = $pageDetails['temp_id'];
                        $this->parseData['page:select:page_temp_id'] = form_dropdown('page_temp_id', $templateList, $select, $attribs);

                        // set Page visibility ( page_is_visible )
                        $attributes = array(
                            'name' => 'page_is_visible',
                            'id' => 'page_is_visible',
                            'value' => true,
                            'checked' => (bool) $pageDetails['is_visible'],
                            'style' => '',
                        );
                        $this->parseData['page:checkbox:page_is_visible'] = form_checkbox($attributes);

                        $output = $this->parser->parse($viewFile, $this->parseData, true);
                } else {
                        $mainHtml = "<p>Some error occured while retrieving page data from database.";
                }

                echo get_admin_main_content($this->parseData, $title, $output);
        }

        public function add() {

                $title = "Add Page";
                $viewFile = "page/page_edit.php";

                $this->parseData['default-message'] = "Add Page";
                $this->parseData['action_type'] = PAGE_ACTION_ADD;

                $this->parseData['page_id'] = "";
                $this->parseData['page_name'] = "";
                $this->parseData['page_slug'] = "";
                $this->parseData['page_redirect'] = "";
                $this->parseData['page_title'] = "";
                $this->parseData['page_description'] = "";
                $this->parseData['page_keywords'] = "";

                // create drop down list for Template Selection
                $templateList = array();
                $templateDetails = $this->template_model->get_templates_list_by_group(WM_TEMPLATE_TYPE_PAGE);

                if (!is_null($templateDetails) AND count($templateDetails) > 0) {

                        foreach ($templateDetails as $temp) {
                                $templateList[$temp['id']] = $temp['name'];
                        }
                        $attribs = " id='page_temp_id' ";
                        $attribs .= " class='form_select_fields' ";
                        $select = '';
                        $this->parseData['page:select:page_temp_id'] = form_dropdown('page_temp_id', $templateList, $select, $attribs);

                        // set Page visibility ( page_is_visible )
                        $attributes = array(
                            'name' => 'page_is_visible',
                            'id' => 'page_is_visible',
                            'value' => true,
                            'checked' => true,
                            'style' => '',
                        );
                        $this->parseData['page:checkbox:page_is_visible'] = form_checkbox($attributes);

                        $output = $this->parser->parse($viewFile, $this->parseData, true);
                } else {

                        // no templates exist, do not allow page add
                        $output = "<p>No templates added yet</p>";
                }

                echo get_admin_main_content($this->parseData, $title, $output);
        }

        public function save_page() {

                // get post values
                $actionType = $this->input->post('action_type');
                $pageId = $this->input->post('page_id');
                $tempId = $this->input->post('page_temp_id');

                $this->form_validation->set_rules('page_name', 'Name', 'required');
                $this->form_validation->set_rules('page_slug', 'Uri', "required|alpha_dash|callback__check_slug_unique[$pageId]");
                $this->form_validation->set_rules('page_temp_id', 'Template', 'required');
                $this->form_validation->set_rules('page_title', 'Title', '');
                $this->form_validation->set_rules('page_description', 'Description', '');
                $this->form_validation->set_rules('page_keywords', 'Keywords', '');

                /* Validations - validate form */
                if ($this->form_validation->run()) {

                        /* Validation successfull */

                        $pageDb = array(
                            //"page_id" => null,
                            "page_temp_id" => $tempId,
                            "page_name" => $this->input->post('page_name'),
                            "page_slug" => $this->input->post('page_slug'),
                            //"page_redirect" => $this->input->post('page_redirect'),
                            //"page_html" => $this->input->post('page_html'),
                            "page_title" => $this->input->post('page_title'),
                            "page_description" => $this->input->post('page_description'),
                            "page_keywords" => $this->input->post('page_keywords'),
                            "page_created" => $this->date_time->now(),
                            "page_modified" => $this->date_time->now(),
                            "page_is_visible" => (bool) $this->input->post('page_is_visible'),
                        );

                        $success = false;
                        $error = null;

                        $db = & $this->page_model->get_db();

                        $this->page_model->transaction_strict(true);
                        $this->page_model->transaction_begin();

                        switch ($actionType) {
                                case PAGE_ACTION_ADD:

                                        // insert new page and create page blocks from template
                                        $success = $this->page_model->add_page_from_template($pageDb, $tempId);

                                        $successMsg = "Page successfully created";
                                        $error = "Unable to add page";
                                        break;

                                case PAGE_ACTION_EDIT:

                                        // remove unnecessary fields from db list
                                        if (isset($pageDb['page_created'])) {
                                                unset($pageDb['page_created']);
                                        }
                                        if (isset($pageDb['page_temp_id'])) {
                                                unset($pageDb['page_temp_id']);
                                        }


                                        $success = $this->page_model->edit_page_meta($pageId, $pageDb);

                                        $successMsg = "Page successfully updated";
                                        $error = "Unable to edit page";
                                        break;

                                default:
                                        $success = false;
                                        $error = "Invalid action type";
                                        break;
                        }

                        if ($success) {

//                                echo "success";
                                $this->page_model->transaction_commit();
                                $this->form_response->set_message(WM_STATUS_SUCCESS, $successMsg);
                        } else {

//                                echo "fail";
                                $this->page_model->transaction_rollback();
                                $this->form_response->set_message(WM_STATUS_ERROR, $error);
                        }
                } else {
                        // form validation fail

                        $this->form_response
                                ->set_message(WM_STATUS_ERROR, "Invalid form fields")
                                ->set_redirect(null)
                                ->add_validation_msg("page_name", form_error("page_name"))
                                ->add_validation_msg("page_slug", form_error("page_slug"))
                                ->add_validation_msg("page_temp_id", form_error("page_temp_id"))
                                ->add_validation_msg("page_title", form_error("page_title"))
                                ->add_validation_msg("page_description", form_error("page_description"))
                                ->add_validation_msg("page_keywords", form_error("page_keywords"))
                                ->add_validation_msg("page_is_visible", form_error("page_is_visible"));


//                        echo "invalid form fields";
                }

                $this->form_response->send();
        }

        /*         * *********************  Actions controllers ***************************************************** */

        public function publish_toggle($pageId = null) {

                $pageIds = $this->input->post('page_list');

                // if parameter provided --> update single page else check post values
                // if no post values also --> check for
                if (!is_null($pageId)) {
                        // action for single element
                        $pageIds = array($pageId);
                } elseif ($pageIds === false) {
                        // POST values NOt set -- error
                        $pageIds = array("null");
                        echo "<p>Please select atleast one page to Publish/Unpublish </p>";
                        return;
                }

                $this->load->model('page/page_manage_model');
                $affectedRows = $this->page_manage_model->publish_toggle($pageIds);

                if ($affectedRows > 0) {

                        echo "<p>$affectedRows Page(s) successfully updated</p>";
                } else {

                        echo '<p>Page(s) not updated</p>';
                }
        }

        public function delete($pageIds = array()) {

                $this->load->model('page/page_manage_model');
                $affectedRows = $this->page_manage_model->delete_pages($pageIds);
                if ($affectedRows > 0) {

                        echo "<p>$affectedRows page(s) successfully deleted</p>";
                } else {

                        echo '<p>Page(s) not deleted</p>';
                }
        }

        // ------------------------------ Private functions -------------------------

        /**
         * Form validation functin to check if a slug is NOT already used in the database for pages
         * Logic:
         * if excludePageId is provided
         *      --> will exclude the current page from search ( this is required in case of edit mode to exclude self )
         *
         * @param string $slug
         * @param string $excludePageId if provided will exclude this id from the search
         *
         * @return bool
         */
        public function _check_slug_unique($slug, $excludePageId = null) {

                $success = true;

                // reset pageId to null if found to be empty string
                $excludePageId = ( $excludePageId == '' ) ? null : $excludePageId;

                // if slug exists --> success = false, else true
                if (!$this->page_model->check_is_page_slug_unique($slug, $excludePageId)) {

                        $this->form_validation->set_message('_check_slug_unique', 'This slug has already been used, please select a unique slug');

                        $success = false;
                }

                return $success;
        }

        public function edit_meta($pageId = null) {

                // check if user is logged in
                $this->load->module('user')->authorize(current_url());


                $pageData = $this->page_model->get_pages_meta_data($pageId);
//                $pageData = $this->page_model->get_page_meta($pageId);
//                $pageData = $this->page_edit_model->get_page_details($pageId);    // DEPRICATED

                $this->parseData['page:id'] = $pageData['id'];
                $this->parseData['page:title'] = $pageData['title'];
                $this->parseData['page:keywords'] = $pageData['keywords'];
                $this->parseData['page:description'] = $pageData['description'];


                $output = null;
                if (!is_null($pageData)) {
                        $this->parseData['default-message'] = '';
                        $this->parseData['admin:includes:head'] = $this->parser->parse('admin/includes/head', $this->parseData, true);
			$this->parseData['admin:includes:scripts'] = $this->parser->parse('admin/includes/scripts', $this->parseData, true);
                        $output = $this->parser->parse('page/admin/page_metadata', $this->parseData, true);
                } else {

                        $output = "page not found";
                }


                echo $output;
        }

        public function edit_meta_do() {

                // check if user is authorized, redirect to login page if not.
                $this->load->module('user')->authorize(current_url());

                $this->form_validation->set_rules('page_title', 'Home Page', 'max_length[250]');
                $this->form_validation->set_rules('page_keywords', 'Home Page', 'max_length[250]');
                $this->form_validation->set_rules('page_desc', 'Home Page', 'max_length[250]');

                /* Validations - validate form */
                if ($this->form_validation->run()) {

                        /* Validation successfull */

                        $id = $this->input->post('page_id');
                        $title = $this->input->post('page_title');
                        $meta = $this->input->post('page_keywords');
                        $description = $this->input->post('page_desc');

                        $data = array(
                            'page_title' => $title,
                            'page_description' => $description,
                            'page_keywords' => $meta
                        );

                        if ($this->page_model->edit_page_meta($id, $data)) {

                                $this->form_response->set_message(WM_STATUS_SUCCESS, "changes saved");
                        } else {

                                $this->form_response->set_message(WM_STATUS_ERROR, "unable to save changes");
                        }
                } else {
                        // form validation fail

                        $this->form_response
                                ->set_message(WM_STATUS_ERROR, "Invalid form values")
                                ->set_redirect(null)
                                ->add_validation_msg("page_title", form_error("page_title"))
                                ->add_validation_msg("page_keywords", form_error("page_keywords"))
                                ->add_validation_msg("page_desc", form_error("page_desc"));
                }

                $this->form_response->send();
        }

}

?>
