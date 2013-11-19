<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
require_once APPPATH . 'libraries/Pagebean.php';

class Blog extends Site_Controller implements I_Page_Render {

        private $module = 'blog';
        private $configFile = 'blog_config';

        public function __construct() {

                parent::__construct();

                // load config files
                $this->config->load($this->configFile, true, true, $this->module);

                // load libraries
                $this->load->library('blog/blog_url_lib');
                $this->load->library('blog/blog_render_lib');
                $this->load->library('blog/blog_template_lib');

                // load helpers
                $this->load->helper('url');
                $this->load->helper('settings');

                // shortcode:ad
                !defined('BLOG_SHORTCODE_AD') ? define('BLOG_SHORTCODE_AD', $this->config->item('shortcodes_ad', $this->configFile)) : null;


                $this->parseData['module:resource'] = module_resource_url($this->module);
        }

        /**
         * This method is called by page controller if current module has special pages
         *
         * note: for blog --> pages are based on 'type' of request
         * namely ( summary, widget, group, post )
         *
         * mapping of pages is as follows
         * summary, widget --> _blog
         * group --> _blog_group
         * post --> _blog_post
         *
         * @param string $page page name to check for special pages
         * @return string|null if appropriate page NOT found, simply return NULL or FALSE
         */
        public function _get_special_page($page) {

                $request = Request::get_instance();
                $this->blog_url_lib->set_uri($request->get('uri_relative'));

                // get permalink structure from database, set permalink for further calculations
                $permalink = get_settings($this->module, $this->config->item('setting_permalink', $this->configFile));
                $this->blog_url_lib->set_permalink($permalink);

                if ($this->blog_url_lib->decode_uri()) {

                        switch ($this->blog_url_lib->get_type()) {
                                case ( BLOG_DATA_TYPE_SUMMARY ) :

                                        $page = "blog";
                                        break;
				case ( BLOG_DATA_TYPE_WIDGET ) :

                                        $page = "blog";
                                        break;

                                case BLOG_DATA_TYPE_GROUP :

                                        $page = "blog_group";
                                        break;
                                case BLOG_DATA_TYPE_POST :

                                        $page = "blog_post";
                                        break;

                                default:
                                        // this will default page to original page in request
                                        $page = null;
                                        break;
                        }
                }

                return $page;
        }

        public function view($pageObj) {

                /**
                 * - Process uri
                 *      * get only blog relative uri from full url
                 *      * decode uri
                 *      * create type/post/page/categ . . . etc
                 * - get blocks
                 * - identify main block section ( combination of URL and block tag )
                 *      - set instance of main section
                 *      - process main block
                 *      - remove main block from block list
                 * - run through each block
                 *      - get render parse tag
                 *      - decode tag ( get array of tag params )
                 *      - get template, data
                 *      - merge template + data --> get final html
                 */
                $position = Template_positions::get_instance();
                $request = Request::get_instance();

                $pagebean = new Pagebean();
                $pagebean = $pageObj;
                $blocks = $pagebean->getBlocks();

                $this->blog_url_lib->set_uri($request->get('uri_relative'));

                // get default ad script
                $this->parseData[BLOG_SHORTCODE_AD] = get_settings($this->module, $this->config->item('setting_ad_script', $this->configFile));

                // get permalink structure from database, set permalink for further calculations
                $permalink = get_settings($this->module, $this->config->item('setting_permalink', $this->configFile));
                $this->blog_url_lib->set_permalink($permalink);

//                // check if atleast one block has a main tag
//                $tagStrings = null;
//                foreach ($blocks as & $block) {
//
//                        $tagStrings[] = $block['tag'];
//                }
//                $isMainTagExists = $this->blog_render_lib->check_main_block_exists($tagStrings);
                // -------
                // converts current blog uri to correct keyword or type, for processing
                // note returns bool, true implies successfully decoded ( uri in valid format )
                // but if return=false --> uri NOT in valid format --> BUT can be ignored as some blocks may not depend on uri
                $success = $this->blog_url_lib->decode_uri();
                // $error = $this->blog_url_lib->get_error();
                // ------
                // get necessary data for rendering
                $urlParams = array(
                    'post_slug' => $this->blog_url_lib->get_post_slug(),
                    'post_id' => $this->blog_url_lib->get_post_id(),
                    'conditions' => $this->blog_url_lib->get_conditions(),
                    'view' => $this->blog_url_lib->get_viewKeyword()
                );

                foreach ($blocks as & $block) {

                        $tagString = $block['tag'];

                        $this->blog_render_lib->reset();

                        // set necessary values in render library
                        $this->blog_render_lib->set_PostId($urlParams['post_id']);
                        $this->blog_render_lib->set_PostSlug($urlParams['post_slug']);
                        $this->blog_render_lib->set_urlConditions($urlParams['conditions']);
                        $this->blog_render_lib->set_urlViewKeyword($urlParams['view']);

                        // render view, if successfull, save head,html in block, else -> default error message
                        if ($this->blog_render_lib->render($tagString, $this->parseData)) {

                                $head = $this->blog_render_lib->get_head();
                                $html = $this->blog_render_lib->get_html();

                                if (!is_null($head) AND $head != '') {
                                        $position->add('head', 'end', $head);
                                }

                                // add html to main page data
                                $html = $this->parser->parse_string($html, $this->parseData, true);
                        } else {
                                // error, throw default message
                                $html = $this->blog_render_lib->get_error();

//                                // throw 404 error if block does not return data
//                                show_page_404($html);
                        }

                        $block['html'] = $html;
                }

                /* Update  blocks with new data */
                $pagebean->setBlocks($blocks);

                return $pagebean;
        }

}

?>
