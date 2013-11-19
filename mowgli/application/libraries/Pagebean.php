<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of page_bean
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Pagebean {

    private $pageId;
    private $tempId;
    private $html;
    private $module;

    private $isLoggedIn;

    private $params = array();
    private $blocks = array();
    private $headHtml = array();

    public function get_block_tag_section( $sectionNo ){
        return $this->params[ $sectionNo - 1 ];
    }

    public function group_blocks_by_field( $field ){

        $return = null;

        if( is_array( $this->blocks ) AND count( $this->blocks ) > 0 ){

            //  group data according to field
            foreach ( $this->blocks as $block ) {

                $return[ $block[ $field ]][] = $block;
                
            }
            
        }

        return $return;
    }

    /*
     * add string to headHtml OR add array to headHtml
     */
    public function add_headHtml( $strings ){

        if( is_array( $strings ) AND count( $strings ) > 0 ){

            $this->headHtml = array_merge( $this->headHtml, $strings );
        }
        else{

            $this->headHtml[] = $strings;
        }

    }

    public function remove_invalid_keys( $array ){

        foreach ($array as $key => $value) {
            if( $key == '' ) unset ( $array [ $key ] );
        }
        return $array;
    }


    /********************** Setters & Getters *****************************************/

    public function setPageId( $pageId ){
        $this->pageId = $pageId;
    }
    public function getPageId(){
        return $this->pageId;
    }

    public function setTempId( $tempId ){
        $this->tempId = $tempId;
    }
    public function getTempId(){
        return $this->tempId;
    }

    public function setHtml( $html ){
        $this->html = $html;
    }
    public function getHtml(){
        return $this->html;
    }

    public function setModule( $module ){
        $this->module = $module;
    }
    public function getModule(){
        return $this->module;
    }

    public function setIsLoggedIn( $isLoggedIn ){
        $this->isLoggedIn = $isLoggedIn;
    }
    public function getIsLoggedIn(){
        return $this->isLoggedIn;
    }

    public function setParams( $params ){
        $this->params = $params;
    }
    public function getParams(){
        return $this->params;
    }

    public function setBlocks( $blocks ){
        $this->blocks = $blocks;
    }
    public function getBlocks(){
        return $this->blocks;
    }

    public function setHeadHtml( $headHtml ){
        $this->headHtml = $headHtml;
    }
    public function getHeadHtml(){
        return $this->headHtml;
    }

}
?>
