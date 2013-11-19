<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class MY_Model extends CI_Model {

	protected $db;

	public function __construct() {
		parent::__construct();

//        $config['hostname'] = "localhost";
//        $config['username'] = "root";
//        $config['password'] = "";
//        $config['database'] = "admin_panel";
//        $config['dbdriver'] = "mysql";
//        $config['dbprefix'] = "";
//        $config['pconnect'] = FALSE;
//        $config['db_debug'] = true; // to throw database error messages
//        $config['cache_on'] = FALSE;
//        $config['cachedir'] = "";
//        $config['char_set'] = "utf8";
//        $config['dbcollat'] = "utf8_general_ci";
		//$this->db = $this->load->database($config, TRUE);
		$this->db = $this->load->database('', true); // true --> to return database object
	}

	public function get_db() {
		return $this->db;
	}

	public function set_db(& $db) {
		$this->db = & $db;
	}

	public function transaction_strict($mode = TRUE) {
		$this->db->trans_strict($mode);
	}

	public function transaction_start($test_mode = FALSE) {
		$this->db->trans_start($test_mode);
	}

	public function transaction_begin($test_mode = FALSE) {
		return $this->db->trans_begin($test_mode);
	}

	public function transaction_complete() {
		return $this->db->trans_complete();
	}

	public function transaction_rollback() {
		return $this->db->trans_rollback();
	}

	public function transaction_commit() {
		return $this->db->trans_commit();
	}

	public function transaction_status() {
		return $this->db->trans_status();
	}

	/**
	 * accepts single dimension array OR string
	 * runs through array
	 * adds slashes to all string values only
	 * returns safe array
	 *
	 * @param array|string $dbArray
	 * @return array|string|null returns array on success, null if NOT an array
	 */
	public function addslashes($dbArray) {

		$return = null;

		// check if array
		if (is_array($dbArray)) {

			/* Run through each column-value pair */
			foreach ($dbArray as $col => $value) {

				//      Delete null values from database, Add slashes only to strings
				if (is_null($value)) {

					unset($dbArray[$col]);
				} elseif (is_string($value)) {

					$dbArray[$col] = addslashes($value);
					//$dbArray[ $col ] =  mysql_real_escape_string( $value );
				}
			}

			$return = $dbArray;
		}
		// check if string
		elseif (is_string($dbArray)) {

			$return = addslashes($dbArray);
		}

		// do not add slashes
		else {
			$return = $dbArray;
		}

		return $return;
	}

	public function stripslashes($data) {
		return !is_null($data) ? stripslashes($data) : null;
	}

	/**
	 * Returns bool according to update success,
	 * note: mysql_affected_rows() returns -1 if failure occured.
	 * If it returns 0 It could also mean that query successful, but no change was found in values
	 *
	 * @return bool
	 */
	public function check_update_success() {

		return $this->db->affected_rows() != -1 ? true : false;
	}

	/**
	 * Reorders elements according to provided oldElementOrder and newElementOrder
	 *
	 * @param string $tableName
	 * @param string $orderColumnName Name of the column that contains the ordering information
	 * @param string $oldOrder      Order value of element being moved
	 * @param string $newOrder      New Order value ( new position ) of element being moved
	 * @param string $whereConditions string of where conditions if any
	 *
	 * @return bool Returns true on Success, False ON failure/Query NOT executed
	 */
	protected function reorder($tableName, $orderColumnName, $oldOrder, $newOrder, $whereConditions = null) {

		$success = false;

		// reset values to null if invalid values found
		$tableName = ( $tableName == "" OR is_null($tableName) ) ? null : $tableName;
		$orderColumnName = ( $orderColumnName == "" OR is_null($orderColumnName) ) ? null : $orderColumnName;
		$oldOrder = ( $oldOrder == "" OR is_null($oldOrder) ) ? null : $oldOrder;
		$newOrder = ( $newOrder == "" OR is_null($newOrder) ) ? null : $newOrder;
		$whereConditions = ( $whereConditions == "" OR is_null($whereConditions) ) ? null : $whereConditions;

		// check if tablename, columnName, oldOrder, newOrder fields are NOT null
		if (!( is_null($tableName) OR is_null($orderColumnName) OR is_null($oldOrder) OR is_null($newOrder) )) {

			$tableName = "`" . $tableName . "`";
			$orderColumnName = "`" . $orderColumnName . "`";
			$whereConditions = (!is_null($whereConditions) AND $whereConditions != "" ) ? " AND $whereConditions " : "";

			if ($oldOrder > $newOrder) {
				/*
				  # Upwards

				  SET @old = 11;
				  SET @new = 1;

				  UPDATE gallery_items
				  SET gallery_item_order =
				  (
				  CASE
				  WHEN gallery_item_order = @old THEN @new
				  WHEN gallery_item_order = @new THEN gallery_item_order + 1
				  ELSE gallery_item_order + 1
				  END
				  )
				  WHERE gallery_item_order BETWEEN @new AND @old

				  ORDER BY gallery_item_order ASC;
				 */

				$sql = "
					UPDATE $tableName
					SET $orderColumnName =
					(
						CASE
						WHEN $orderColumnName = $oldOrder THEN $newOrder
						WHEN $orderColumnName = $newOrder THEN $orderColumnName + 1
						ELSE $orderColumnName + 1
						END
					)
					WHERE $orderColumnName BETWEEN $newOrder AND $oldOrder
					$whereConditions
					ORDER BY $orderColumnName ASC;";
			} else {
				/*
				  # Downwards

				  SET @old = 2;
				  SET @new = 6;
				  SET @new = @new + 1;

				  UPDATE gallery_items
				  SET gallery_item_order =
				  (
				  CASE
				  WHEN gallery_item_order = @old THEN @new - 1
				  WHEN gallery_item_order = @new THEN gallery_item_order
				  ELSE gallery_item_order - 1
				  END
				  )
				  WHERE gallery_item_order BETWEEN @old AND @new

				  ORDER BY gallery_item_order ASC;
				 */

				$newOrder = $newOrder + 1;
				$sql = "
					UPDATE $tableName
					SET $orderColumnName =
					(
						CASE
						WHEN $orderColumnName = $oldOrder THEN $newOrder - 1
						WHEN $orderColumnName = $newOrder THEN $orderColumnName
						ELSE $orderColumnName - 1
						END
					)
					WHERE $orderColumnName BETWEEN $oldOrder AND $newOrder
					$whereConditions
					ORDER BY $orderColumnName ASC;";
			}

			$success = $this->db->query($sql);
		} else {

			$success = false;
		}

		return $success;
	}

	protected function reorder_BACKUP($tableName, $orderColumnName, $oldOrder, $newOrder, $whereConditions = null) {

		$success = false;

		// reset values to null if invalid values found
		$tableName = ( $tableName == "" OR is_null($tableName) ) ? null : $tableName;
		$orderColumnName = ( $orderColumnName == "" OR is_null($orderColumnName) ) ? null : $orderColumnName;
		$oldOrder = ( $oldOrder == "" OR is_null($oldOrder) ) ? null : $oldOrder;
		$newOrder = ( $newOrder == "" OR is_null($newOrder) ) ? null : $newOrder;
		$whereConditions = ( $whereConditions == "" OR is_null($whereConditions) ) ? null : $whereConditions;


		// check if tablename, columnName, oldOrder, newOrder fields are NOT null
		if (!( is_null($tableName) OR is_null($orderColumnName) OR is_null($oldOrder) OR is_null($newOrder) )) {

			$tableName = "`" . $tableName . "`";
			$orderColumnName = "`" . $orderColumnName . "`";
			$whereConditions = (!is_null($whereConditions) AND $whereConditions != "" ) ? " AND $whereConditions " : "";


			/*
			  SET @oldOrder = 3;
			  SET @newOrder = 1;

			  UPDATE
			  movies
			  SET
			  sortOrder =
			  CASE
			  WHEN sortOrder = @oldOrder THEN @newOrder
			  ELSE IF(@newOrder > @oldOrder, sortOrder - 1, sortOrder + 1)
			  END
			  WHERE
			  sortOrder BETWEEN LEAST(@newOrder, @oldOrder) AND GREATEST(@newOrder, @oldOrder)
			 */

			$sql = "
                        UPDATE $tableName
                        SET
                                $orderColumnName =
                                  ( CASE
                                        WHEN $orderColumnName = $oldOrder THEN $newOrder
                                        ELSE IF( $newOrder > $oldOrder, $orderColumnName - 1, $orderColumnName + 1 )
                                        END
                                  )
                        WHERE
                                $orderColumnName BETWEEN
                                  LEAST( $newOrder, $oldOrder ) AND
                                  GREATEST( $newOrder, $oldOrder )

                                $whereConditions
                                ";

			$success = $this->db->query($sql);
		} else {

			$success = false;
		}

		return $success;
	}

	/**
	 * returns the last query that was run in the database
	 */
	public function last_query() {


		return $this->db->last_query();
	}

	public function total_row_count() {

		/**
		 * LOGIC:
		 * - Check if SQL_CALC_TOTAL_ROWS is present in last query
		 * - Yes -->
		 *      - get FOUND_ROWS()
		 * - NO -->
		 *      - return null
		 */
//                $totalCount = null;
//
//                $lastQuery = $this->last_query();
//
//                $pattern = "/^(.+)SQL_CALC_FOUND_ROWS(.+)$/i";
//
//                if ( (bool) preg_match($pattern, $lastQuery)) {
//
//                        $query = $this->db->query('SELECT FOUND_ROWS() as count FROM dual');
//                        $totalCount = $query->row()->count;
//                }
//                else{
//                        $totalCount = null;
//                }

		$query = $this->db->query('SELECT FOUND_ROWS() AS count FROM dual');
		return $query->row()->count;
	}

}

?>