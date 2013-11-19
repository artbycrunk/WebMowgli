<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Description of gallery_tag_lib
 *
 * @author Lloyd Saldanha
 */

include_once MODULEPATH . 'gallery/libraries/gallery_tag_base_lib.php';

class gallery_tag_lib extends gallery_tag_base_lib {


	/**
         * Calls _router( $params )
         *
         * @param array $params list of param values passed to functions
         *
         * @return bool
         */
        public function load_tags( $view, $ids = null, $params = array()) {

		$this->set_view($view);
		$this->set_ids($ids);

                return $this->_router( $params);
        }

	/**
         * checks if view_$keyword exists
         * if YES --> call method
         * return result
         *
         * @param array params incase view methods needs params to be parsed
         */
        private function _router( $params = array()) {

                $success = false;

                $method = "view_" . $this->get_view();	// Eg. view_galleries

                // check if method exists for given templateName
                if (method_exists($this, $method)) {

                        // call method
                        $success = call_user_func_array(array($this, $method), $params);
                } else {

                        // view not defined
                        $success = false;
                        $this->set_error("Gallery view ( $viewKeyword ) not defined");
                }

                return $success;
        }


	/* ******************************** ADD OVERRIDING VIEWS BELOW ******************************** */

}

?>
