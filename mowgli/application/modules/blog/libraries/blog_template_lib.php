<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_template_lib
 *
 * @author Lloyd
 */
class blog_template_lib {

        private $ci;
        private $module = 'blog';
//        private $template = null;
        private $head = null;
        private $html = null;
        private $error = null;
        private $templateDir = null;

        public function __construct() {
                $this->ci = & get_instance();

                $this->ci->load->library('templates');

                $this->ci->load->helper('url');

                $this->templateDir = module_path($this->module) . "/views/templates/";
        }

        private function set_error($error) {
                $this->error = $error;
        }

        private function set_head($head) {
                $this->head = $head;
        }

        private function set_html($html) {
                $this->html = $html;
        }

        public function get_error() {
                return $this->error;
        }

        public function get_head() {
                return $this->head;
        }

        public function get_html() {
                return $this->html;
        }

        public function reset(){

                $this->set_head(null);
                $this->set_html(null);
                $this->set_error(null);
        }

        /**
         * - Get template from db ( using general template library )
         * - if template not found --> get default view for that template
         *
         * @param string $templateName
         *
         * @return bool
         */
        public function load_template($templateName, $parseData = array()) {

                $success = false;

                $this->reset();

                // get template from database ( using general template library )
                if ($this->ci->templates->load($this->module, $templateName, WM_TEMPLATE_TYPE_MODULE, false)) {

                        $this->set_head($this->ci->templates->get_head());
                        $this->set_html($this->ci->templates->get_html());

                        $success = true;
                } else {

                        // template with given name does not exist, check if default template ( view ) available
                        // get template from view
                        // if not available, return false

                        $filePath = $this->templateDir . "/$templateName.php";

                        if (file_exists($filePath)) {

                                $html = $this->ci->parser->parse($this->module . "/templates/$templateName", $parseData, true);
                                $success = true;
                                $this->set_html($html);
                        } else {
                                $success = false;
                                $this->set_error("template not available");
                        }
                }

                return $success;
        }

}

?>
