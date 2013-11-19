<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of setting
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
!defined("SETTING_CATEG_KEYWORD") ? define("SETTING_CATEG_KEYWORD", "setting_categ") : null;
!defined("SETTING_SAVE_SUCCESS") ? define("SETTING_SAVE_SUCCESS", "Settings successfully saved") : null;
!defined("SETTING_SAVE_FAIL") ? define("SETTING_SAVE_FAIL", "Unable to save settings") : null;
!defined("SETTING_SAVE_FORM_INVALID") ? define("SETTING_SAVE_FORM_INVALID", "Form fields have invalid values, settings not saved") : null;

class Settings extends Admin_Controller {

	private $configFile = "settings_config";
	private $module = "settings";

	public function __construct() {

		parent::__construct();

		$this->load->library('site_settings');
		$this->load->library('json_response');
		$this->load->library('form_response');
		$this->load->library('form_validation');
		// $this->form_validation->CI = & $this;;    // required for form validation to work with hmvc

		$this->load->helper('admin');

		$this->parseData['default-message'] = "";
	}

	/* display view to setting site */

	public function general() {

		$pageName = "General Settings";
		$category = 'general';
		$viewfile = "settings/general_settings";

		// get settings data from database
		$settings = $this->site_settings->get_data($category);

		$this->_set_form_parse_tags($category, $settings);

		$tabHtml = $this->parser->parse($viewfile, $this->parseData, true);

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $tabHtml);
	}

	public function general_save() {

		$category = $this->input->post(SETTING_CATEG_KEYWORD);
		$settings = array(
		    "site_name" => $this->input->post('site_name'),
		    "home_page" => $this->input->post('home_page'),
		    "page_404" => $this->input->post('page_404'),
		    "analytics" => $this->input->post('analytics'),
		    "enable_comments" => (bool) $this->input->post('enable_comments'),
		    "comments_username" => $this->input->post('comments_username')
		);

		$this->form_validation->set_rules('site_name', 'Site Name', '');
		$this->form_validation->set_rules('home_page', 'Home Page', 'required');
		$this->form_validation->set_rules('page_404', '404 Page', 'required');
		$this->form_validation->set_rules('analytics', 'Analytics Code', '');
		$this->form_validation->set_rules('enable_comments', 'Enable Comments', '');
		if ($settings['enable_comments']) {
			// validate comment username ONLY if enable_comments = true
			$this->form_validation->set_rules('comments_username', 'Comments Username', 'required');
		}

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			if ($this->site_settings->set_by_category($category, $settings)) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, SETTING_SAVE_SUCCESS);
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, SETTING_SAVE_FAIL);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, SETTING_SAVE_FORM_INVALID)
				->set_redirect(null)
				->add_validation_msg("site_name", form_error("site_name"))
				->add_validation_msg("home_page", form_error("home_page"))
				->add_validation_msg("page_404", form_error("page_404"))
				->add_validation_msg("analytics", form_error("analytics"))
				->add_validation_msg("enable_comments", form_error("enable_comments"))
				->add_validation_msg("comments_username", form_error("comments_username"));
		}

		$this->form_response->send();
	}

	public function email() {

		$pageName = "Email Settings";
		$category = 'email';
		$viewfile = "settings/email_settings";

		// get settings data from database
		$settings = $this->site_settings->get_data($category);

		$this->_set_form_parse_tags($category, $settings);

		$tabHtml = $this->parser->parse($viewfile, $this->parseData, true);

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $tabHtml);
	}

	public function email_save() {

		$category = $this->input->post(SETTING_CATEG_KEYWORD);

		$settings = null;

		$this->form_validation->set_rules('from_name', 'From Name', 'required');
//                $this->form_validation->set_rules( 'from_email', 'From Email', 'required|valid_email' );
		$this->form_validation->set_rules('contact_email', 'Contact Email', 'required|valid_email');
		$this->form_validation->set_rules('server_email', 'Server Email', 'required|valid_email');
		$this->form_validation->set_rules('bcc', 'Bcc', 'valid_emails');

		$settings = array(
		    "from_name" => $this->input->post('from_name'),
//                        "from_email" => $this->input->post( 'from_email' ),
		    "contact_email" => $this->input->post('contact_email'),
		    "server_email" => $this->input->post('server_email'),
		    "bcc" => $this->input->post('bcc'),
		    "smtp" => $this->input->post('smtp'), // note: value needs to be set as true always while loading form
//                        "smtp_username" => $this->input->post( 'smtp_username' ),
//                        "smtp_password" => $this->input->post( 'smtp_password' ),
//                        "smtp_host" => $this->input->post( 'smtp_host' ),
//                        "smtp_port" => $this->input->post( 'smtp_port' ),
//                        "smtp_charset" => $this->input->post( 'smtp_charset' )
		);

		// perform validations and save settings value for SMTP only if SMTP is selected
		if ($this->input->post('smtp')) {

			// form validations
//                $this->form_validation->set_rules( 'smtp', 'SMTP', '' );
			$this->form_validation->set_rules('smtp_username', 'SMTP Username', 'required');
			$this->form_validation->set_rules('smtp_password', 'SMTP Password', '');
			$this->form_validation->set_rules('smtp_host', 'SMTP Host', 'required');
			$this->form_validation->set_rules('smtp_port', 'SMTP Port', 'integer');
			$this->form_validation->set_rules('smtp_charset', 'SMTP Charset', '');

//                        $settings["smtp"] = $this->input->post( 'smtp' ); // note: value needs to be set as true always while loading form
			$settings["smtp_username"] = $this->input->post('smtp_username');
			$settings["smtp_password"] = $this->input->post('smtp_password');
			$settings["smtp_host"] = $this->input->post('smtp_host');
			$settings["smtp_port"] = $this->input->post('smtp_port');
			$settings["smtp_charset"] = $this->input->post('smtp_charset');
		}



		/* Validations - validate form */
		if ($this->form_validation->run()) {

			// form success

			if ($this->site_settings->set_by_category($category, $settings)) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, SETTING_SAVE_SUCCESS);
			} else {
				$this->form_response->set_message(WM_STATUS_SUCCESS, SETTING_SAVE_FAIL);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, SETTING_SAVE_FORM_INVALID)
				->set_redirect(null)
				->add_validation_msg("from_name", form_error("from_name"))
//                                ->add_validation_msg( "from_email", form_error( "from_email" ) )
				->add_validation_msg("contact_email", form_error("contact_email"))
				->add_validation_msg("server_email", form_error("server_email"))
				->add_validation_msg("bcc", form_error("bcc"))
				->add_validation_msg("smtp", form_error("smtp"))
				->add_validation_msg("smtp_username", form_error("smtp_username"))
				->add_validation_msg("smtp_password", form_error("smtp_password"))
				->add_validation_msg("smtp_host", form_error("smtp_host"))
				->add_validation_msg("smtp_port", form_error("smtp_port"))
				->add_validation_msg("smtp_charset", form_error("smtp_charset"));
		}

		$this->form_response->send();
	}

	/* display view to setting site */

	public function datetime() {

		$pageName = "Date & Time Settings";
		$category = 'datetime';
		$viewfile = "settings/datetime_settings";

		// get settings data from database
		$settings = $this->site_settings->get_data($category);

		$this->_set_form_parse_tags($category, $settings);

		$timezone = $this->site_settings->get($category, "timezone");

		// overriding timezone parse tags
		$this->parseData['set:select:timezone'] = timezone_menu($timezone, 'form_select_fields', 'timezone');
//                $this->parseData['set:description:timezone'] = "This is a description for timezone";

		$tabHtml = $this->parser->parse($viewfile, $this->parseData, true);

		$this->load->library('admin/admin_views');
		echo $this->admin_views->get_main_content($this->parseData, $pageName, $tabHtml);
	}

	public function datetime_save() {

		$category = $this->input->post(SETTING_CATEG_KEYWORD);
		$settings = array(
		    "timezone" => $this->input->post('timezone'),
		    "format_date" => $this->input->post('format_date'),
		    "format_time" => $this->input->post('format_time'),
		    "dst_used" => $this->input->post('dst_used')
//                        "dst_offset" => $this->input->post( 'dst_offset' ) // will be set later, only if dst_used = true
		);

		$this->form_validation->set_rules('timezone', 'Timezone', '');
		$this->form_validation->set_rules('format_date', 'Date Format', 'required');
		$this->form_validation->set_rules('format_time', 'Time Format', 'required');

		// if dst_used = true --> set post values and rules for dst_offset
		if ($this->input->post('dst_used')) {

			$settings["dst_offset"] = $this->input->post('dst_offset');
			$this->form_validation->set_rules('dst_offset', 'DST Offset', '');
		}

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			if ($this->site_settings->set_by_category($category, $settings)) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, SETTING_SAVE_SUCCESS);
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, SETTING_SAVE_FAIL);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, SETTING_SAVE_FORM_INVALID)
				->set_redirect(null)
				->add_validation_msg("timezone", form_error("site_name"))
				->add_validation_msg("format_date", form_error("format_date"))
				->add_validation_msg("format_time", form_error("format_time"))
				->add_validation_msg("dst_used", form_error("dst_used"))
				->add_validation_msg("dst_offset", form_error("dst_offset"));
		}

		$this->form_response->send();
	}

	/* display view to setting site */

	public function url_BACKUP() {

		$pageName = "Url Settings";
		$viewfile = "settings/url_settings";

		$modules = null;
		$allModules = get_module_dir_list();

		// run through all modules to check which all use unique urls, store final module list in $modules
		foreach ($allModules as $count => $module) {

			$configFile = $module . "_config"; // Eg. module_menu
			// lod config files, process only if config file available
			if ($this->config->load($configFile, true, true, $module)) {

				$hasUniqueUrls = $this->config->item(WM_MODULE_HAS_UNIQUE_URLS, $configFile);

				// remove module from list if it does not support unique urls
				if ($hasUniqueUrls) {

					// remove from list of module does not support unique URLs
					$modules[] = $module;
				}
			}
		}

		// checl if atleast 1 module has unique URLs
		if (count($modules) > 0) {

			// get settings data from database
			$modulePrefixes = $this->site_settings->get(WM_SET_CATEG_URL, 'module_url_prefixes');

			$parseData = null;

			// generate settings fields for only those module that support unique urls
			// note: settings can be lesser than actual modules that support url prefixes
			foreach ($modules as $module) {

				$parseData[] = array(
				    'module:name' => $module,
				    // note: if urlPrefix not saved, then assume module name
				    'module:prefix' => ( isset($modulePrefixes[$module]) ) ? $modulePrefixes[$module] : $module
				);
			}

			$this->parseData['mapping'] = $parseData;
		}

		$this->parseData['set:category'] = "url";
		$html = $this->parser->parse($viewfile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $pageName, $html);
	}

	public function url() {

		$pageName = "Url Settings";
		$viewfile = "settings/url_settings";

		$this->load->helper('config');

		$moduleConfigs = get_config_array(WM_MODULE_HAS_UNIQUE_URLS);

		// checl if atleast 1 module has unique URLs
		if (count($moduleConfigs) > 0) {

			// get settings data from database
			$modulePrefixes = $this->site_settings->get(WM_SET_CATEG_URL, 'module_url_prefixes');

			$parseData = null;

			// generate settings fields for only those module that support unique urls
			// note: settings can be lesser than actual modules that support url prefixes
			foreach ($moduleConfigs as $module => $config) {

				// only display modules for which WM_MODULE_HAS_UNIQUE_URLS = true
				if (isset($config[WM_MODULE_HAS_UNIQUE_URLS]) AND $config[WM_MODULE_HAS_UNIQUE_URLS] === true) {

					$parseData[] = array(
					    'module:name' => $module,
					    // note: if urlPrefix not saved, then assume module name
					    'module:prefix' => ( isset($modulePrefixes[$module]) ) ? $modulePrefixes[$module] : $module
					);
				}
			}

			$this->parseData['mapping'] = $parseData;
		}

		$this->parseData['set:category'] = "url";
		$html = $this->parser->parse($viewfile, $this->parseData, true);

		echo get_admin_main_content($this->parseData, $pageName, $html);
	}

	public function url_save() {

		$category = $this->input->post(SETTING_CATEG_KEYWORD);
		$modules = $this->input->post('modules');

		$settings = null;
		foreach ($modules as $module) {

			$fieldName = "module_url_prefixes__$module";

			// prepare settings array
			$settings[$module] = $this->input->post($fieldName);

			// set rules
			$this->form_validation->set_rules($fieldName, "$module prefix", 'alpha_dash');
		}

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			/* Validation successfull */

			if ($this->site_settings->set($category, "module_url_prefixes", $settings)) {

				$this->form_response->set_message(WM_STATUS_SUCCESS, SETTING_SAVE_SUCCESS);
			} else {

				$this->form_response->set_message(WM_STATUS_ERROR, SETTING_SAVE_FAIL);
			}
		} else {
			// form validation fail

			$this->form_response
				->set_message(WM_STATUS_ERROR, SETTING_SAVE_FORM_INVALID)
				->set_redirect(null);

			// set validation errors for each module prefix
			foreach ($modules as $module) {

				$fieldName = "module_url_prefixes__$module";
				$this->form_response->add_validation_msg($fieldName, form_error($fieldName));
			}
		}

		$this->form_response->send();
	}

	/*	 * ********************** Private functions ***************************** */

	// sets all necessary parse tags for settings form
	private function _set_form_parse_tags($category, $settings) {

		// set hidden field, category name
		$this->parseData["set:category"] = $category;

		// for options
		$this->load->helper('form');

		foreach ($settings as $setting) {

			$key = $setting["key"];

			$this->parseData["set:key:$key"] = $setting["key"];
			$this->parseData["set:value:$key"] = $setting["value"];
			$this->parseData["set:options:$key"] = $setting["options"];
			$this->parseData["set:description:$key"] = $setting["description"];

			// if type is 'array' ( i.e. for <select><options> ), generate select list, with selected option
			if ($setting["data_type"] == SETTING_TYPE_ARRAY) {

				$name = $key;      // name attribute
				$selectedOption = $setting["value"];    // selected attribute

				$options = null;   // array[ value ] = value
				foreach ($setting["options"] as $item) {

					$options[$item] = $item;
				}
				$extra = "id='" . $name . "'";

				// add entire <select> . . . . </select> to parse tag
				$this->parseData["set:select:$key"] = form_dropdown($name, $options, $selectedOption, $extra);
			}

			switch ($setting["data_type"]) {

				case SETTING_TYPE_BOOL:

					$name = $key;      // name attribute
					$selectedOption = $setting["value"];    // selected attribute

					$attributes = array(
					    'name' => $key,
					    'id' => $key,
					    'value' => true,
					    'checked' => (bool) $setting["value"],
//                                                'value'       => (int)true,
//                                                'checked'     => false,
					    'style' => '',
					);

					$this->parseData["set:checkbox:$key"] = form_checkbox($attributes);

					break;

				case SETTING_TYPE_ARRAY:

					$name = $key;      // name attribute
					$id = $key;
					$selectedOption = $setting["value"];    // selected attribute

					$options = null;   // array[ value ] = value
					foreach ($setting["options"] as $item) {

						$options[$item] = $item;
					}
					$extra = "id='" . $id . "'";
					// add entire <select> . . . . </select> to parse tag
					$this->parseData["set:select:$key"] = form_dropdown($name, $options, $selectedOption, $extra);

					break;



				default:
					break;
			}
		}
	}

}

?>
