<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pagelibrary
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';
require_once APPPATH . 'libraries/Request.php';
require_once APPPATH . 'libraries/Template_positions.php';

class Page_Library {

        private $ci;

        public function __construct() {
                $this->ci = & get_instance();

                // Load models
                $this->ci->load->model('page/page_model');
        }

        public function render($pageDetails, $params = array()) {

                $position = Template_positions::get_instance();
                $request = Request::get_instance();

                // Check if logged in, set variable for future use.
                $isLoggedIn = $request->get('is_logged');

                // $blocks[][] contains temp_id, block, tag, module, html
                $blocks = $this->ci->page_model->get_blocks_for_page($pageDetails['id']);

                // Process page Blocks if any
                if (is_array($blocks) AND count($blocks) > 0) {

                        $includeBlocks = array();
                        $otherBlocks = array();
                        $includeIds = array();
                        $headHtml = array();    // to store head html required by inner modules.

                        //
                        //  split blocks according to includes and others
                        foreach ($blocks as $block) {

                                if ($block['module'] == 'includes') {
                                        $includeBlocks[] = $block;
                                        $includeIds[] = $block['temp_id'];
                                } else {
                                        //  $sections[moduleName][]
                                        //$moduleBlocks[$block['module']][] = $block;
                                        $otherBlocks[] = $block;
                                }
                        }

                        //  Check if includes present, if yes-> process includes
                        if (count($includeBlocks) > 0) {

                                // includes present



                                $incBean = new Pagebean();
                                $incBean->setPageId($pageDetails['id']);
                                $incBean->setTempId($pageDetails['temp_id']);
                                $incBean->setModule(null);
                                $incBean->setIsLoggedIn($isLoggedIn);
                                $incBean->setHtml($pageDetails['html']); // html of main template
                                $incBean->setParams($params);
                                $incBean->setBlocks($includeBlocks);
                                //$incBean->setHeadHtml( null );


                                //  get [ temp_id, block_temp_id, block, tag, module, html ] of ALL blocks inside each include Block
                                $incBlockContent = $this->ci->page_model->get_blocks_for_templates($includeIds);

                                //  Process include blocks, replace original html with include blocks removed
                                //$incBean = $this->process_includes( $incBean, $includeBlocks );
                                $returnBean = $this->process_includes($incBean, $incBlockContent);

                                // Add/append all head html from All includes into main page head html
                                if (is_array($returnBean->getHeadHtml())) {
                                        $headHtml = array_merge($headHtml, $returnBean->getHeadHtml());
                                }

                                // html partially rendered ( includes ) by include blocks and its children
                                $pageDetails['html'] = $returnBean->getHtml();
                        }

                        //  Check if Other Blocks present, if yes-> process blocks
                        if (count($otherBlocks) > 0) {

                                $pageObj = new Pagebean();
                                $pageObj->setPageId($pageDetails['id']);
                                $pageObj->setTempId($pageDetails['temp_id']);
                                $pageObj->setModule(null);
                                $pageObj->setIsLoggedIn($isLoggedIn);
                                $pageObj->setHtml($pageDetails['html']);  // html of main template ( with include blocks already rendered )
                                $pageObj->setParams($params);
                                $pageObj->setBlocks($otherBlocks);
                                //$pageObj->setHeadHtml( null );

                                $returnBean = $this->process_blocks($pageObj);

                                // Add/append all head html from All includes into main page head html
                                if (is_array($returnBean->getHeadHtml()))
                                        $headHtml = array_merge($headHtml, $returnBean->getHeadHtml());

                                // page html with other blocks ( non-include blocks ) also rendered
                                $pageDetails['html'] = $returnBean->getHtml();
                        }

                        $headHtml = array_unique($headHtml);
//                                $headText = ( count($headHtml) > 0 ) ? ( $headText . "\n" . implode("\n", $headHtml) ) : $headText;

                        $position->add('head', 'start', $headHtml);
                }

                return $pageDetails;
        }

        public function process_includes($incObject, $incBlockContent) {

                $incObj = new Pagebean();
                $incObj = $incObject;

                $includeBlocks = $incObject->getBlocks();

                $parseData = array();
                $includesArray = array();
                $includes = array();

                //  Prepare parse Tag Data for page blocks
                foreach ($includeBlocks as $pageBlock) {

                        $includesArray[$pageBlock['temp_id']]['html'] = $pageBlock['html'];
                        $includesArray[$pageBlock['temp_id']]['parse_tag'] = $pageBlock['block'];

                        $parseData[$pageBlock['block']] = $pageBlock['html'];
                }


                // in case none of the includes have any blocks
                //if( is_array( $incBlockContent ) ){
                if (is_array($incBlockContent) AND count($incBlockContent) > 0) {

                        //  Group blocks according to include Ids
                        foreach ($incBlockContent as $block) {
                                $includes[$block['temp_id']][] = $block;
                        }

                        //$includes = $incObj->group_blocks_by_field( 'temp_id' );
                }


                //      Run through all includes of main page (including ones without inner blocks)
                foreach ($includesArray as $includeId => $incData) {

                        //      set parse html by default as database html text
                        //      html of current include
                        $includeHtml = $incData['html'];

                        //      Check if current include has blocks, if yes -> process blocks, get processed html
                        if (isset($includes[$includeId])) {

                                // blocks present

                                $incBean = new Pagebean();
                                $incBean->setPageId($incObj->getPageId());
                                $incBean->setTempId($includeId);
                                $incBean->setModule(null);
                                $incBean->setIsLoggedIn($incObj->getIsLoggedIn());
                                $incBean->setHtml($includeHtml);
                                $incBean->setParams($incObj->getParams());
                                $incBean->setBlocks($includes[$includeId]);
                                //$incBean->setHeadHtml( null );

                                $returnBean = $this->process_blocks($incBean);

                                //      Copy all head html from inner modules into main head html
                                if (is_array($returnBean->getHeadHtml()))
                                        $incObj->setHeadHtml(array_merge($incObj->getHeadHtml(), $returnBean->getHeadHtml()));

                                $includeHtml = $returnBean->getHtml();
                        }

                        $parseData["block:" . $incData['parse_tag']] = $includeHtml;
                }

                $html = $this->ci->parser->parse_string($incObj->getHtml(), $parseData, true); // true => return value

                $incObj->setHtml($html);

                return $incObj;
        }

        /**
         * Processes blocks in given html
         * - Group blocks according to modules
         * - Run through all modules, Calls respective modules 'view' method, returns blocks with modified html
         * - NOTE: requires that respective module function returns same blockData array with modified html
         * - replaces old html of each block with new html
         * - parses block tags in main html with new html.
         *
         * Input required :
         * $blockData[ temp_id, block_temp_id, block, tag, module, html ]
         *
         * @param string $html
         * @param int $pageId
         * @param array[] $params String array of parameters in uri
         * @param array[] $blockData [ temp_id, block_temp_id, block, tag, module, html ]
         *
         * @return string returns new processed string, without block parse tags
         */
        public function process_blocks($pageObject) {

                $pageObj = new Pagebean();
                $pageObj = $pageObject;

                $parseData = array();

                if (count($pageObj->getBlocks()) > 0) {

                        //  Group blocks according to modules
                        $modules = $pageObj->group_blocks_by_field('module');

                        //      IMP --> needed if includes deleted directly -> delete keys from array where module = '' (actually value is returned as null from db)
                        /*            $modules = $pageObj->remove_invalid_keys( $modules ); */

                        //      Run through all modules, process blocks
                        foreach ($modules as $module => $Blocks) {

                                $insertDefaultBlock = false;

                                // check if module present, ( possibility tag not associated OR include deleted )
                                if ($module != '') {

                                        //      check if module exists
                                        if (!is_null($this->ci->load->module("$module/$module"))) {

                                                $modBean = new Pagebean();
                                                $modBean->setPageId($pageObj->getPageId());
                                                $modBean->setTempId($pageObj->getTempId());
                                                $modBean->setModule($module);
                                                $modBean->setIsLoggedIn($pageObj->getIsLoggedIn());
                                                $modBean->setHtml(null);
                                                $modBean->setParams($pageObj->getParams());
                                                $modBean->setBlocks($Blocks);
                                                //$modBean->setHeadHtml( null );
                                                //      module exists
                                                $method = VIEW_METHOD;

                                                //  call view function of respective module.
                                                //  returns moduleTags[tagname] = tagHtml
                                                $returnBean = $this->ci->$module->$method($modBean);

                                                //      Copy all head html from inner modules into main head html
                                                if (is_array($returnBean->getHeadHtml()))
                                                        $pageObj->setHeadHtml(array_merge($pageObj->getHeadHtml(), $returnBean->getHeadHtml()));

                                                // run through blocks, create parsetag, key = block[blockname], value = block[html]
                                                foreach ($returnBean->getBlocks() as $block) {
                                                        if (isset($block['block']) AND !is_null($block['tag']) AND $block['tag'] != '') {

                                                                $parseData["block:" . $block['block']] = $block['html'];
                                                        } else {
                                                                // data not found in block
                                                                // display "No data"
                                                                $parseData["block:" . $block['block']] = "<p>No content found</p>";
                                                        }
                                                }
                                        } else {
                                                // module does not exist
                                                $insertDefaultBlock = true;
                                        }
                                } else {

                                        $insertDefaultBlock = true;
                                        // module not present, possibility of tag not being defined
                                }


                                if ($insertDefaultBlock) {

                                        // insert default blocks
                                        // run through all blocks, insert default block
                                        foreach ($Blocks as $block) {


                                                $parseData["block:" . $block['block']] = $this->get_default_block($pageObj->getIsLoggedIn());
                                        }
                                }
                        } // end foreach

                        $html = $this->ci->parser->parse_string($pageObj->getHtml(), $parseData, true); // true => return value

                        $pageObj->setHtml($html);
                }

                return $pageObj;
        }

        /*         * *
         * Insert default blocks into page
         * if logged in display default block ( border and background colour )
         * if NOt logged --> return null
         *
         * @param bool $isLogged if user is logged or not
         * @return string|null returns string (default block) if user logged in, retunr NULL if not logged in
         */

        public function get_default_block($isLogged = false) {

                $output = null;

                if ($isLogged == true) {
                        $output = $this->ci->parser->parse('page/default_block.php', array(), true);
                }

                return $output;
        }

}

?>
