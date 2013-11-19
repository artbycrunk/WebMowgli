<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of template_lib
 *
 * @author Lloyd
 */
class template_lib {

        private $ci;
        private $error;

        public function __construct() {

                $this->ci = & get_instance();

                // load helpers
                $this->ci->load->helper('directory');
        }

        public function get_modules_with_templates() {

                $modules = null;

                $moduleDirs = get_module_dir_list();

                foreach ($moduleDirs as $key => $module) {

                        $module_config = $module . "_config"; // Eg. module_config
                        // load 'module_config' file, check if config exists
                        if ($this->ci->config->load($module_config, true, true, $module)) {

                                // check if page templates config item set in module_config
                                $hasTemplate = $this->ci->config->item(WM_HAS_TEMPLATES, $module_config);

                                // if module has allows page templates, add to module list
                                if ($hasTemplate)
                                        $modules[] = $module;
                        }
                }

                return $modules;
        }

        /**
         * returns an array that can be used for creating a select option list for the add / edit form.
         * - adds default select option [0] => "select a module"
         *
         * Eg.
         * $modules = array (
         * 0 => "select module"
         * 'module1' => 'module1',
         * 'module2' => 'module2',
         * 'module3' => 'module3'
         * )
         *
         * @return array
         */
        public function get_module_select_list() {

                $moduleList = null;
                $modules = array( '' => 'select module' ); // add dummy text to start of drop down list

                // gets list of modules that allow users to create templates
                $moduleList = $this->get_modules_with_templates();

                // save value as key. Eg. [value1]=> [value1]
                foreach ($moduleList as $key => $module) {
                        $modules[$module] = $module;
                }

//                array_unshift(& $modules, "select module");

                return $modules;
        }
        

}

?>
