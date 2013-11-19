<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_tag_lib
 *
 * @author Lloyd
 */
include_once MODULEPATH . '/blog/libraries/blog_tag_base_lib.php';

class blog_tag_lib extends blog_tag_base_lib {

        /**
         * checks if view_$keyword exists
         * if YES --> call method
         * return result
         *
         * @param array params incase view methods needs params to be parsed
         */
        private function _router($params = array()) {

                $success = false;
//                $parseTags = null;

                $viewKeyword = $this->get_viewKeyword();

                $method = "view_$viewKeyword";

                // check if method exists for given templateName
                if (method_exists($this, $method)) {

                        // call method
                        $success = call_user_func_array(array($this, $method), $params);
//                        $success = true;
//                        $this->reset();
                } else {

                        // view not defined
                        $success = false;
                        $this->set_error("view ( $viewKeyword ) not defined");
                }

                return $success;
        }

        /**
         * Calls _router( $params )
         *
         * @param array $params list of param values passed to functions
         *
         * @return bool
         */
        public function load_tags($params = array()) {

                return $this->_router($params);
        }

        /*         * ************************ ADD OVERRIDING METHODS HERE ******************************** */

        //
        // Format:
        //
//        private function view_'keyword_name'(){
//
//                $success = false;
//
//                /*
//                 * To Do,
//                 * - get data from database
//                 * - create tag structure from data array ( Eg. $tags )
//                 * - call $this->set_tags( $tags )
//                 * - return bool ( if $success = false ==> set error message Eg. $this->set_error( 'error message here' ) )
//                 */
//
//                return $success;
//        }

}

?>
