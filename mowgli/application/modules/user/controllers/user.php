<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


/**
 * Description of user
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
define('USERNAME_MIN_LENGTH', 5);
define('PASS_MIN_LENGTH', 5);

// delay for redirection after user has successfully logged in
define('USER_LOGIN_REDIRECT_DELAY', 200); //miliseconds
define('USER_DELAY_SMALL', 1000);
define('USER_DELAY_MEDIUM', 3000);
define('USER_DELAY_LARGE', 5000);

define('IDEN_REFERER', 'referer');
define('IDEN_REDIRECT_URL', 'redirect_url');
define('IDEN_DEFAULT_LOGIN_MESSAGE', 'default-message');

define('DEFAULT_ADMIN_REDIRECT', 'admin/dashboard');
//define('DEFAULT_LOGIN_REDIRECT', 'user/login');
define('DEFAULT_LOGIN_REDIRECT', 'login');
define('USER_CHANGE_PASSWORD_LINK', 'user/change_password');

!defined('USER_LOGIN_MODE_NORMAL') ? define('USER_LOGIN_MODE_NORMAL', 'normal') : null;
!defined('USER_LOGIN_MODE_MODAL') ? define('USER_LOGIN_MODE_MODAL', 'modal') : null;

!defined('SESSION_IS_RESET_MODE') ? define('SESSION_IS_RESET_MODE', 'is_reset_mode') : null;
!defined('SESSION_USERNAME') ? define('SESSION_USERNAME', 'username') : null;

class User extends My_Controller {

	private $module = "user";
	private $configFile = 'user_config';
	// config variables
	private $actKeyValidity;
	private $maxUsers;
	private $_defaultMessage = '';    // default message ( if any ) that displays above forms
//	private $_status = null;
//	private $_message = null;
//	private $_responseData = array();
	private $_redirectUrl = null;
	private $_softwareName = null;

	public function __construct() {

		parent::__construct();

		$this->config->load($this->configFile, true, true, $this->module);

		// set config variables from config file
		$this->actKeyValidity = $this->config->item('activation_key_validity', $this->configFile);
		$this->maxUsers = $this->config->item('max_users', $this->configFile);

		define('ACTIVATION_LINK_BASE_URL', site_url('user/activate'));
		define('RESET_LINK_BASE_URL', site_url('user/reset_password_verify'));

		$this->_softwareName = $this->config->item('software_name');

		/* load libraries */
		$this->load->library('session');
		$this->load->library('user/auth');
		$this->load->library('email');
		$this->load->library('form_response');
		$this->load->library('json_response');
		$this->load->library('form_validation');
		//// $this->form_validation->CI = & $this;;    // required for form validation to work with hmvc
		$this->form_validation->set_error_delimiters('', '');   // remove <p> and </p> for all validation errors

		/* Load helpers */
		$this->load->helper('form');

		/* Load user_model */
		$this->load->model('user/user_model');

		$this->parseData["module:resource"] = module_resource_url($this->module);
		$this->parseData[IDEN_DEFAULT_LOGIN_MESSAGE] = $this->_defaultMessage;

		// set default redirect uri as admin/dashboard
		$this->_set_redirectUrl(site_url(DEFAULT_ADMIN_REDIRECT));

//                $this->_set_partial_views();
	}

	public function index() {
		$this->login();
	}

	private function _get__is_reset_mode() {
		return $this->session->userdata(SESSION_IS_RESET_MODE);
	}

	private function _set__is_reset_mode($_is_reset_mode) {

		if ($_is_reset_mode) {
			// set session value
			$this->session->set_userdata(SESSION_IS_RESET_MODE, true);
		} else {
			// delete session
			$this->session->unset_userdata(SESSION_IS_RESET_MODE);
		}
	}

	/**
	 * check if authorized / logged in. If NOT logged in redirects to login page.
	 * @param string $referer url to redirect to after login is successful
	 * @return bool Returns true if logged in
	 */
	public function authorize($referer = DEFAULT_ADMIN_REDIRECT) {

		/* Check if logged in */
		if (!$this->auth->is_logged_in()) {

			/* NOT logged in */
			$this->session->set_userdata(IDEN_REFERER, $referer);

			// if ajax, set header to 401 - unauthorized
			if ($this->input->is_ajax_request()) {

				$this->output->set_status_header('401');
			}
			// if not ajax . . 302 redirect to login page.
			else {

				// -> redirect to login screen */
				redirect(DEFAULT_LOGIN_REDIRECT);
			}
		} else {
			/* Change 31-12-2011 by Lloyd -- 'if' condition added
			 * Reason : db is updated 2 times for every request */

			// check if session has referer, if yes --> delete from session
			if ($this->session->userdata(IDEN_REFERER) !== false) {

				// remove referer from session
				$this->session->unset_userdata(IDEN_REFERER);
			}

			return true;
		}
	}

	public function login($mode = null) {

		// required for new user activation mesage
		$this->parseData[IDEN_DEFAULT_LOGIN_MESSAGE] = $this->_defaultMessage;
		$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);

		/* Check if logged in */
		if (!$this->auth->is_logged_in()) {

			$this->parseData['mode'] = ( $mode == USER_LOGIN_MODE_MODAL ) ? USER_LOGIN_MODE_MODAL : USER_LOGIN_MODE_NORMAL;
			$this->parser->parse('user/login', $this->parseData);
		} else {
			/* Logged in */
			redirect($this->_redirectUrl);
		}
	}

	public function login_do() {

		/* Authenticate user */
		if ($this->_authenticate_user()) {

			/* user authorized */

			$username = $this->security->xss_clean($this->input->post('username'));
			$this->auth->session_login($username);

			// get referer (previous) url from session
			$url = $this->session->userdata(IDEN_REFERER);

			if ($url == FALSE) {

				/* Referer not set in session --> redirect to admin/dashboard */
				$url = $this->_redirectUrl;
			}

			$this->form_response
				->set_message('success', 'Successfully Logged in')
				->set_redirect($url, USER_LOGIN_REDIRECT_DELAY);
		} else {

			// Invalid credentials, send json response

			$this->form_response
				->set_message(WM_STATUS_ERROR, 'Invalid username or password')
				->set_redirect(null);
		}

		$this->form_response->send();
	}

	public function register() {

		/* Check if logged in */
		if (!$this->auth->is_logged_in()) {

			$this->_set_register_form_values();
			$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);

			$this->parser->parse('user/register', $this->parseData);
		} else {
			/* Already logged in, hence redirect to dashboard */
			redirect($this->_redirectUrl);
		}
	}

	public function register_do() {

		/*
		 * PROCESS -->
		 * validation
		 *  - Mandatory fields
		 *  - username/email exists
		 *  - valid email
		 *  - passwords DO NOT match
		 *  - minimum password lengths
		 * XSS filter / security
		 * hash / encrypt password
		 * Generate special key link
		 * Store details in DB
		 * Send email
		 * redirect to last page
		 */

		$pass = $this->input->post('password');

		/* Validations - set rules */
		$this->form_validation->set_rules('username', 'Username', 'required|min_length[' . USERNAME_MIN_LENGTH . ']|xss_clean|callback__check_username_exists');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|xss_clean|callback__check_email_exists');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . PASS_MIN_LENGTH . "]");
		$this->form_validation->set_rules('password-re', 'Confirmation Password', "callback__check_passwords_match[$pass]");
		$this->form_validation->set_rules('question', 'Secret Question', 'required');
		$this->form_validation->set_rules('answer', 'Secret Answer', 'required');

		/* Validations - validate form */
		if ($this->form_validation->run()) {

			$username = $this->security->xss_clean($this->input->post('username'));
			$cryptPassword = $this->auth->crypt_password($this->security->xss_clean($this->input->post('password')));
			;
			$actKey = $this->auth->create_activation_key($cryptPassword);

			/* Validation successfull */

			if ($this->_check_max_users_reached()) {

				$dbFields = array();

				//  $dbFields['user_auth_id'] = null;
				$dbFields['user_auth_username'] = $username;
				$dbFields['user_auth_email'] = $this->security->xss_clean($this->input->post('email'));
				$dbFields['user_auth_password'] = $cryptPassword;
//			$dbFields['user_auth_act_key'] = $actKey;
//			$dbFields['user_auth_act_key_created'] = get_gmt_time();
				$dbFields['user_auth_sec_question'] = $this->security->xss_clean($this->input->post('question'));
				$dbFields['user_auth_sec_answer'] = $this->security->xss_clean($this->input->post('answer'));
				$dbFields['user_auth_created'] = get_gmt_time(); // current GMT time
				//  $dbFields['user_auth_last_login'] = null;
				$dbFields['user_auth_is_active'] = false;

				/* add user to database */
				$this->user_model->transaction_strict();
				$this->user_model->transaction_begin();

				// check if adding user to db is successful
				if ($this->user_model->add_user($dbFields) > 0) {

					// set activation key for newly created user
					$this->user_model->set_activation_key($username, $actKey);

					$act_link = ACTIVATION_LINK_BASE_URL . "/$username/$actKey";
					//$mail   = 'registration successfull your activation link has been mailed to you.<br/>';
					$message = "Thank you for joining.<br/>";
					$message .= "Please click on the <a href='$act_link' target='_blank'>activation link</a> to activate your account<br/><br/>";
					$message .= "OR<br/><br/>";
					$message .= "simply go to the below url in your browser<br/>";
					$message .= "$act_link";
					$message .= "<br/><br/>";
					$message .= "<i>Note: This link is only valid for " . $this->actKeyValidity . " days.</i><br/>";

					$softwareName = $this->_softwareName;

					$serverEmail = $this->site_settings->get(WM_SET_CATEG_EMAIL, 'server_email');
					$serverName = $this->site_settings->get(WM_SET_CATEG_EMAIL, 'from_name');

					$this->email->from($serverEmail, $serverName);
					$this->email->to($dbFields['user_auth_email']);
//                                $this->email->reply_to( $serverEmail, $serverName );
					$this->email->subject($softwareName . ' - User Activation');
					$this->email->message($message);

					if (@$this->email->send()) {

						$this->user_model->transaction_commit();
						$message = "Activation link sent, Please check your email for the activation link";

						$this->form_response
							->set_message('success', $message)
							->set_redirect(site_url(DEFAULT_LOGIN_REDIRECT), USER_DELAY_LARGE);
					} else {

						$this->user_model->transaction_rollback();

						$message = "Cannot send activation email, registration unsuccessfull, please try again later";
//					$message .= "<br/><br/>" . strip_tags($this->email->print_debugger());

						$this->form_response
							->set_message(WM_STATUS_ERROR, $message)
							->set_redirect(DEFAULT_LOGIN_REDIRECT, USER_DELAY_MEDIUM);
					}
				} else {

					$this->user_model->transaction_rollback();

					$message = "An unexpected error occured while trying to register to site";

					$this->form_response
						->set_message(WM_STATUS_ERROR, $message)
						->set_redirect(null);
				}
			} else {

				// reached max limit for users, Registration NOT allowed

				$message = "Sorry !! Max user limit has been reached, Registration unsuccessful.";

				$this->form_response
					->set_message(WM_STATUS_ERROR, $message)
					->set_redirect(null);
			}
		} else {
			//      form NOT valid, send json error message

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields, were submitted")
				->set_redirect(null)
				->add_validation_msg("username", form_error("username"))
				->add_validation_msg("email", form_error("email"))
				->add_validation_msg("password", form_error("password"))
				->add_validation_msg("password-re", form_error("password-re"))
				->add_validation_msg("question", form_error("question"))
				->add_validation_msg("answer", form_error("answer"));
		}

		$this->form_response->send();
	}

	public function change_password() {

		$viewFile = 'user/change-password';

		$this->_set_change_password_form_values();
		$this->parseData['is_reset_mode'] = $this->_get__is_reset_mode();
		$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);

		if (!$this->_get__is_reset_mode()) {

			// Do NOT hide old password field,
			// normal change password mode

			/* redirect to login page if NOT logged in */
			if ($this->authorize(current_url())) {

				$this->parser->parse($viewFile, $this->parseData);
			}
		} else {

			// Password reset mode
			// hide old password field

			$this->parser->parse($viewFile, $this->parseData);
		}
	}

	/**
	 * Change password of logged in user. On success redirects to login page. On failure reloads change_password page with errors
	 */
	public function change_password_do() {

		/* Validations - set rules */
		$username = $this->auth->get_username();
		$oldPass = $this->security->xss_clean($this->input->post('password-old'));
		$newPass = $this->security->xss_clean($this->input->post('password-new'));
		$newPassReType = $this->security->xss_clean($this->input->post('password-new-re'));

		$this->form_validation->set_rules('password-old', 'Current Password', "required");
		$this->form_validation->set_rules('password-new', 'New Password', 'required|min_length[' . PASS_MIN_LENGTH . ']');
		$this->form_validation->set_rules('password-new-re', 'New Confirmation Password', "callback__check_passwords_match[$newPass]");

		/* Validations - validate form */
		if ($this->form_validation->run()) {
			/* Form Valid */

			$success = $this->_check_old_password($username, $oldPass);

			if ($success) {

				// change pass
				$newPass = $this->auth->crypt_password($newPass);
				$this->user_model->change_password($username, $newPass);

				$this->form_response
					->set_message('success', "Password successfully changed")
					->set_redirect(site_url(DEFAULT_ADMIN_REDIRECT), USER_DELAY_MEDIUM);
			} else {

				// Invalid username Or password provided
				$this->form_response
					->set_message(WM_STATUS_ERROR, "Invalid password")
					->set_redirect(null);
			}
		} else {

			//      form NOT valid, send json error message

			$this->form_response
				->set_message(WM_STATUS_ERROR, "Invalid form fields")
				->set_redirect(null)
				->add_validation_msg("password-old", form_error("password-old"))
				->add_validation_msg("password-new", form_error("password-new"))
				->add_validation_msg("password-new-re", form_error("password-new-re"));
		}

		$this->form_response->send();
	}

	public function reset_password() {

		/* DO NOT display reset page if logged in */
		if (!$this->auth->is_logged_in()) {

			$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);
			$this->parser->parse('user/reset-password', $this->parseData);
		} else {
			/* Already logged in, hence redirect to dashboard */
			redirect($this->_redirectUrl);
		}
	}

	public function reset_password_do() {

		$message = null;

		$username = $this->security->xss_clean($this->input->post('username'));
		$email = $this->security->xss_clean($this->input->post('email'));

		// Check if username and email match
		$matchSuccess = $this->user_model->match_user_email($username, $email);

		if ($matchSuccess === true) {

			/* Values valid, reset password */

			$actKey = $this->auth->create_activation_key($username . $email);

			$this->user_model->transaction_strict();
			$this->user_model->transaction_begin();

			// set activation key ( only for active users )
			if ($this->user_model->set_activation_key($username, $actKey, true)) {

				$reset_link = RESET_LINK_BASE_URL . "/$username/$actKey";

				$message = "Your password recovery has been initiated.<br/>";
				$message .= "If you have requested for a password reset follow the instructions below.<br/>";
				$message .= "If you have NOT requested for a password reset, simply ignore this email, you can still use your old password<br/><br/>";
				$message .= "Please click on the <a href='$reset_link' target='_blank'>password reset link</a> to change your password<br/><br/>";
				$message .= "OR<br/>";
				$message .= "simply go to the below url in your browser<br/>";
				$message .= "$reset_link";
				$message .= "<br/><br/>";
				$message .= "<i>Note: This link is only valid for " . $this->actKeyValidity . " days.</i><br/>";

				$this->email->to($email);
				$this->email->subject($this->_softwareName . ' - Password Recovery');
				$this->email->message($message);

				if (@$this->email->send()) {

					$this->user_model->transaction_commit();

					$message = "Password recovery email sent, please check your email";
					$this->form_response
						->set_message('success', $message)
						->set_redirect(null);
				} else {
					// email failed --> redirect to reset page, with error message

					$this->user_model->transaction_rollback();

					$message = "Unable to send password recovery email, please try again later.";
//					$message .= "\n\n" . $this->email->print_debugger();

					$this->form_response
						->set_message('error', $message)
						->set_redirect(null);
				}
			} else {

				// ERROR unable to set activation key ( possibly user is NOT activated yet )
				$this->user_model->transaction_rollback();

				$message = "Error occured while attempting to reset password, please try again later.";
				$this->form_response
					->set_message('error', $message)
					->set_redirect(null);
			}
		} else {
			// username and email does not match
			$message = "Invalid username or email";

			$this->form_response
				->set_message('error', $message)
				->set_redirect(null);
		}

		$this->form_response->send();
	}

	public function reset_password_verify($username, $actkey) {

		// display change password form ( only new pass and confirm pass )
		/*
		 * Check if actkey valid
		 * 	* delete keys older than x days
		 */

		$username = $this->security->xss_clean($username);
		$actkey = $this->security->xss_clean($actkey);

		// resets old/invalid activation keys to null
		$this->user_model->delete_activation_keys();

		if ($this->user_model->check_activation_key($username, $actkey, true)) {

			// set username in session for verification in reset_password_verify_do
			$this->session->set_userdata(SESSION_USERNAME, $username);

			// set reset mode ( so that change password does NOT ask for old password )
			$this->_set__is_reset_mode(true);

			$this->_set_change_password_form_values();
			$this->parseData['is_reset_mode'] = true;
			$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);
			$this->parser->parse('user/change-password', $this->parseData);
		} else {

			$this->show_error_page("The password recovery url provided is not valid");
		}
	}

	public function reset_password_verify_do() {

		$username = $this->session->userdata(SESSION_USERNAME);

		// check if currently in reset_mode AND username is set in session
		if ($this->_get__is_reset_mode() AND $username !== false) {

			/* Validations - set rules */
			$newPass = $this->security->xss_clean($this->input->post('password-new'));
			$newPassReType = $this->security->xss_clean($this->input->post('password-new-re'));

			$this->form_validation->set_rules('password-new', 'New Password', 'required|min_length[' . PASS_MIN_LENGTH . ']');
			$this->form_validation->set_rules('password-new-re', 'New Confirmation Password', "callback__check_passwords_match[$newPass]");

			/* Validations - validate form */
			if ($this->form_validation->run()) {

				/* Form Valid */

				// change pass
				$newPass = $this->auth->crypt_password($newPass);
				$this->user_model->change_password($username, $newPass);

				// remove temp username from session, remove reset mode
				$this->session->unset_userdata(SESSION_USERNAME);
				$this->_set__is_reset_mode(false);

				// reset activation key
				$this->user_model->set_activation_key($username);

				$this->form_response
					->set_message('success', "Password successfully changed")
					->set_redirect(site_url(DEFAULT_ADMIN_REDIRECT), USER_DELAY_MEDIUM);
			} else {

				//      form NOT valid, send json error message

				$this->form_response
					->set_message(WM_STATUS_ERROR, "Invalid form fields")
					->set_redirect(null)
					->add_validation_msg("password-new", form_error("password-new"))
					->add_validation_msg("password-new-re", form_error("password-new-re"));
			}
		} else {
			$message = "Please use the correct recovery password link to reset lost password";
			$this->form_response->set_message(WM_STATUS_ERROR, $message);
		}

		$this->form_response->send();
	}

	public function activate($username, $actKey) {

		$username = $this->security->xss_clean($username);

		// delete expired inactive users
		$this->user_model->delete_activation_keys(true);

		if ($this->_check_max_users_reached()) {

			if ($this->user_model->check_activation_key($username, $actKey, false)) {

				// delete act key from database
				// inform successful activation
				$this->user_model->activate_user($username);

				$this->_set_defaultMessage("Your account has been activated");
				$this->login();
			} else {
				// invalid/expired activation link
				$message = "The activation link provided is NOT valid";
				//$this->_set_defaultMessage($message);
				$this->show_error_page($message);
			}
		} else {

			// max users reached
			$message = "Sorry !! Max user limit has been reached, Registration unsuccessful.";
			//$this->_set_defaultMessage($message);
			$this->show_error_page($message);
		}
	}

	public function logout() {
		$this->auth->session_logout();

		// display login page, show logged out message
//		$this->parseData[IDEN_DEFAULT_LOGIN_MESSAGE] = "You are currently logged out";
		$this->parseData[IDEN_DEFAULT_LOGIN_MESSAGE] = "";
		$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);

		$this->parser->parse(DEFAULT_LOGIN_REDIRECT, $this->parseData);
	}

	/**
	 * Display a single page error message
	 */
	private function show_error_page($message) {

		$this->parseData['message'] = $message;
		$this->parseData['head'] = $this->parser->parse("user/includes/head", $this->parseData, true);
		$this->parser->parse('user/error', $this->parseData);
	}

	/**
	 * Checks user credentials from POST and verifies if user is correct or not
	 *
	 * @return bool
	 */
	private function _authenticate_user() {

		$username = $this->security->xss_clean($this->input->post('username'));
		$cryptPass = $this->auth->crypt_password($this->security->xss_clean($this->input->post('password')));
		//$this->form_validation->set_message('_authenticate_user', 'Invalid Username or Password');
		return ( $this->user_model->check_user_authorized($username, $cryptPass) );
	}

	private function _set_register_form_values() {

		$this->load->helper('form');

		$this->parseData['register:username'] = set_value('username');
		$this->parseData['register:email'] = set_value('email');
		$this->parseData['register:password'] = set_value('password');
		$this->parseData['register:password-re'] = set_value('password-re');
		$this->parseData['register:question'] = set_value('question');
		$this->parseData['register:answer'] = set_value('answer');

		$this->parseData['admin:error_message'] = validation_errors();
	}

	private function _set_change_password_form_values() {

		$this->load->helper('form');

		$this->parseData['change-pass:username'] = $this->auth->get_username();
		$this->parseData['change-pass:password-old'] = '';
		$this->parseData['change-pass:password-new'] = '';
		$this->parseData['change-pass:password-new-re'] = '';

		$this->parseData['admin:error_message'] = validation_errors();
	}

	/*	 * ***************** Form Validation methods ***************** */

	public function _check_username_exists($username) {

		$username = $this->security->xss_clean($username);

		$this->form_validation->set_message('_check_username_exists', 'Username already exists');

		return (!$this->user_model->check_user_exists($username) );
	}

	public function _check_email_exists($email) {

		$email = $this->security->xss_clean($email);

		$this->form_validation->set_message('_check_email_exists', 'Email already exists');

		return (!$this->user_model->check_email_exists($email) );
	}

	/**
	 * @return bool
	 */
	public function _check_old_password($username, $oldPass) {

		$cryptPass = $this->auth->crypt_password($this->security->xss_clean($oldPass));

		return $this->user_model->check_user_authorized($username, $cryptPass);
	}

	public function _check_passwords_match($passRe, $pass) {

		$success = false;

		if ($pass == $passRe) {

			$success = true;
		} else {
			$success = false;
			$this->form_validation->set_message('_check_passwords_match', 'The new passwords do not match');
		}

		return $success;
	}

	public function _match_username_email($email, $username) {

		$success = false;
		$errorMsg = "Username and Email do not match";

		if ($this->user_model->match_user_email($username, $email)) {

			$success = true;
		} else {
			$this->form_validation->set_message('_match_username_email', $errorMsg);
			$success = false;
		}

		return $success;
	}

	/*	 * ***************** Form Validation methods END ***************** */

	private function _set_defaultMessage($_defaultMessage) {
		$this->_defaultMessage = $_defaultMessage;
		$this->parseData[IDEN_DEFAULT_LOGIN_MESSAGE] = $this->_defaultMessage;
	}

	private function _set_redirectUrl($redirectUrl) {
		$this->_redirectUrl = $redirectUrl;
	}

	/**
	 * Checks if the ACTIVE users in the database are less than the user limit ( from config )
	 *
	 * @return bool Returns true if Active users are lesser than limit OR if limit = false, False otherwise
	 */
	private function _check_max_users_reached() {

		$dbUserCount = $this->user_model->get_user_count(true);

		return ( $this->maxUsers === false OR $dbUserCount < $this->maxUsers ) ? true : false;
	}

	/*	 * ************** Testing Deletable UNUSED functions *********** */

//
//	private function _set_reset_password_form_values() {
//
//		$this->load->helper('form');
//
//		$this->parseData['username'] = set_value('username');
//		$this->parseData['email'] = set_value('email');
//
//		$this->parseData['admin:error_message'] = validation_errors();
//	}
//
//
//	private function _set_status($_status) {
//		$this->_status = $_status;
//	}
//
//	private function _set_message($_message) {
//		$this->_message = $_message;
//	}
//
//	private function _set_responseData($_responseData) {
//		$this->_responseData = $_responseData;
//	}
//	private function _add_response_data($key, $value) {
//		$this->_responseData[$key] = $value;
//	}
//
//	private function _clear_json_response() {
//
//		$this->_set_redirectUrl(null);
//		$this->_set_message(null);
//		$this->_set_status(null);
//		$this->_set_responseData(null);
//	}
//
//	private function _send_json_response() {
//
//		$this->json_response
//			->set_message($this->_status, $this->_message)
////                        ->set_data( array( 'errors' => $this->_responseData ) )
//			->set_data($this->_responseData)
//			->send();
//
////                $this->_clear_json_response();
//	}
}

?>
