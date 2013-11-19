<?php

if (!function_exists('load_resources')) {

        /**
         * excerpt from library . . .
         *
         * gets the resource string for a particular type of resource.
         * a single recource can be added or a list of resources one after the other
         * note: all resources will be seperated by "\n" to display neatly on new line
         *
         * @params string $type 'css' OR 'js'
         * @params string|array $keywords single keyword ( eg. 'jquery' ) OR a list of keywords ( e.g. array( 'jquery', 'tinymce' ) )
         * @params bool $strict if strict=false --> will load current resource even if already loaded
         *
         * @return string|null
         */
        function load_resources($type, $keywords, $strict = true ) {

//                $ci = &get_instance();
//                $ci->load->library('admin/admin_resources');
//                return $ci->admin_resources->load_resources($type, $keywords);

                include_once MODULEPATH . "admin/libraries/admin_resources.php";

//                $resourceObj = new Admin_Resources();
                $resourceObj = Admin_Resources::get_instance();

                return $resourceObj->load_resources($type, $keywords, $strict);
        }

}
?>
