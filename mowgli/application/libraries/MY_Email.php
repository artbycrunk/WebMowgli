<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of MY_Email
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class MY_Email extends CI_Email {

	private $ci;
	/*
	 * [default]
	  'from' Name
	  'from' email
	  'reply-to' email
	  'reply-to' name
	  bcc list

	  smtp (yes/no)

	  host
	  port
	  user
	  pass
	  charset
	 */
	private $config;
	private $settings = null; // key-value pair directly from db
	private $from_name = null;
	private $from_email = null;
	private $reply_name = null;
	private $reply_to = null;
	private $bcc = array();

	public function __construct($config = array()) {

//		parent::__construct();

		$this->ci = & get_instance();

		if (get_settings(WM_SET_CATEG_EMAIL, 'smtp') == true) {

			// use SMTP
			// Load config from database

			$this->_load_settings();
			$this->initialize($this->config);
		}
		// check if config provided in config file ( config/email.php ) is provided, set defaults if it is
		elseif (is_array($config) AND count($config) > 0) {

			$this->_set_defaults();
			$this->config = $config;
			$this->initialize($config);
		} else {
			// Email NOT initialized
		}

		log_message('debug', "Email Class Initialized");
	}

	public function __construct_BACKUP($config = array()) {

		parent::__construct();

		$this->ci = & get_instance();

		/* If config provided OR
		 * if config file created in application/config/email.php,
		 * --> use provided config & override database config */
		if (count($config) > 0) {

			$this->initialize($config);
			$this->config = $config;
		} else {
			// Load config from database
			if ($this->_load_settings()) {

				$this->initialize($this->config);
			}

			// if values NOT set in db --> DO not load --> CI has loaded from config file
		}
	}

	private function _set_defaults() {

		$settings = get_settings(WM_SET_CATEG_EMAIL);
		// check if settings available or some error
		if (!is_null($settings)) {

			$this->settings = $settings;

			$fromEmail = $settings['server_email'];
			$fromName = $settings['from_name'];

			// set default from, replyto, bcc list
			$this->from($fromEmail, $fromName);
			$this->reply_to($fromEmail, $fromName);
			$this->bcc($settings['bcc']);
			$this->subject("Email from " . $fromName);
			$this->message("");
		}
	}

	// will get config from database
	private function _load_settings() {

		$settings = get_settings(WM_SET_CATEG_EMAIL);

		$this->settings = $settings;

		// check if settings available or some error
		if (!is_null($settings)) {

			$this->from_email = $this->reply_to = $settings['server_email'];
			$this->from_name = $this->reply_name = $settings['from_name'];
			$this->bcc = $settings['bcc'];

			// set default from, replyto, bcc list
			$this->from($this->from_email, $this->from_name);
			$this->reply_to($this->reply_to, $this->reply_name);
			$this->bcc($this->bcc);
			$this->subject("Email from " . $this->from_name);
			$this->message("");

			// set config values for email
			$this->config['useragent'] = $this->ci->config->item('software_name');
			$this->config['protocol'] = ( $settings['smtp'] == true ) ? 'smtp' : 'mail';
			$this->config['smtp_host'] = $settings['smtp_host'];
			$this->config['smtp_port'] = $settings['smtp_port'];
			$this->config['smtp_user'] = $settings['smtp_username'];
			$this->config['smtp_pass'] = $settings['smtp_password'];
			$this->config['charset'] = $settings['smtp_charset'];
			$this->config['newline'] = "\r\n";       // comply with RFC 822 standard
			$this->config['mailtype'] = "html";
			$this->config['validate'] = true;
		}

		return $this;
	}

	public function clear($clear_attachments = FALSE) {

		/* Clear data as usual */
		parent::clear($clear_attachments);

		/* reset values from database */
		$this->from($this->from_email, $this->from_name);
		$this->reply_to($this->reply_to, $this->reply_name);
		$this->bcc($this->bcc);
	}

}

?>
