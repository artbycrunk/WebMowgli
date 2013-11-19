<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of Template
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//!defined('WM_TEMPLATE_TYPE_INCLUDE') ? define('WM_TEMPLATE_TYPE_INCLUDE', 'includes') : null;
//!defined('WM_TEMPLATE_TYPE_PAGE') ? define('WM_TEMPLATE_TYPE_PAGE', 'page') : null;
//!defined('WM_TEMPLATE_TYPE_MODULE') ? define('WM_TEMPLATE_TYPE_MODULE', 'module') : null;

class Template extends Admin_Controller {

        private $module = "template";

        public function __construct() {

                parent::__construct();

                $this->config->load('site_config');

                $moduleResourceRootPath = site_url() . $this->config->item('module_resource_root_uri');
                $this->parseData['module:resource'] = "$moduleResourceRootPath/" . strtolower(__CLASS__);

                // load libraries
                $this->load->library('template_lib');
                $this->load->library('pagination');
                $this->load->library('form_validation');
                // $this->form_validation->CI = & $this;;           // required for form validation to work with hmvc
                $this->form_validation->set_error_delimiters('', '');   // remove <p> and </p> for all validation errors

		// load models
                $this->load->model('template/template_model');
		$this->load->model('template/template_manage_model');

                // default message at top of all forms
                $this->parseData['default-message'] = '';
        }

        private function _DEFAULT_SAMPLE_PAGE_WITH_TABS($tabId = null) {

                /*
                 * RULES:
                 *
                 * - create array mapping from each controller (i.e. each page)
                 * - get specific template as parameter passed
                 * - call admin {main} template. ( wrap tab html with main template )
                 * echo entire main template.
                 */

                $pageName = "Manage Templates";
                $pageUri = "admin/" . strtolower(__CLASS__) . "/" . strtolower(__FUNCTION__);
                $tabId = is_null($tabId) ? 'tab1' : $tabId;   // if tab not selected --> set as first tab
                $selectedTabLink = "$pageUri/$tabId";
                $mainHtml = null;

                $tabListData = array(
                    array('tab_name' => 'Tab 1', 'tab_link' => "$pageUri/tab1"),
                    array('tab_name' => 'Tab 2', 'tab_link' => "$pageUri/tab2"),
                    array('tab_name' => 'Tab 3', 'tab_link' => "$pageUri/tab3")
                );

                switch ($tabId) {

                        case 'tab1':

                                // some processing to get content for tab data

                                $mainHtml = $this->parser->parse("template/manage/tab1", $this->parseData, true);
                                break;

                        case 'tab2':

                                // some processing to get content for tab data

                                $mainHtml = $this->parser->parse("template/manage/tab2", $this->parseData, true);
                                break;

                        case 'tab3':

                                // some processing to get content for tab data

                                $mainHtml = $this->parser->parse("template/manage/tab3", $this->parseData, true);
                                break;

                        default:

                                show_404();
                                break;
                }

                $this->load->library('admin/admin_views');

                // call this function only if tabs present
                $tabListHtml = $this->admin_views->get_main_tab_list($this->parseData, $selectedTabLink, $tabListData);
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml, $tabId, $tabListHtml);
        }

        public function manage($tempType = 'all', $pageNo = 0 ) {

                $pageName = "Manage Templates";
                $viewFile = "template/manage/index";
                $mainHtml = null;

                $offset = $this->pagination->get_offset( $pageNo, WM_PAGINATION_LIMIT  );

                $templates = $this->template_manage_model->get_templates($tempType, WM_PAGINATION_LIMIT, $offset);

                $config['base_url'] = site_url("admin/template/manage/$tempType/");
                $config['total_rows'] = $this->template_manage_model->total_row_count();

                $this->pagination->initialize( $config, $pageNo);
                $paginationLink = $this->pagination->create_links();

                if (!is_null($templates) AND is_array($templates)) {

                        $pageData = array();
                        foreach ($templates as $row) {

                                $pageData[] = array(
                                    'template:id' => $row['id'],
                                    'template:name' => $row['name'],
                                    'template:usage' => $row['count'] > 0 ? $row['count'] : '',
                                    'template:modified' => $row['modified'],
				    'template:type' => $row['type'],
				    'template:module' => $row['module'],
                                    'template:edit_link' => site_url() . "admin/template/edit/" . $row['id'],
                                    'template:delete_link' => site_url() . "admin/template/delete/" . $row['id'],
                                );
                        }

                        $this->parseData['template:filtered_table_uri'] = site_url() . 'admin/template/manage';
                        $this->parseData['template:current_filter'] = $tempType;
                        $this->parseData['template:all_filter_selected'] = strtolower($tempType) == 'all' ? "selected='selected'" : '';
                        $this->parseData['template:page_filter_selected'] = strtolower($tempType) == 'page' ? "selected='selected'" : '';
                        $this->parseData['template:inc_filter_selected'] = strtolower($tempType) == 'includes' ? "selected='selected'" : '';
                        $this->parseData['template:add_link'] = site_url() . 'admin/template/add';
                        $this->parseData['template:import_link'] = site_url() . 'admin/template/import';

                        $this->parseData['template:row'] = $pageData;
                        $this->parseData['pagination'] = $paginationLink;

                        $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                } else {

                        $mainHtml = "<p>No Templates found matching given criteria</p>";
                }

//                $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        public function add() {

                $pageName = "Add Template";
                $viewFile = "template/edit";
                $mainHtml = null;

                $this->parseData['default-message'] = 'Add a template';
                $this->parseData['action_type'] = 'add';
                $this->parseData['temp_id'] = null;

                // create drop down list for template type
                $tempTypeList = array(
                    '' => 'select type',
                    'page' => 'page',
                    WM_TEMPLATE_TYPE_INCLUDE => 'include',
                    'module' => 'module'
                );
                $attribs = " id='temp_type' ";
                $attribs .= " class='form_select_fields' ";
                $select = '';
                $this->parseData['temp:select:temp_type'] = form_dropdown('temp_type', $tempTypeList, $select, $attribs);

                // create drop down list for selecting Modules
                $modules = $this->template_lib->get_module_select_list();
                $attribs = " id='temp_module_name' ";
                $attribs .= " class='form_select_fields' ";
                $select = '';
                $this->parseData['temp:select:temp_module_name'] = form_dropdown('temp_module_name', $modules, $select, $attribs);

                $this->parseData['temp_name'] = '';
                $this->parseData['temp_head'] = '';
                $this->parseData['temp_html'] = '';

                // set visibility checkbox for form
                $attributes = array(
                    'name' => 'temp_is_visible',
                    'id' => 'temp_is_visible',
                    'value' => true,
                    'checked' => true,
                    'style' => '',
                );
                $this->parseData['temp:checkbox:temp_is_visible'] = form_checkbox($attributes);

                $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        public function edit($tempId = null) {

                $pageName = "Edit Template";
                $mainHtml = null;
                $viewFile = "template/edit";

                // override with post value ( if present )
                $tempId = isset($_POST['temp_id']) ? $this->input->post('temp_id') : $tempId;

                if (!is_null($tempId)) {


                        $tempDetails = $this->template_model->get_template_details($tempId, true); // allowHidden = true
                        // check if template exists
                        if (!is_null($tempDetails) AND is_array($tempDetails)) {

                                $moduleName = $tempDetails['module_name'];
                                $tempType = $tempDetails['type'];

                                $this->parseData['default-message'] = 'Edit template';
                                $this->parseData['action_type'] = 'edit';
                                $this->parseData['temp_id'] = $tempDetails['id'];

                                $this->parseData['temp_name'] = $tempDetails['name'];

                                // create drop down list for template type
                                $tempTypeList = array($tempType => $tempType);
                                $attribs = " id='temp_type' ";
                                $attribs .= " class='form_select_fields' ";
                                $select = $tempType;
                                $this->parseData['temp:select:temp_type'] = form_dropdown('temp_type', $tempTypeList, $select, $attribs);

                                // create drop down list for Modules
                                $modules = array($moduleName => $moduleName);
                                $attribs = " id='temp_module_name' ";
                                $attribs .= " class='form_select_fields' ";
                                $select = $moduleName;
                                $this->parseData['temp:select:temp_module_name'] = form_dropdown('temp_module_name', $modules, $select, $attribs);

                                $this->parseData['temp_head'] = htmlentities($tempDetails['head']);
                                $this->parseData['temp_html'] = htmlentities($tempDetails['html']);

                                // set visibility checkbox for form
                                $attributes = array(
                                    'name' => 'temp_is_visible',
                                    'id' => 'temp_is_visible',
                                    'value' => true,
                                    'checked' => (bool) $tempDetails['is_visible'],
                                    'style' => '',
                                );
                                $this->parseData['temp:checkbox:temp_is_visible'] = form_checkbox($attributes);

                                $mainHtml = $this->parser->parse($viewFile, $this->parseData, true);
                        } else {

                                $mainHtml = "Template does not exist";
                        }
                } else {

                        $mainHtml = "Please select a template to edit.";
                }

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $mainHtml);
        }

        public function save() {

                $this->form_validation->set_rules('temp_name', 'Name', 'required|callback__check_template_name');
                $this->form_validation->set_rules('temp_type', 'Type', 'required');
                $this->form_validation->set_rules('temp_head', 'Head', '');
                $this->form_validation->set_rules('temp_html', 'HTML', 'required');
                if ($this->input->post('temp_type') == 'module')
                        $this->form_validation->set_rules('temp_module_name', 'Module', 'required');

                /* Validations - validate form */
                if ($this->form_validation->run()) {

                        /* Validation successfull */

                        $actionType = $this->input->post('action_type');
                        $this->load->library('date_time');



                        $success = false;
                        $errorMsg = null;

                        // load import library
                        $this->load->library('import/import_site_lib');

                        $this->load->model('template/template_model');
                        $db = & $this->template_model->get_db();

                        $this->load->model('import/import_model');
                        //$this->import_model->set_db(& $db);
                        $this->import_model->set_db($db);

                        $tempType = $this->input->post('temp_type');

                        switch ($actionType) {

                                case 'add':

                                        $tempDb = array(
                                            'temp_name' => $this->input->post('temp_name'),
                                            'temp_type' => $this->input->post('temp_type'),
                                            'temp_module_name' => $this->input->post('temp_module_name'),
                                            'temp_head' => $this->input->post('temp_head'),
                                            'temp_html' => $this->input->post('temp_html'),
                                            'temp_is_visible' => $this->input->post('temp_is_visible'),
                                            'temp_created' => $this->date_time->now(),
                                            'temp_modified' => $this->date_time->now()
                                        );


                                        $this->template_model->transaction_strict(true);



                                        switch ($tempType) {
                                                case WM_TEMPLATE_TYPE_PAGE:

                                                        unset($tempDb['temp_head']);
                                                        unset($tempDb['temp_module_name']);

                                                        $isCreatePage = false;
                                                        $success = $this->import_site_lib->import($tempDb, $tempDb['temp_html'], $db, $isCreatePage);
                                                        $errorMsg = "Some error occured while adding template";

                                                        break;

                                                case WM_TEMPLATE_TYPE_INCLUDE:

                                                        unset($tempDb['temp_head']);
                                                        unset($tempDb['temp_module_name']);

                                                        $html = $tempDb['temp_html'];
                                                        $includeId = $this->import_model->create_template($tempDb);
                                                        $isCreatePage = false;
                                                        //$this->import_site_lib->extract_content(& $html, & $db, $includeId, $isCreatePage);
                                                        $this->import_site_lib->extract_content($html, $db, $includeId, $isCreatePage);

                                                        $tempEditDb = array(
                                                            'temp_html' => $html,
                                                            'temp_modified' => $this->date_time->now()
                                                        );

                                                        $success = $this->import_model->edit_template_by_id($tempEditDb, $includeId);

                                                        $errorMsg = "Some error occured while adding include";

                                                        break;

                                                case WM_TEMPLATE_TYPE_MODULE:

                                                        $tempId = $this->import_model->create_template($tempDb);
                                                        $success = (bool) $tempId;

                                                        break;


                                                default:

                                                        $success = false;
                                                        $errorMsg = "Invalid template type selected";
                                                        break;
                                        } // end  switch($tempType)





                                        break;

                                case 'edit';

                                        $tempId = $this->input->post('temp_id');

                                        $tempDb = array(
                                            'temp_name' => $this->input->post('temp_name'),
                                            'temp_type' => $this->input->post('temp_type'),
                                            'temp_module_name' => $this->input->post('temp_module_name'),
                                            'temp_head' => $this->input->post('temp_head'),
                                            'temp_html' => $this->input->post('temp_html'),
                                            'temp_is_visible' => $this->input->post('temp_is_visible'),
                                            'temp_created' => $this->date_time->now(),
                                            'temp_modified' => $this->date_time->now()
                                        );


                                        $this->template_model->transaction_strict(true);



                                        switch ($tempType) {
                                                case WM_TEMPLATE_TYPE_PAGE:

                                                        unset($tempDb['temp_head']);
                                                        unset($tempDb['temp_module_name']);

                                                        $htmlMain = $tempDb['temp_html'];
                                                        $isCreatePage = false;

														//$success = $this->import_site_lib->process_page_template($tempDb, & $htmlMain, & $db, $tempId, $isCreatePage);
                                                        $success = $this->import_site_lib->process_page_template($tempDb, $htmlMain, $db, $tempId, $isCreatePage);
                                                        $errorMsg = "CODE PENDING for template type = page";

                                                        // do nothing here, for NOW
                                                        break;

                                                case WM_TEMPLATE_TYPE_INCLUDE:

                                                        unset($tempDb['temp_head']);
                                                        unset($tempDb['temp_module_name']);


                                                        $html = $tempDb['temp_html'];
                                                        $includeId = $tempId;
                                                        $isCreatePage = false;

                                                        //$success = $this->import_site_lib->process_include(& $html, & $db, $includeId, $isCreatePage);
														$success = $this->import_site_lib->process_include( $html, $db, $includeId, $isCreatePage);

                                                        $errorMsg = "Some error occured while editing include";

                                                        break;

                                                case WM_TEMPLATE_TYPE_MODULE:

                                                        $tempDb = array(
                                                            'temp_name' => $this->input->post('temp_name'),
                                                            'temp_type' => $this->input->post('temp_type'),
                                                            // 'temp_module_name' => $this->input->post('temp_module_name'),
                                                            'temp_head' => $this->input->post('temp_head'),
                                                            'temp_html' => $this->input->post('temp_html'),
                                                            'temp_is_visible' => $this->input->post('temp_is_visible'),
                                                            'temp_modified' => $this->date_time->now()
                                                        );

                                                        $tempId = $this->input->post('temp_id');
                                                        $success = $this->import_model->edit_template_by_id($tempDb, $tempId);
                                                        $errorMsg = "Unable to save template";

                                                        break;


                                                default:

                                                        $success = false;
                                                        $errorMsg = "Invalid template type selected";
                                                        break;
                                        } // end  switch($tempType)


                                        /*                                         * **************** OLD ********************** */


                                        break;

                                default:
                                        // should never reach here, unless add / edit is changed
                                        break;
                        }

                        // check success status and commit/rollback accordingly, also set output message
                        if ($success) {
                                $this->template_model->transaction_commit();
                                $this->form_response->set_message(WM_STATUS_SUCCESS, 'Template successfully saved');
                        } else {
                                $this->template_model->transaction_rollback();

                                $errorMsg = is_null($errorMsg) ? "Unable to save template" : $errorMsg;
                                $this->form_response->set_message(WM_STATUS_ERROR, $errorMsg);
                        }
                } else {
                        // form validation fail

                        $this->form_response
                                ->set_message( WM_STATUS_ERROR, "Invalid form fields")
                                ->set_redirect(null)
                                ->add_validation_msg("temp_name", form_error("temp_name"))
                                ->add_validation_msg("temp_type", form_error("temp_type"))
                                ->add_validation_msg("temp_module_name", form_error("temp_module_name"))
                                ->add_validation_msg("temp_head", form_error("temp_head"))
                                ->add_validation_msg("temp_html", form_error("temp_html"));
                }

                $this->form_response->send();
        }

        /*         * ********************************* Action Controllers ************************************************************** */

        public function delete($tempIds = array()) {

                $this->load->model('template/template_manage_model');
                $affectedRows = $this->template_manage_model->delete_templates($tempIds);

                if ($affectedRows > 0) {

                        echo "<p>$affectedRows Template(s) successfully deleted</p>";
                } else {

                        echo '<p>Template(s) not deleted</p>';
                }
        }

        /*         * *********************** Form Validation function ********************************** */

        public function _check_template_name() {

                $actionType = $this->input->post('action_type');
                $tempName = $this->input->post('temp_name');
                $tempType = $this->input->post('temp_type');
                $tempModule = $this->input->post('temp_module_name');

                $success = false;

                $moduleName = null;

                // check for clash in template name, ONLY for add type queries.
                if ($actionType == 'add') {

                        if ($tempType == 'page' OR $tempType == WM_TEMPLATE_TYPE_INCLUDE) {

                                $tempModule = null;
                        } elseif ($tempType == 'module') {

                                // dummy check
                        }

                        // returns true if name DOES NOT EXIST
                        // for type = module, also adds module_name in where condition.
                        $success = $this->template_model->check_valid_template_name($tempType, $tempName, $tempModule);

                        if (!$success) {
                                $this->form_validation->set_message('_check_template_name', "The template name already exists for the selected template type ( $tempType ) or module");
                        }
                } else {
                        $success = true;
                }

                return $success;
        }

}

?>
