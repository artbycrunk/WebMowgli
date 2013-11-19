<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of view
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class View {

    private $parse;



    public function  __construct() {

        $pageUri = 'admin/template/manage';

        $tab1 = array(
            'main:tab_list:selected_tab' => '',
            'main:tab_list:tab_link' => '',
            'main:tab_name' => $value
        );

        $tab2 = array(
            'main:tab_list:selected_tab' => '',
            'main:tab_list:tab_link' => $value,
            'main:tab_name' => $value
        );

        $tab3 = array(
            'main:tab_list:selected_tab' => '',
            'main:tab_list:tab_link' => $value,
            'main:tab_name' => $value
        );
    

        $this->parse = array(
            'main:page_name' => 'Template',
            'main:tab_list' => array(
                                  array('main:tab_list:selected_tab' => 'Title 1', 'main:tab_list:tab_link' => 'Body 1', 'main:tab_name' => 'Body 1'),
                                  array('main:tab_list:selected_tab' => 'Title 2', 'main:tab_list:tab_link' => 'Body 2', 'main:tab_name' => 'Body 1'),
                                  array('main:tab_list:selected_tab' => 'Title 3', 'main:tab_list:tab_link' => 'Body 3', 'main:tab_name' => 'Body 1'),
                                  array('main:tab_list:selected_tab' => 'Title 4', 'main:tab_list:tab_link' => 'Body 4', 'main:tab_name' => 'Body 1'),
                                  array('main:tab_list:selected_tab' => 'Title 5', 'main:tab_list:tab_link' => 'Body 5', 'main:tab_name' => 'Body 1')
                              )
        );

        

    }
}
?>
