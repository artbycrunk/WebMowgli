<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of import_site_lib
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
/* * ************** Settings Start *************************************** */

define('SLUG_SEPARATOR', '-'); // alternate: 'underscore'

/* * ************** Settings End *************************************** */

define('METATAGS_TITLE', 'title');
define('METATAGS_DESCRIPTION', 'description');
define('METATAGS_KEYWORDS', 'keywords');

define('EXTRACT_TAG_IDENTIFIER', 'extract');
define('EXTRACT_TAG_ATTRIBUTE_BLOCK', 'block');
define('EXTRACT_TAG_ATTRIBUTE_REUSE', 'reuse');

define('PARSE_TAG_SEPERATOR', ':');

define('LINK_RESOURCE_PARSE_TAG', '{site:resource}' . '/');   // note: trailing slash

define('EXTRACT_TYPE_TEMPLATE', 'template');
define('EXTRACT_TYPE_PAGE', 'page');
define('MODULE_EXTRACT_CONTENT', '_extract_content');

define('STRING_TO_ARRAY_DELIMITER', '|');
define('RESOURCE_TAG_STRING', "img|link|script|input");
define('RESOURCE_EXTERNAL_URL_MATCHES', "http://|https://|www");

//define('TAG_HEAD', "{head}");

class import_site_lib {

	private $ci;
	private $configFile = "import_config";
	private $module = "import";

	public function __construct() {

		$this->ci = & get_instance();

		// load libraries
		$this->ci->load->library('domparser');
		$this->ci->load->library('notifications');

		// Load helpers
		$this->ci->load->helper('html');

		// load model
		$this->ci->load->model('import/import_model');

		// load configs files
		$this->ci->config->load($this->configFile, true, true, $this->module);

		!defined('ALLOW_IMPORT_ERRORS') ? define('ALLOW_IMPORT_ERRORS', $this->ci->config->item('allow_import_errors', $this->configFile)) : null;
		!defined('TAG_META_TITLE') ? define('TAG_META_TITLE', $this->ci->config->item('tag_meta_title', $this->configFile)) : null;
		!defined('TAG_META_DESCRIPTION') ? define('TAG_META_DESCRIPTION', $this->ci->config->item('tag_meta_description', $this->configFile)) : null;
		!defined('TAG_META_KEYWORDS') ? define('TAG_META_KEYWORDS', $this->ci->config->item('tag_meta_keywords', $this->configFile)) : null;

		!defined('MODULE_INCLUDES') ? define('MODULE_INCLUDES', $this->ci->config->item('module_includes', $this->configFile)) : null;
	}

	/**
	 * Imports templates listed in given file array.
	 *     Checks ALLOW_IMPORT_ERRORS and accordingly writes to database if there are errors or not
	 *     Sets suitable error, warning, info, success messaages in global arrays of base class
	 * Scans file/html using dom parser
	 * adds mandatory tags (title, description, keywords) if not exists
	 * modifies resource links to point to root resource folder
	 * calls respective extracting functions for given modules
	 *     according to parse tags in id attribute of extract tag
	 * comits or rollback database transactions according to ALLOW_IMPORT_ERRORS OR isSuccess for each file
	 *
	 * @param array $filesArray[][] array of files to be processed file details are required are
	 *        [0] =>
	 *          'filename' => 'about-us',
	 *          'filename_full' => 'about-us.html',
	 *          'filepath' => 'http://somesite.com/some/path/about-us.html',
	 *          'extension' => 'html',
	 *          'file_html' => '<head><title>ashads . ... ..</html>'
	 *
	 * @return bool $finalSuccess Returns true if NO errors at all, returns false if one or more errors found
	 */
	public function import($tempData, $htmlMain, & $db, $isCreatePage = false) {

		$finalSuccess = true;

		//$adminObj = & get_instance_admin();
//                $this->ci->load->library('notifications');
		$this->ci->import_model->set_db($db);

		/* process mandatory tags (title, description, keywords)
		 *  if they do not exist, create blank values */
		$pageMeta = $this->add_mandatory_tags($htmlMain);

		/* add suitable parseTag before resource links */
		$this->edit_resource_links($htmlMain);

		$tempData['temp_html'] = $htmlMain;
		$tempNameOriginal = $tempData['temp_name'];
		$tempData['temp_name'] = $this->get_available_temp_name($tempNameOriginal, $tempData['temp_type'], $db);
		$tempId = $this->ci->import_model->create_template($tempData);

		// remove '{' and '}' from all tags, also convert id attribute to lowercase
		$this->_sanitize_extract_tags($htmlMain);

		$includeSuccess = $this->extract_includes($htmlMain, $db, $tempId, $isCreatePage);

		/* process extract tags ( Eg. <extract id='some:text' > ), call respective modules to process content */
		$tempSuccess = $this->extract_content($htmlMain, $db, $tempId, $isCreatePage);



		/**
		 * @todo scan for manual parse tags {} and add to tags table
		 * - scan for tags with below conditions
		 *      - starts with '{alpha_num_chars'
		 *      - ends with '}'
		 *      - atleast 1 colon ( : ) in between
		 *      - any one of these text types ( alpha-num, :, #, _, -, / ) in between : and last }
		 *      - first element is NOT 'block' or 'Block'
		 *
		 * check if module exists for first word between { and :  Eg. includes OR content
		 *  NO-> remove tag from array
		 * create tags in tags table for remaining found array elements
		 */
		$tempData['temp_html'] = $htmlMain;
		$tempData['temp_modified'] = get_gmt_time();

		//if( ( $tempSuccess AND $includeSuccess ) OR ALLOW_IMPORT_ERRORS ){
		if ($tempSuccess OR ALLOW_IMPORT_ERRORS) {

			/* DATABASE - create template in database */

			$tempDb = array(
			    //    'temp_id' => null,
			    //    'temp_name' => $file[ FILE_ARRAY_FILENAME ],
			    //    'temp_type' => WM_TEMPLATE_TYPE_PAGE, // TEMPLATE_TYPE = page
			    'temp_html' => $tempData['temp_html'], // created later in import_site_lib->import() function
			    //    'temp_created' => get_gmt_time(),
			    'temp_modified' => $tempData['temp_modified'],
				//    'temp_description' => null, // null - since template imported from zip file
				//    'temp_is_visible' => true
			);

			/* Write template to database */
			if ($this->ci->import_model->edit_template_by_id($tempDb, $tempId) == true) {

				//$this->ci->import_model->transaction_commit();
				//$adminObj->set_success("Template '" . $tempData['temp_name'] . "' successfully created");
				$this->ci->notifications->add('success', "Template '" . $tempData['temp_name'] . "' successfully created");

				if ($isCreatePage) {
					// create page automatically from templates
					$pageId = $this->create_page_by_name($tempNameOriginal, $tempId, $pageMeta, $db);

					//      Create actual page content, creating page blocks will create all necessary data for pages
					if ($this->ci->import_model->create_page_blocks_from_template($pageId, $tempId)) {

						//$adminObj->set_success("Page '" . $tempData['temp_name'] . "' successfully created");
						$this->ci->notifications->add('success', "Page '" . $tempData['temp_name'] . "' successfully created");
						$finalSuccess = true;
					} else {
						//$adminObj->set_error("Some error occured while creating Page '" . $tempData['temp_name'] . "' in database");
						$this->ci->notifications->add('error', "Page '" . $tempData['temp_name'] . "' NOT created - error(s) occured while creating page in database");
						$finalSuccess = false;
					}
				}
			} else {
				//$this->ci->import_model->transaction_rollback();
				//$adminObj->set_error("Template '" . $tempData['temp_name'] . "' NOT created - error(s) occured while creating template in database");
				$this->ci->notifications->add('error', "Template '" . $tempData['temp_name'] . "' NOT created - error(s) occured while creating template in database");
				$finalSuccess = false;
			}
		} else {
			// NOT IMPORTED
			//$this->ci->import_model->transaction_rollback();
			//$adminObj->set_error("Template '" . $tempData['temp_name'] . "' NOT created - error(s) were found in the template html");
			$this->ci->notifications->add('error', "Template '" . $tempData['temp_name'] . "' NOT created - error(s) were found in the template html");

			$finalSuccess = false;
		}

//        } // end foreach
		$this->ci->notifications->save();

		return $finalSuccess;
	}

	/**
	 * scans through file array and returns a detailed file array or name, filetype, path
	 * @param array $files string array of all uploaded files
	 * @return array returns associative array of pages and resources --> array('pages', 'resources')
	 */
	public function seperate_pages_resources($files = array()) {

		$fileDetails = array('pages', 'resources');

		$pageFilesArray = array(); // will hold all page file details
		$resourceFilesArray = array(); // will hold all resource file details

		/* Run through uploaded files */
		foreach ($files as $file) {

			$tempFileData = pathinfo($file);

			if (isset($tempFileData['extension']) AND isset($tempFileData['filename'])) {

				/* check if file is html / htm / php */
				if (in_array($tempFileData['extension'], explode(FILETYPE_DELIMITER, PAGE_FILETYPES))) {
					/* template / page file --> perform extract processes */

					$pageFilesArray[] = array(
					    FILE_ARRAY_FILENAME => $tempFileData['filename'], // Eg. test
					    FILE_ARRAY_FILENAME_FULL => $tempFileData['basename'], // eg. test.css
					    FILE_ARRAY_FILETYPE => $tempFileData['extension'], // eg. css
					    FILE_ARRAY_FILEPATH => realpath($file) // eg c:\ . . / / /.  . .. test.css
					);
				} else {
					/* resource file --> save details in array for database */
					$resourceFilesArray[] = array(
					    FILE_ARRAY_FILENAME => $tempFileData['filename'],
					    FILE_ARRAY_FILENAME_FULL => $tempFileData['basename'],
					    FILE_ARRAY_FILETYPE => $tempFileData['extension'],
					    FILE_ARRAY_FILEPATH => realpath($file)
					);
				}
			} else {

				// error invalid resource file ( either filename or extension NOT found )
				// ignore current file

				$this->ci->notifications->add('warning', "Invalid file ignored : " . $tempFileData['basename']);
			}


			unset($tempFileData);
		}

		$fileDetails['pages'] = $pageFilesArray;
		$fileDetails['resources'] = $resourceFilesArray;

		$this->ci->notifications->save();

		return $fileDetails;
	}

	/**
	 * scans through all extract tags and converts id attirbute to
	 * lowercase
	 * strips { and } from tag
	 * returns status true/false
	 */
	private function _sanitize_extract_tags(& $htmlMain) {

		$isSuccess = true;

		//$adminObj = & get_instance_admin();
//                $this->ci->load->library('notifications');

		$dom = $this->ci->domparser->str_get_html($htmlMain);
		$extractTags = $dom->find(EXTRACT_TAG_IDENTIFIER);

		if (count($extractTags) > 0) {
			/* run through each tag */
			foreach ($extractTags as $div) {

				/* Check if 'id' attribute present */
				if ($div->hasAttribute('id')) {

					$id = $div->getAttribute('id');
					// remove curly braces from id and convert to lowercase
					$parseTagString = strtolower(str_replace(array('{', '}'), '', $id));
					$div->setAttribute('id', $parseTagString);
				} else {
					// extract tag found but id attribute NOT found
					$isSuccess = false;
					//$adminObj->set_error("NO 'id' attribute found in extract tag.");
					$this->ci->notifications->add('error', "NO 'id' attribute found in extract tag.");
				}
			}
		} else {
			// no extract tags found
			$isSuccess = false;
		}

		// save modified dom in main ( referanced ) Main html
		$htmlMain = $dom->save();

		$dom->clear();
		unset($dom);

		$this->ci->notifications->save();

		return $isSuccess;
	}

	public function process_page_template($tempDb, & $htmlMain, & $db, $tempId, $isCreatePage = false) {

		$success = false;

		$this->ci->import_model->set_db($db);

		/* add suitable parseTag before resource links */
		$this->edit_resource_links($htmlMain);

		// remove '{' and '}' from all tags, also convert id attribute to lowercase
		$this->_sanitize_extract_tags($htmlMain);

		$includeSuccess = $this->extract_includes($htmlMain, $db, $tempId, $isCreatePage);

		/* process extract tags ( Eg. <extract id='some:text' > ), call respective modules to process content */
		$tempSuccess = $this->extract_content($htmlMain, $db, $tempId, $isCreatePage);

		// check if includes and content extraction was successfull
		if ($includeSuccess == true AND $tempSuccess = true) {

			$tempDb['temp_html'] = $htmlMain;

			// write update template in db
			if ($this->ci->import_model->edit_template_by_id($tempDb, $tempId) == true) {


//                        $this->ci->notifications->add('success', "Template '" . $tempData['temp_name'] . "' successfully created");

				if ($isCreatePage) {
					// create page automatically from templates
					$pageId = $this->create_page_by_name($tempData['temp_name'], $tempId, $pageMeta, $db);

					//      Create actual page content, creating page blocks will create all necessary data for pages
					if ($this->ci->import_model->create_page_blocks_from_template($pageId, $tempId)) {

						//$adminObj->set_success("Page '" . $tempData['temp_name'] . "' successfully created");
//                                        $this->ci->notifications->add('success', "Page '" . $tempData['temp_name'] . "' successfully created");
						$finalSuccess = true;
					} else {
						//$adminObj->set_error("Some error occured while creating Page '" . $tempData['temp_name'] . "' in database");
//                                        $this->ci->notifications->add('error', "Page '" . $tempData['temp_name'] . "' NOT created - error(s) occured while creating page in database");
						$finalSuccess = false;
					}
				}

				$success = true;
			} else {
				// template could not be saved, update failed

				$success = false;
			}
		} else {
			// error while creating includes or extracting content

			$success = false;
		}

		return $success;
	}

	public function process_include(& $html, & $db, $includeId, $isCreatePage) {

		$success = false;

		$this->extract_content($html, $db, $includeId, $isCreatePage);

		$tempDb = array(
		    //    'temp_id' => null,
		    //    'temp_name' => $file[ FILE_ARRAY_FILENAME ],
		    //    'temp_type' => WM_TEMPLATE_TYPE_PAGE, // TEMPLATE_TYPE = page
		    'temp_html' => $html, // created later in import_site_lib->import() function
		    //    'temp_created' => get_gmt_time(),
		    'temp_modified' => get_gmt_time(),
			//    'temp_description' => null, // null - since template imported from zip file
			//    'temp_is_visible' => true
		);

//                $this->ci->load->model('import/import_model');
		$this->ci->import_model->set_db($db);

		/* Edit newly created include in database */
		$success = $this->ci->import_model->edit_template_by_id($tempDb, $includeId);

		return $success;
	}

	public function extract_includes(& $htmlMain, & $db, $tempId, $isCreatePage) {

		/* final success/failure status for current page or template */
		$isSuccess = true;

		//$adminObj = & get_instance_admin();
//                $this->ci->load->library('notifications');

		$dom = $this->ci->domparser->str_get_html($htmlMain);
		/* find all extract tags with id starts with 'includes:' */
		$domSearchString = EXTRACT_TAG_IDENTIFIER . "[id^=includes:]";
		$includesTags = $dom->find($domSearchString);

		$blocksDb = array();
		$includeData = array();

		/* Check if includes tags found, if NOT found set $isSuccess = true */
		if (is_array($includesTags) AND count($includesTags) > 0) {

			// will set suitable block names ONLY for include tags ( all <extract id='includes:....'> tags )
			$this->_set_unique_block_names($dom, $domSearchString, $tempId, $db);

//                        $this->ci->load->model('import/import_model');
			$this->ci->import_model->set_db($db);

			/* run through each (include) tag, extract content for each include */
			foreach ($includesTags as $div) {

				//      Check if id attribute present for current tag
				if ($div->hasAttribute('id')) {

					$parseTagString = $div->getAttribute('id');
					$tempCount = 1; // number of times to replace 'includes:' in string_replace()
					$includeName = str_replace("includes:", '', $parseTagString, $tempCount);

					$existingTempId = $this->ci->import_model->get_template_id_from_name($includeName, WM_TEMPLATE_TYPE_INCLUDE);

					$includeTagId = null;
					$innerTextInclude = $div->innertext;

					$includeData = array(
					    'temp_id' => null,
					    'temp_name' => $includeName,
					    'temp_type' => WM_TEMPLATE_TYPE_INCLUDE, // WM_TEMPLATE_TYPE_INCLUDE = include
					    'temp_html' => $innerTextInclude,
					    'temp_created' => get_gmt_time(),
					    //    'temp_modified' => null,
					    'temp_description' => null, // null - since includes dont have user descriptions
					    'temp_is_visible' => true
					);


					//      Check if temp name Does NOT exists
					if ($existingTempId === false) {
						// temp name Does NOT exist
						// insert template ( type=includes ) into templates table
						$includeId = $this->ci->import_model->create_template($includeData);
						//$parseTagString = "includes:$includeName";

						$includeSuccess = $this->process_include($innerTextInclude, $db, $includeId, $isCreatePage);


						$tagDb = array(
						    'tag_id' => null,
						    'tag_module_name' => MODULE_INCLUDES,
						    'tag_temp_id' => $includeId,
						    'tag_keyword' => $parseTagString,
						    'tag_name' => $parseTagString,
							//    'tag_data_id' => null,
							//    'tag_description' => null
						);
						// create tag for current include (Eg. {includes:header} )
						$includeTagId = $this->ci->import_model->create_tag($tagDb);
					} else {
						// temp name exists
						// check if reuse = true/false
						// true --> link to existing temp id
						// false --> rename current temp, create new, warning

						if ($div->hasAttribute(EXTRACT_TAG_ATTRIBUTE_REUSE)) {
							// link to existing template
							$includeTagId = $this->ci->import_model->get_tag_id_for_include($existingTempId, $parseTagString, MODULE_INCLUDES);
						} else {

							// rename, create new, set warning
							$newTempName = $this->get_available_temp_name($includeName, WM_TEMPLATE_TYPE_INCLUDE, $db);
							$includeData['temp_name'] = $newTempName;
							$includeId = $this->ci->import_model->create_template($includeData);
							$message = "Duplicate include found ('$includeName'), renaming current include to '$newTempName'";
							$this->ci->notifications->add('warning', $message);

							$includeSuccess = $this->process_include($innerTextInclude, $db, $includeId, $isCreatePage);

							$tagDb = array(
							    'tag_id' => null,
							    'tag_module_name' => MODULE_INCLUDES,
							    'tag_temp_id' => $includeId,
							    'tag_keyword' => "includes:$newTempName",
							    'tag_name' => "includes:$newTempName",
							);
							// create tag for current include (Eg. {includes:header} )
							$includeTagId = $this->ci->import_model->create_tag($tagDb);
						}
					}

					$includeBlockName = $div->getAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK);
					$div->outertext = "{block:" . $includeBlockName . "}";

					$blocksDb[] = array(
					    'block_id' => null,
					    'block_temp_id' => $tempId, // change : $includeId,
					    'block_name' => $includeBlockName,
					    'block_tag_id' => $includeTagId
					);
				} else {
					// include tags present, but id attribute not provided
					$this->ci->notifications->add('error', "No id attribute found for include tag '" . $includeData['temp_name'] . "'");
					$isSuccess = false;
				}
			}  // end foreach

			$isSuccess = $this->ci->import_model->create_blocks($blocksDb);

			if (!$isSuccess) {

				$this->ci->notifications->add('error', "Some error occured while trying to creating blocks for include '" . $includeData['temp_name'] . "'");
			}
		} else {
			// no include tags found
			$isSuccess = true;
		}

		// save modified dom in main ( referanced ) Main html
		$htmlMain = $dom->save();

		$dom->clear();
		unset($dom);

		$this->ci->notifications->save();

		return $isSuccess;
	}

	/**
	 * @todo correct description for extract_content()
	 *
	 * Extracts content from given template/page
	 * Searches for <extract></extract> tags in html DOM.
	 * processes 'id' and 'innerHtml' of tags
	 * calls respective modules extract_content function to process tags
	 * replaces outerHtml in DOM with suitable text returned from module function
	 * if error found in module function returns false
	 * If any errors found during entire process,
	 *  suitable errors, warnings, infos, success messages set in main base instance
	 *
	 * @param string &$htmlMain html of current template/include to be prcessed for extract tags
	 * @param int &$dbId database id of current template/include/ being processed
	 * @param int $tempId id of template/page
	 * @param bool $isCreatePage whether a page is being automatically created for this extract or not.
	 *
	 * @return bool true on success, false if atleast 1 error found
	 */
	public function extract_content(& $htmlMain, & $db, $tempId, $isCreatePage) {

		/* final success/failure status for current page or template */
		$isSuccess = true;

//                $this->ci->load->library('notifications');

		$dom = $this->ci->domparser->str_get_html($htmlMain);
		/* find all extract div tags ( <div class='extract' ) */
		$extractTags = $dom->find(EXTRACT_TAG_IDENTIFIER);

		/* Check if extract tags found */
		if (count($extractTags) > 0) {

			$this->_set_unique_block_names($dom, EXTRACT_TAG_IDENTIFIER, $tempId, $db);

			$blocksDb = array();

			/* run through each extract tag */
			foreach ($extractTags as $div) {

				/* Check if 'id' attribute present */
				if ($div->hasAttribute('id')) {

					$id = $div->getAttribute('id');

					$innerText = $div->innertext;

					/* Remove '{' and '}' from parseTag
					 * split parse tag at ':' into an array */
					$parseTagString = strtolower(str_replace(array('{', '}'), '', $id));
					$parseTagArray = explode(PARSE_TAG_SEPERATOR, $parseTagString);

					$module = $parseTagArray[0];

					/* Check if module exists --> ( $this->ci->load->module ) returns null if NOT exists */
					if (!is_null($this->ci->load->module("$module/admin/$module"))) {

						/* module found process extract tag */

						$method = MODULE_EXTRACT_CONTENT;

						/* Call respective module extract function, returns tagId OR false/null on failure */

						$tagId = $this->ci->$module->$method($tempId, $parseTagString, $innerText, $db);

						/* replace outer text if return $tagId is NOT false or null */
						if (( $tagId != false ) OR (!is_null($tagId) )) {

							// block processed successfully, create block in db

							/* Block names were forcefully created using _set_unique_block_names */

							$blockName = $div->getAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK);

							$div->outertext = "{block:" . $blockName . "}";

							$blocksDb[] = array(
							    'block_id' => null,
							    'block_temp_id' => $tempId,
							    'block_name' => $blockName,
							    'block_default_html' => $div->innertext, // save original html for backup
							    'block_tag_id' => $tagId
							);
						} else {
							/* note: although error found, continuing extract with remaining tags,
							 * database can be reverted from main function if needed */
							//$adminObj->set_error("Error occured while processing parse tag '{" . $parseTagString . "}'");
							$isSuccess = false;
						}
					} else {
						/* module does not exist Or Invalid parse Tag found */
						//$adminObj->set_error("Invalid parse tag '{" . $parseTagString . "}' found");
						$this->ci->notifications->add('error', "Invalid parse tag '{" . $parseTagString . "}' found");
						$isSuccess = false;
					}
				} else {
					/* id attribute NOT found in current extract tag */
					//$adminObj->set_error("NO 'id' attribute found in extract tag.");
					$this->ci->notifications->add('error', "NO 'id' attribute found in extract tag.");
					$isSuccess = false;
				}
			} // end foreach
//                        $this->ci->load->model('import/import_model');
			$this->ci->import_model->set_db($db);

			$this->ci->import_model->create_blocks($blocksDb);

			/**
			 * @todo create pages_blocks in database
			 */
			$isSuccess = true;
		} else {
			/* No extract tags found in template provided */

			// $isSuccess = false; // ORIGINAL
			$isSuccess = true; // changed by lloyd ( 6th March, 2012 ) - reason: NOT mandatory for includes or templates to have blocks
		}

		// save modified dom in main ( referanced ) Main html
		$htmlMain = $dom->save();

		$dom->clear();
		unset($dom);

		$this->ci->notifications->save();

		return $isSuccess;
	}

	private function get_slug_name($text) {

		$text = url_title($text, SLUG_SEPARATOR, true);

		// Commented due to upgrade to CI ver 2.1.3
//		if (SLUG_SEPARATOR == '_') {
//
//			$text = str_replace('_', '-', $text);
//		}

		return $text;
	}

	/**
	 * Uses parsed dom object, searches for title and meta tags
	 * if present saves inner values in tag array (for return)
	 * if not present creates tag elements in dom
	 * $tags returns either actual tag content OR blank values
	 *
	 * @param object dom
	 * @return array tags (title, description, keywords)
	 */
	public function add_mandatory_tags(& $htmlMain) {

		$dom = $this->ci->domparser->str_get_html($htmlMain);

		//$adminObj = & get_instance_admin();
//                $this->ci->load->library("notifications");


		$isTagFound = array(
		    METATAGS_TITLE => false,
		    METATAGS_DESCRIPTION => false,
		    METATAGS_KEYWORDS => false,
		);

		$tags = array(
		    METATAGS_TITLE => null,
		    METATAGS_DESCRIPTION => null,
		    METATAGS_KEYWORDS => null,
		);

		$meta = array();


		// title
		$titleTag = $dom->find('title', 0);

		if (isset($titleTag)) {
			$tags[METATAGS_TITLE] = $titleTag->innertext;
			$isTagFound[METATAGS_TITLE] = true;
			$titleTag->outertext = '';
		}
		unset($titleTag);

		$metaTags = $dom->find('meta');

		/* Check if meta present */
		foreach ($metaTags as $tag) {

			$name = strtolower($tag->getAttribute('name'));

			/* if content found for tag save content in $tags array --> replace content with parse tags */

			if ($name == 'description' AND $isTagFound[METATAGS_DESCRIPTION] == false) {
				$tags[METATAGS_DESCRIPTION] = $tag->getAttribute('content');
				$tag->setAttribute('content', TAG_META_DESCRIPTION);
				$isTagFound[METATAGS_DESCRIPTION] = true;
			}

			if ($name == 'keywords' AND $isTagFound[METATAGS_KEYWORDS] == false) {
				$tags[METATAGS_KEYWORDS] = $tag->getAttribute('content');
				$tag->setAttribute('content', TAG_META_KEYWORDS);
				$isTagFound[METATAGS_KEYWORDS] = true;
			}
		}
		unset($metaTags);

		/* add tags if not present */
		if ($isTagFound[METATAGS_DESCRIPTION] == false) {
			$meta[] = array('name' => 'description', 'content' => TAG_META_DESCRIPTION);
		}
		if ($isTagFound[METATAGS_KEYWORDS] == false) {
			$meta[] = array('name' => 'keywords', 'content' => TAG_META_KEYWORDS);
		}

		$tagTitle = "\n <title>" . TAG_META_TITLE . "</title> \n";


//                $this->ci->load->helper('html');

		$head = $dom->find('head', 0);

		/* Check for <head> tags */
		if (!isset($head)) {
			/* NO <head> tags found */
			$htmlTag = $dom->find('html', 0);
			if (!isset($htmlTag)) {
				/* <html> tag NOt found */
				//$adminObj->set_error("No <html> tags found in template");
				$this->ci->notifications->add('error', "No <html> tags found in template");
			} else {
				/* <html> tag found --> adding head and meta */
				//$htmlTag->innertext = "\n" . "<head> " . $tagTitle . meta($meta) . TAG_HEAD . "\n </head>" . $htmlTag->innertext;
				$htmlTag->innertext = "\n <head> " . $tagTitle . meta($meta) . "\n </head>" . $htmlTag->innertext;
			}
		} else {

			// <head> tag found adding meta

			$addText = "";
			$addText .= $tagTitle;
			$addText .= meta($meta) . "\n";
			//$addText .= TAG_HEAD . "\n";
			// TAG_HEAD = {head} --> for inserting text in page head at viewing time
			$head->innertext = $addText . $head->innertext;
		}

		// save modified dom in main ( referenced ) Main html
		$htmlMain = $dom->save();
		$this->ci->notifications->save();

		$dom->clear();
		unset($dom);

		return $tags;
	}

	/**
	 * Only Template ( $isCreatePage = false ) --> assume template at root folder
	 * list of required html tags ( link, img, script, . . . . )
	 * - search all href, src
	 * - run through all
	 * - check if among tagList
	 *      no -> continue
	 *      yes -> check if http or https or www
	 *          yes -> continue, no change
	 *          no -> replace ../.... till text with {site:resource}
	 */
	public function edit_resource_links(& $htmlMain) {
		/**
		 * @todo process resource links for page remove . ./ ../ /../ . . etc
		 * --> remove  / , ./ , ../, from start
		 */
		$dom = $this->ci->domparser->str_get_html($htmlMain);

		/* get list of tags whose links have to be searched from settings */
		$tagList = explode(STRING_TO_ARRAY_DELIMITER, RESOURCE_TAG_STRING);

		/* get list of prefixes for urls that should NOT be modified
		 * eg http, https, www, {site:resource} */
		$urlStartMatches = $this->_get_url_prefix_exceptions();

		$searchAttribs = array(
		    'href' => $dom->find('[href]'),
		    'src' => $dom->find('[src]')
		);

		foreach ($searchAttribs as $attrib => $tagObj) {

			foreach ($tagObj as $tag) {

				if (in_array($tag->tag, $tagList)) {

					if (!$this->_string_starts_with($tag->getAttribute($attrib), $urlStartMatches)) {

						$relativeUri = $tag->getAttribute($attrib);

						// remove any . and / and \ chars at beginning of string till alphy-num char OR _ or - is found
						$pattern = "/^(\.|\/|\\\)*([a-zA-Z0-9_\-\/])(.*)$/i";
						$replacement = '${2}${3}';
						$relativeUri = preg_replace($pattern, $replacement, $relativeUri);

						// remove slash.
						$relativeUri = trim($relativeUri, '/');

						// do not modify resources if src or href begins with '{' OR 'http' OR 'www' --> to handle {}
						if (!preg_match("/^({|http|www)/i", $relativeUri)) {
							$tag->setAttribute($attrib, LINK_RESOURCE_PARSE_TAG . $relativeUri);
						}


//                        $value = $tag->getAttribute( $attrib );
//                        echo $value;
					} else {
						/**
						 * @todo http OR https OR www found at start of resource url
						 * use parse_url() to get host name and perform necessary steps.
						 */
					}
				}
			}
		}

		// save modified dom in main ( referanced ) Main html
		$htmlMain = $dom->save();

		$dom->clear();
		unset($dom);
	}

	// gets all exceptions for href OR src OR link where url prefix should NOT be modified.
	private function _get_url_prefix_exceptions() {

		$exceptions = explode(STRING_TO_ARRAY_DELIMITER, RESOURCE_EXTERNAL_URL_MATCHES);

		// remove trailing slash, add '{site:resource}' to exceptions list
		$exceptions[] = rtrim(LINK_RESOURCE_PARSE_TAG, '/');

		return $exceptions;
	}

	/**
	 * Case insensitive search to check if array elements are present at start of string (haystack)
	 * @param array $needle List of start to elements to search FOR
	 * @param string $haystack string to search in
	 * @return bool true if found at start of string, false if NOt.
	 */
	private function _string_starts_with($haystack = null, $needle = array()) {

		$isFound = false;
		foreach ($needle as $value) {
			if (substr(strtolower($haystack), 0, strlen($value)) == strtolower($value)) {
				$isFound = true;
				break;
			}
		}
		return $isFound;
	}

	/*	 * ********************************* to DELETE ***************************************************************** */

	/**
	 * checks if a page is existing in the database using pagename
	 * if present retreives pageId
	 * if NOT present creates new page and returns new id
	 *
	 * @param string $pageName page name
	 * @param array $meta array of page meta data array( 'title', 'description', 'keywords' )
	 * @return int id of page
	 */
	public function create_page_by_name($pageName, $tempId, $meta, & $db) {

//                $this->ci->load->model('import/import_model');
		$this->ci->import_model->set_db($db);

		$pageSlug = $this->get_slug_name($pageName);
		$pageUri = $pageSlug;

		$pageNames = $this->ci->import_model->get_pages_starts_with($pageName, $pageSlug, $pageUri);

		//      If similar page names exist, create new page name Eg. page-1, page-2
		if (!is_null($pageNames) AND count($pageNames) > 0 AND in_array($pageName, $pageNames)) {
			/* set proper offset for Page name */

			$offset = 0;
			$tempPageName = $pageName;

			while (in_array($tempPageName, $pageNames)) {

//				$offset += 1;
//				$tempPageName = $pageName . SLUG_SEPARATOR . $offset;
				$tempPageName = increment_string($tempPageName, SLUG_SEPARATOR);
			}

//			do {
//				$offset += 1;
//				$tempPageName = $pageName . SLUG_SEPARATOR . $offset;
//			} while (in_array($tempPageName, $pageNames));

			$pageName = $tempPageName;
		}

		$pageSlug = $this->get_slug_name($pageName);
//		$pageUri = $pageSlug;

		$dataArray = array(
		    //   'page_id' => null,
		    'page_temp_id' => $tempId,
		    'page_name' => $pageName,
		    'page_slug' => $pageSlug,
		    //'page_uri' => $pageUri,
		    'page_html' => null,
		    'page_title' => $meta[METATAGS_TITLE],
		    'page_description' => $meta[METATAGS_DESCRIPTION],
		    'page_keywords' => $meta[METATAGS_KEYWORDS],
		    'page_created' => get_gmt_time(),
		    'page_modified' => get_gmt_time(),
		    'page_is_visible' => 1
		);

		$pageId = $this->ci->import_model->create_page($dataArray);

		return $pageId;
	}

	/**
	 * Checks if given name exists in db
	 * if yes --> modifies name till name does not exists (Eg. name_1, name_2 )
	 * returns available name.
	 */
	private function get_available_temp_name($checkName, $tempType, & $db) {

		$availableName = null;

		$this->ci->import_model->set_db($db);
		$dbNames = $this->ci->import_model->get_templates_starts_with($checkName, $tempType);

		if (is_array($dbNames) AND in_array($checkName, $dbNames) AND count($dbNames) > 0) {

			// name matches atleast 1 existing template name
			// set proper offset for template name

			$offset = 0;
			$tempName = $checkName;

			while (in_array($tempName, $dbNames)) {
				$tempName = increment_string($tempName, SLUG_SEPARATOR);
			}

			$availableName = $tempName;
		} else {
			// name does not match any existing template, use original name
			$availableName = $checkName;
		}

		return $availableName;
	}

	/**
	 * finds all blocks in dom
	 * replaces with allowed name in case if name clashes with other name in database
	 * modifies dom by adding block attribute to each extract tags
	 * returns void
	 */
	private function _set_unique_block_names(& $dom, $domSearchString, $tempId = null, & $db = null) {

		$blockNames = array();

		$domTagsArray = $dom->find($domSearchString);

		// get list of block names store in single string array, Also set block attribute in DOM if block not mentioned
		foreach ($domTagsArray as $tag) {

			// Check if id attribute present in extract tag
			if ($tag->hasAttribute('id')) {

				$id = $tag->getAttribute('id');
				$parseString = strtolower(str_replace(array('{', '}'), '', $id));

				$blockName = "";

				// Check if block attribute present in extract tag
				if ($tag->hasAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK)) {

					// block attribute present, get block name
					$blockName = $tag->getAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK);
				} else {
					// block attribute is NOT present, generate block name based on parse string
					//$parseTagArray = explode( PARSE_TAG_SEPERATOR , $parseString, 1 ); // returns only 1st occurance in string (i.e. module )
					//$blockName = $parseTagArray[0];
					$blockName = $parseString;
				}

				$blockNames[] = $blockName;
				$tag->setAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK, $blockName);
			}
		} // end of foreach
		// get existing block names from template

		$existingNames = array();
		if (!is_null($tempId) AND !is_null($db)) {

//                        $this->ci->load->model('import/import_model');
			$this->ci->import_model->set_db($db);

			// get existing blocks from database for current template.
			$existingNames = $this->ci->import_model->get_block_names($tempId);
			$blockNames = !is_null($existingNames) ? array_merge($blockNames, $existingNames) : $blockNames;
		}


		// sort block names in descending order
		arsort($blockNames, SORT_STRING);

		// Get number of occurances of each block name
		$blockNameCounts = array_count_values($blockNames);

		$blockCounter = count($blockNames);
		// run through tags in reverse (to correct only names that repeat and NOT first occurance)
		foreach (array_reverse($domTagsArray, true) as $tag) {

			$blockName = $tag->getAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK);

			// Replace block name with blockName_count if more than one occurance of block
			if ($blockNameCounts[$blockName] > 1) {
				//$lastCount = $blockNameCounts[$blockName] + count( $existingNames );
				$lastCount = $blockCounter;
				$blockCounter -= 1;


				$newBlockName = $blockName . "_" . $lastCount;
				$tag->setAttribute(EXTRACT_TAG_ATTRIBUTE_BLOCK, $newBlockName);
				$blockNameCounts[$blockName] -= 1;
			}
		} // end of foreach

		$success = false; // dummy statement
	}

}

// end of file
?>
