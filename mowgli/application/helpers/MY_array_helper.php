<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of MY_array_helper
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */

if(!function_exists('rearrange_array')) {

        /**
         * Rearranges a 2 dimensional array on the basis of a specified key
         *
         * @param array $array
         * @param string $key
         * @param bool $multiDimension set to true -- when multiple items will have same pivot point ( $arrangeBy )
         *
         * @return array|null returns rearranged array on success OR null on failure
         */
        function rearrange_array( $array, $key, $multiDimension = false ) {

                $return = null;

                if( is_array( $array ) ) {
                        
                        /* run through array */
                        foreach ( $array as $element ) {

                                $arrangeBy = isset ( $element[ $key ] ) ? $element[ $key ] : null;

                                /* check if key is present in provided array, if not terminate */
                                if( ! is_null( $arrangeBy ) ) {

                                        if( $multiDimension == false ) {

                                                $return[ $arrangeBy ] = $element;
                                        }
                                        else {
                                                // arrange array in a list under $arrangeBy
                                                $return[ $arrangeBy ][] = $element;
                                        }
                                }
                                else {
                                        $return = null;
                                        break;
                                }

                        }
                }
                return $return;

        }

}


/* End of file MY_array_helper.php */
/* Location: ./application/helpers/MY_array_helper.php */
?>
