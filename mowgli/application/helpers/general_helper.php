<?php
/*
 * This helper will contain some global common functions
 */


if(!function_exists('show_page_404')) {

        /**
         * redirects pages to 404 page
         */
        function show_page_404( $errorMessage = null ) {

                echo Modules::run( 'page/page/show_404', $errorMessage );

        }
}

if(!function_exists('string_to_bool')) {

        /**
         * Returns TRUE for "1", "true", "on" and "yes". Returns FALSE otherwise.
	 *
	 * @param string $string string value to check if true OR not ( Eg. 'yes', 'no', '1', '0', 'on', 'off' )
         */
        function string_to_bool( $string ) {

                return filter_var($string, FILTER_VALIDATE_BOOLEAN);

        }
}

?>
