<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
//define('TBL_USERS_AUTH', 'users_auth');


class user_model extends MY_Model {

	private $module = 'user';
	private $configFile = 'user_config';
	private $actKeyValidity; // in days

	public function __construct() {
		parent::__construct();

		$this->config->load($this->configFile, true, true, $this->module);
		$this->config->load('database_tables');

		// get activation key validity from config files
		$this->actKeyValidity = $this->config->item('activation_key_validity', $this->configFile);

		define('TBL_USERS_AUTH', $this->config->item('TBL_USERS_AUTH'));
	}

	public function add_user($dbFields) {

		$dbFields = $this->addslashes($dbFields);
		$this->db->insert(TBL_USERS_AUTH, $dbFields);
		return $this->db->affected_rows();
	}

	public function check_user_exists($username) {

		$username = $this->addslashes($username);
		$sql = "SELECT count(*) as count FROM " . TBL_USERS_AUTH . " WHERE user_auth_username = '$username'";
		return ( $this->db->query($sql)->row()->count > 0 ) ? true : false;
	}

	public function check_email_exists($email) {

		$email = $this->addslashes($email);
		$sql = "SELECT count(*) as count FROM " . TBL_USERS_AUTH . " WHERE user_auth_email = '$email'";
		return ( $this->db->query($sql)->row()->count > 0 ) ? true : false;
	}

	public function check_user_authorized($username, $password) {

		$username = $this->addslashes($username);
		$password = $this->addslashes($password);

		$this->db->select('user_auth_username');
		$this->db->from(TBL_USERS_AUTH);
		$this->db->where('user_auth_username', $username);
		$this->db->where('user_auth_password', $password);
		$this->db->where('user_auth_is_active', true);
		$query = $this->db->get();
		return ( $query->num_rows() > 0 ? true : false);
	}

	public function match_user_email($username, $email) {

		$username = $this->addslashes($username);
		$email = $this->addslashes($email);

		$this->db->select('user_auth_username');
		$this->db->from(TBL_USERS_AUTH);
		$this->db->where('user_auth_username', $username);
		$this->db->where('user_auth_email', $email);
		$this->db->where('user_auth_is_active', true);
		$query = $this->db->get();
		return ( $query->num_rows() > 0 ? true : false);
	}

	public function check_activation_key($username, $actKey, $is_active = null) {

		$username = $this->addslashes($username);
		$actKey = $this->addslashes($actKey);

		$this->db->select('user_auth_act_key');
		$this->db->from(TBL_USERS_AUTH);
		$this->db->where('user_auth_username', $username);
		$this->db->where('user_auth_act_key', $actKey);
		if (is_bool($is_active)) {

			// apply is_active condition ONLY if provided
			$this->db->where('user_auth_is_active', $is_active);
		}


		$query = $this->db->get();
		return ( $query->num_rows() > 0 ? true : false );
	}

	/**
	 * Activate user when he clicked on the correct activation link
	 * Delete activation key if user successfully activated
	 */
	public function activate_user($username) {

		$username = $this->addslashes($username);

		$data = array(
		    'user_auth_is_active' => true
		);
		$this->db->where('user_auth_username', $username);
		$return = $this->db->update(TBL_USERS_AUTH, $data);

		if ($return) {

			// reset activation key
			$return = $this->set_activation_key($username);
		}

		return $return === false ? false : true;
	}

	public function change_password($username, $password) {

		$username = $this->addslashes($username);
		$password = $this->addslashes($password);

		$this->db->set('user_auth_password', $password);
		$this->db->where('user_auth_username', $username);
		$return = $this->db->update(TBL_USERS_AUTH);

		return $return === false ? false : true;
//                return $return != -1 ? true : false;
	}

	/**
	 * Sets / resets the activation key for the given username
	 *
	 * @param string|array $username single / list of usernames for which the activation key should be set
	 * @param string $actKey activation key ( if NOT provide, will reset activation key )
	 * @param bool $is_active whether to search only among active/inactve users ( if NOT set, searches all users )
	 *
	 * @param bool
	 */
	public function set_activation_key($username, $actKey = null, $is_active = null) {

		$username = $this->addslashes($username);
		$actKey = $this->addslashes($actKey);

		$data = array(
		    'user_auth_act_key' => $actKey,
		    'user_auth_act_key_created' => is_null($actKey) ? null : get_gmt_time()
		);
		$this->db->where_in('user_auth_username', $username);

		// if $is_active provided, add to where conditions, else ignore
		if (is_bool($is_active)) {

			$this->db->where('user_auth_is_active', $is_active);
		}

		$return = $this->db->update(TBL_USERS_AUTH, $data);

		return $return === false ? false : true;
	}

	/**
	 * Deletes Old/invalid activation keys OR deletes user having old/invalid activation keys
	 *
	 * @param bool $is_delete_user Dletes user if set to true
	 *
	 * @return void
	 */
	public function delete_activation_keys($is_delete_user = false) {

		// search for rows that were created x days before current date time
		$this->db->where("user_auth_act_key_created < SUBDATE( UTC_TIMESTAMP(), interval " . $this->actKeyValidity . " day )");

		if ($is_delete_user) {

			// delete users, that are NOT active
			$this->db->where('user_auth_is_active', false);
			$this->db->delete(TBL_USERS_AUTH);
		} else {

			// reset act key to null
			$data = array(
			    'user_auth_act_key' => null,
			    'user_auth_act_key_created' => null
			);

			$this->db->update(TBL_USERS_AUTH, $data);
		}
	}

	/**
	 * Get number of users in table
	 * $is_active = null --> all users in table
	 * is_active = true --> only Active users
	 * is_active = false --> only InActive users
	 *
	 * @param bool $is_active to get type of users, possible values ( null, true, false )
	 */
	public function get_user_count($is_active = null) {

		$count = null;

		$this->db->select('count(*) AS count', false); // prevent backticks
		$this->db->from(TBL_USERS_AUTH);

		if (is_bool($is_active)) {

			$this->db->where('user_auth_is_active', $is_active);
		}

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			$count = $row['count'];
		}

		return $count;
	}

}

?>
