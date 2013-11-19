<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of I_Admin_Extract
 * this is an Interface for module Admin content extraction
 * this interface should be implemented only in those classes,
 *   which are being used to extract content from templates during site import
 * i.e. modules/module_name/admin/module_name.php
 * Eg. modules/content/admin/content.php
 *
 * @author Lloyd
 */
interface I_Admin_Extract {

        public function _extract_content($tempId, $parseTag, $innerText, & $db);
}

?>
