<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!function_exists('get_gmt_time')) {

	function get_gmt_time($format = 'Y-m-d H:i:s') {

		return date($format, time() - date("Z")); // current GMT time
	}

}

if (!function_exists('format_date')) {

	function format_date($dateTimeString, $format) {

		return date_format(date_create($dateTimeString), $format);
	}

}

if (!function_exists('get_date_tags')) {

	/**
	 * Generates an array of date tags for a given date-time string
	 * generates the following tags.
	 *
	 * Eg.
	 * {day:short} - 1, 12
	 * {day:long} - 01, 12

	 * {weekday:short} - mon, tue, wed
	 * {weekday:long} - monday, tuesday

	 * {month:short} - jan, feb
	 * {month:long} - January, February

	 * {year:short} - 01, 02
	 * {year:long} - 2001, 2002
	 *
	 * @param string $dateTimeString date time string
	 *
	 * @return array
	 */
	function get_date_tags($dateTimeString) {

		$tags = array(
		    'day:short' => date('j', strtotime($dateTimeString)),	// 1, 5, 30
		    'day:long' => date('d', strtotime($dateTimeString)),	// 01, 05, 30

		    'weekday:short' => date('D', strtotime($dateTimeString)),	// mon, tue, wed
		    'weekday:long' => date('l', strtotime($dateTimeString)),	// monday, tuesday

		    'month:short' => date('M', strtotime($dateTimeString)),	// jan, feb
		    'month:long' => date('F', strtotime($dateTimeString)),	// january, februaru

		    'year:short' => date('y', strtotime($dateTimeString)),	// 01, 02
		    'year:long' => date('Y', strtotime($dateTimeString))	// 2001, 2002
		);

		return $tags;
	}

}
?>
