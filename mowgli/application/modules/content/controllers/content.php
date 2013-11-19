<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of content
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
 
 require_once APPPATH . 'libraries/Pagebean.php';
 
class Content extends Site_Controller implements I_Page_Render {

        private $module = "content";

        public function __construct() {
                parent::__construct();

                $this->load->model('content/content_model');

                $this->parseData['module:resource'] = module_resource_url($this->module);
        }

        // generates content for mentioned page blocks
        public function view($pageObj) {

//// Inserting text into specific areas of template
////$position = new Template_positions();
                $position = Template_positions::get_instance();
//$position->add( 'head', 'start', "@MESSAGE - in " . __CLASS__ . " module");
                // $moduleBlocks ==> [][ temp_id, block_temp_id, block, tag, module, html ]

                
                $pagebean = new Pagebean();
                $pagebean = $pageObj;

                $moduleBlocks = $pagebean->getBlocks();

                $blocks = array();
                $contentIds = array();
                $tagIdSection = 2; // section in parse tag that holds id

                /* run through each block, get block tag, get content ids of tags */
                foreach ($moduleBlocks as $block) {

                        $tagParts = explode(":", $block['tag']); // Eg. content:article:5
                        $contentIds[] = $tagParts[$tagIdSection];
                }

                //  get content data --> returns [ id, type, data ]
                $contentData = $this->content_model->get_contents($contentIds);

                $content = array();

                //  group content data according to content_id
                foreach ($contentData as $row) {
                        $content[$row['id']] = $row;
                }

                foreach ($moduleBlocks as $block) {

                        $tagParts = explode(":", $block['tag']); // Eg. content:article:5
                        $id = $tagParts[$tagIdSection];

                        if (isset($content[$id])) {

                                $html = $content[$id]['data'];

                                //$this->load->library('user/auth');
                                //$this->auth->is_logged_in()

                                if ($pagebean->getIsLoggedIn()) {

//                                        $head = $this->parser->parse('content/front/head', $this->parseData, true);
//                                        $pagebean->add_headHtml($head);
                                        $adminResourceUrl = admin_resource_url();
                                        $moduleResourceUrl = module_resource_url($this->module);

//                                        $position->add('head', 'start', "<!-- START: Content editor resources -->");
                                        $position->add('head', 'start', "<script type='text/javascript'> var admin_resource_url = '$adminResourceUrl'; </script>");
                                        $position->add('head', 'start', "<link rel='stylesheet' href='$adminResourceUrl/css/inplace_editor.css' type='text/css' media='screen' />");
                                        $position->add('head', 'start', load_resources('js', 'jquery'));
                                        $position->add('head', 'start', load_resources('js', 'tinymce'));
                                        $position->add('head', 'start', "<script type='text/javascript' src='$moduleResourceUrl/scripts/content.adminconsole.js'></script>");
//                                        $position->add('head', 'start', "<!-- END: Content editor resources -->");

                                        $data = array(
                                            'id' => $id,
                                            'content' => $html,
                                            'post_url' => site_url("admin/content/update_content")
                                        );

                                        $html = $this->parser->parse('content/content_edit_wrapper', $data, true); // return value
                                }

                                $block['html'] = $html;
                        } else {
                                // NOT id not found in database
                                // data NOT available
                        }

                        $blocks[] = $block;
                }

                $pagebean->setBlocks($blocks);

                return $pagebean;
        }

}

?>
