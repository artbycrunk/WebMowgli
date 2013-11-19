<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * Parser Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Parser
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/parser.html
 */
class MY_Parser extends CI_Parser {

	private $mapping = null;

	public function get_mapping() {
		return $this->mapping;
	}

	public function set_mapping($mapping) {
		$this->mapping = $mapping;
	}

	/**
	 *  Parse a template ( Overloaded method )
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * IMPROVEMENTS over original CI method
	 * - considers {if}{else}{/if} tags and accordingly parses tags
	 * - handles multiple instances of blocks/array tags
	 * - supports if-else statements in child blocks ( dows NOT clash with 'if' of parent )
	 *
	 *
	 * @access	public
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	function _parse($template, $data, $return = FALSE) {

		if ($template == '') {

			return FALSE;
		}

		$template = $this->_render($template, $data);

		if ($return == FALSE) {

			$CI = & get_instance();
			$CI->output->append_output($template);
		}

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 * Performs actual rendering logic
	 * - separate variable type data and array( block ) type data
	 * - filter out blocks, replace with temp string
	 * - evaluate {if}{else}{/if} conditions and decide final display for current level
	 * - undo block filter, replace temporary unique string with actual block string
	 * - render blocks for current level
	 * - render the top level variables ( NON block types )
	 *
	 * @param string	$template	tempalte to be rendered
	 * @param array		$data		Array of All parseTags ( variables + block types )
	 */
	private function _render( $template, $data ){

		$variables = null; // will hold key-value pair for variables
		$blocks = null;  // will hold key-value pair for blocks/arrays
		//
		// separate variable type data and array( block ) type data
		$this->_separate_blocks($data, $variables, $blocks);

		// filter out blocks, replace with temp string
		$template = $this->_filter_blocks($template, $data);

		// evaluate {if}{else}{/if} conditions and decide final display for current level
		$template = $this->_evaluate_conditions($template, $data);

		// undo block filter, replace temporary unique string with actual block string
		$template = $this->_undo_filter_blocks($template);

		// render blocks for current level
		$template = $this->_render_blocks($template, $blocks);

		// render the top level variables ( NON block types )
		$template = $this->_render_variables($template, $variables);

		return $template;
	}

	/**
	 * Separates variable parseTags from the Blocks parseTags
	 * and puts them in referenced variables $variables and $blocks
	 *
	 * @param array	$data		list of all parseTags ( variable and arrays ) with their corresponding values
	 * @param ref	$variables	will hold the variable type parsetags and their values
	 * @param ref	$blocks		will hold the block type parsetags and their values
	 *
	 * @return void
	 *
	 */
	private function _separate_blocks($data, & $variables, & $blocks) {

		if (is_array($data)) {

			foreach ($data as $key => $val) {

				if (is_array($val)) {

					$blocks[$key] = $val;
				} else {

					$variables[$key] = $val;
				}
			}
		}
	}

	/**
	 * Temporarily remove array blocks from template and replace them with unique strings
	 * this will prevent any {/if} from clashing with the top level {if} if any
	 * mappings are created between each unique string and its corresponding actual strings and stored in $this->mappings
	 *
	 * @param string $string template to be filtered
	 * @param array $data list of all parse tags with their values ( inclusive of both variables AND blocks )
	 *
	 * @return string Returns modified string after replacing blocks with temporary unique string
	 */
	private function _filter_blocks($string, $data) {

		$mapping = null;
		$start = "@{{@**";
		$end = "**@}}@";

		if (is_array($data)) {

			// reset mapping
			$this->set_mapping(null);

			// run through current parseTags ( both variables AND arrays )
			foreach ($data as $key => $var) {

				// check if matches found for block type tags
				$matches = $this->_match_pairs($string, $key);

				// check if match found for current variable
				if ($matches !== false) {

					// in case of multiple occurances of same block keyword
					// Eg. {posts}..ABC..{/posts} . . .{posts}..XYZ..{/posts}
					foreach ($matches as $count => $match) {

						// @{{@**var**@}}@
						$replacement = $start . $key . $count . $end;

						// replace text with temp replacement string
						$string = str_replace($match[0], $replacement, $string);

						// add replacement, actual string to mapping, to undo replacement later
						$mapping[$replacement] = $match[0];
					}
				}
			}

			$this->set_mapping($mapping);
		}

		return $string;
	}

	private function _undo_filter_blocks($template) {

		if (is_array($this->get_mapping())) {

			foreach ($this->get_mapping() as $unique => $string) {

				$template = str_replace($unique, $string, $template);
			}
		}

		// reset mapping
		$this->set_mapping(null);

		return $template;
	}

	/**
	 * Searches and Evaluates any {if}{else}{/if} conditions
	 * currently only supports one test per if statement
	 *
	 * @param string $template	tempalte to perform {if}{else}{/if} evaluation
	 * @param array	 $data		all array of parseTags ( both variable + array types ) with their values
	 *
	 * @return string evaluated string with either the if section or the else section
	 */
	private function _evaluate_conditions($template, $data) {

		// Evaluate conditions only for current $key
		$expression = '/' . $this->l_delim . "\s*if (.+)" . $this->r_delim . '(.+)' . $this->l_delim . '\s*\/if\s*' . $this->r_delim . '/siU';
//		$expression = '/' . $this->l_delim . "if (.+)" . $this->r_delim . '(.+)' . $this->l_delim . '\/if' . $this->r_delim . '/siU';

		if (preg_match_all($expression, $template, $conditionals, PREG_SET_ORDER)) {

			if (count($conditionals) > 0) {

				// filter through conditionals
				foreach ($conditionals as $condition) {

//					// get full string match, in including {if} .... {/if}
					$outerText = (isset($condition[0])) ? $condition[0] : FALSE;
					// get if  conditions
					$condString = (isset($condition[1])) ? str_replace(' ', '', $condition[1]) : FALSE;
					$innerText = (isset($condition[2])) ? $condition[2] : '';

					// check code is valid, remove entire outerText if conditions string contains '{' OR }
					if (!preg_match('/(' . $this->l_delim . '|' . $this->r_delim . ')/', $condString, $condProblem)) {

						// if outerText empty OR conditionString NOT provided OR innerText NOT provided --> ignore current {if} . . {/if}
						if (!empty($outerText) || $condString !== FALSE || !empty($innerText)) {


							if (preg_match("/^!(.*)$/", $condString, $matches)) {

//								$lhsValue = (@!$data[trim($matches[1])]) ? 0 : $data[trim($matches[1])];
								$lhsValue = (!isset($data[trim($matches[1])]) ) ? false : $data[trim($matches[1])];

								@$result = (!$lhsValue) ? TRUE : FALSE;
//								$result = ($lhsValue === false ) ? FALSE : TRUE;
//
							}
							// split condition string into lhs, operator. rhs value for comparison
							elseif (preg_match("/([a-z0-9\-_:\(\)]+)(\!=|=|==|>|<|>=|<=)([a-z0-9\-_\/]+)/", $condString, $matches)) {

//								$lhsValue = (@!$data[$matches[1]]) ? 0 : $data[trim($matches[1])];
								$lhsValue = isset($data[$matches[1]]) ? $data[$matches[1]] : null;

								$op = $matches[2]; // operator
								$rhs = $matches[3];

								// if lhs value in parse Data is boolean, convert rhs string to bool equivalent
								$rhs = is_bool($lhsValue) ? string_to_bool($rhs) : $rhs;

								if ($op == '==' || $op == '=') {
									@$result = ($lhsValue == $rhs) ? TRUE : FALSE;
								} elseif ($op == '!=') {
									@$result = ($lhsValue != $rhs) ? TRUE : FALSE;
								} elseif ($op == '>') {
									@$result = ($lhsValue > $rhs) ? TRUE : FALSE;
								} elseif ($op == '<') {
									@$result = ($lhsValue < $rhs) ? TRUE : FALSE;
								} elseif ($op == '>=') {
									@$result = ($lhsValue >= $rhs) ? TRUE : FALSE;
								} elseif ($op == '<=') {
									@$result = ($lhsValue <= $rhs) ? TRUE : FALSE;
								}
							} else {
								// condition strgin does not follow pattern
								// check if condition string is a key in the data AND is an array
								if (isset($data[$condString]) && is_array($data[$condString])) {

									// key exists and value is an array
									$result = (count($data[$condString]) > 0) ? TRUE : FALSE;
								} else {
									// key exists and value is a string
									$result = (isset($data[$condString]) && $data[$condString] != '') ? TRUE : FALSE;
								}
							}

							// filter for else
							$innerText = preg_split('/' . $this->l_delim . 'else' . $this->r_delim . '/siU', $innerText);

							if ($result == TRUE) {
								// evaluation = true
								// show inner text in the if section
								$template = str_replace($outerText, $innerText[0], $template);
							} else {
								// evaluation = false
								// show inner text in the else section OR
								// delete inner text if {else} does not exist

								if (is_array($innerText)) {

									// set string as {else} section ( if exists ) OR set as '' is {else} does NOT exist
									$innerText = (isset($innerText[1])) ? $innerText[1] : '';
									$template = str_replace($outerText, $innerText, $template);
								} else {

									// else does not exist, simply DELETE innertext
									$template = str_replace($outerText, '', $template);
								}
							}
						} else {

							// invalid outerText OR innerText OR conditions not provided
							// ignore string
						}
					} else {
						// remove any conditionals we cant process
						$template = str_replace($outerText, '', $template);
					}
				}
			}

		}

		return $template;
	}

	/**
	 * Simple search and replace for all variable type parseTags
	 *
	 * @param string $template	template to parse
	 * @param array  $variablesData all variable type parse tags ONLY
	 *
	 * @return string partially parsed template
	 */
	private function _render_variables($template, $variablesData) {

		if (is_array($variablesData)) {

			foreach ($variablesData as $key => $val) {

				$template = $this->_parse_single($key, (string) $val, $template);
			}
		}

		return $template;
	}

	/**
	 * rendering all block type parseTag	 *
	 *
	 * @param string $template	template to parse
	 * @param array  $blocksData all block type parse tags ONLY
	 *
	 * @return string parsed template
	 */
	private function _render_blocks($template, $blocksData) {

		if (is_array($blocksData)) {

			foreach ($blocksData as $key => $data) {

				$template = $this->_parse_pair($key, $data, $template);
			}
		}

		return $template;
	}

	/**
	 * Parse a tag pair {some_tag} string... {/some_tag}
	 *
	 * performs recurssive rendering for all block type data
	 * steps in _parse should be repeated here to handle child elements
	 *
	 * @access	private
	 * @param	string	$variable
	 * @param	array	$data
	 * @param	string	$string
	 *
	 * @return	string
	 */
	function _parse_pair($variable, $data, $string) {

		if (FALSE === ($matches = $this->_match_pairs($string, $variable))) {
			return $string;
		}

		if ($matches !== false) {

			// in case of multiple occurances of same block keyword
			// Eg. {posts}..ABC..{/posts} . and . .{posts}..XYZ..{/posts}
			foreach ($matches as $count => $match) {

				$str = '';
				foreach ($data as $row) {

					// note: this is only the sub-section of the template, without {block_name}{/block_name}
					$template = $match[1];

					$template = $this->_render($template, $row);

					$str .= $template;
				}

				$string = str_replace($match[0], $str, $string);
			}
		}

		return $string;
	}

	/**
	 *  Matches a variable pair
	 * ( supports multiple instances of current pair(block) in view file )
	 * note: original CI method _match_pair is NOT required anymore
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	mixed
	 */
	function _match_pairs($string, $variable) {

		// "/{variable}(.+){\/variable}/sU"
		// note: /U modifier is required to enable unGreedy matches, this will prevent overlapping scans
		$expression = "/" . preg_quote($this->l_delim) . $variable . preg_quote($this->r_delim) . "(.+)" . preg_quote($this->l_delim) . '\/' . $variable . preg_quote($this->r_delim) . "/sU";
//		$expression = "/" . $this->l_delim . $variable . $this->r_delim . "(.+)" . $this->l_delim . '\/' . $variable . $this->r_delim . "/sU";

		if (!preg_match_all($expression, $string, $matches, PREG_SET_ORDER)) {

			return FALSE;
		}

		return $matches;
	}

}
// END Parser Class
