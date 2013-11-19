<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of I_Page
 * this is an Interface for module page rendering
 * this interface should be implemented by rendering classes of module
 * i.e. modules/module_name/module_name.php
 * Eg. modules/content/content.php
 *
 * @author Lloyd
 */
interface I_Page_Render {

        public function view($pageObj);

}

?>
