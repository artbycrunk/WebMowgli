<?php

if (!function_exists('get_config')) {

        /**
         * Gets a particular config key value of a module
         *
         * @param string $keys provide a single config key
         * @param string $modules module name
         * @param string|null $configSuffix suffix value for config file ( assuming filename is moduleName_suffix ), If suffix NOT provided, default suffix is assumed, i.e. moduleName_config
         *
         * @return mixed|bool Returns false if config NOT found
         */
        function get_config($key, $module, $configSuffix = null) {

                // get config files
                $configArray = get_config_array($key, $module, $configSuffix);

                return isset($configArray[$module][$key]) ? $configArray[$module][$key] : null;
        }

}

if (!function_exists('get_config_array')) {

        /**
         * Get config values of modules
         * Load necessary config files
         * check for required key in each module config file
         * note: final result will only have those modules where atleast one key is set among list of requested keys
         *
         * Final result is returned as an array with config of each array attached to each module
         *
         * Eg. Return in the format $config[$module][$key] = value
         * $config = array(
         *      [module 1] = array(
         *                      [key 1] = value 1,
         *                      [key 2] = value 2
         *              )
         *      [module 2] = array(
         *                      [key 1] = value 1,
         *                      [key 2] = value 2
         *              )
         * )
         *
         * @param string|array $keys provide a single config key OR an array of keys
         * @param string|array|null $modules if modules NOt provided, all module forlders are checked, if provided, only particular modules are checked
         * @param string|null $configSuffix suffix value for config file ( assuming filename is moduleName_suffix ), If suffix NOT provided, default suffix is assumed, i.e. moduleName_config
         *
         * @return array|null returns config arranged by modules, return null if no config found
         */
        function get_config_array($keys, $modules = null, $configSuffix = null) {

                $config = null;

                $ci = & get_instance();
//                $ci->load->helper('directory');
                // convert $keys to array if NOT array
                $keys = is_array($keys) ? $keys : array($keys);

                // check if modules provided
                if (!is_null($modules)) {

                        // modules provided,
                        // convert $modules to array if NOT array
                        $modules = is_array($modules) ? $modules : array($modules);
                } else {

                        // modules NOT provided, assume all modules
                        // get all modules
                        $modules = get_module_dir_list();
                }

                // run through all modules, load config files, get key-value pair
                foreach ($modules as $module) {

                        // if configSuffix provided use moduleName_suffix ELSE use moduleNAme_config
                        $configFile = $module . "_" . ( is_null($configSuffix) ? "config" : $configSuffix );

                        // load config files, process only if config file available
                        if ($ci->config->load($configFile, true, true, $module)) {

                                // iterate through all required keys, get values if exists
                                foreach ($keys as $key) {

                                        $value = $ci->config->item($key, $configFile);

                                        // if Item NOT found DO NOT add to array
                                        if (!is_null($value)) {

                                                // save key-value pair in config
                                                $config[$module][$key] = $value;
                                        }
                                }
                        }
                }

                return $config;
        }

}
?>
