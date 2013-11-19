<?php
if (!defined('BASEPATH'))
        exit('No direct script access allowed');
/**
 * Description of gallery_themes_library
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class gallery_themes_library {

        private $ci;

        private $module = 'gallery';

        private $themeName;
//        private $themeScripts;
        private $themeConfig;   // array that will hold theme config.ini data

        private $configFile = 'gallery_config';
        private $config_database = 'gallery_config_database';


        public function __construct() {

                $this->ci = & get_instance();

                $this->ci->config->load( $this->configFile, true, true, $this->module );
                $this->ci->config->load( $this->config_database, true, true, $this->module );

		// load libraries
		$this->ci->load->library( 'domparser' );
		$this->ci->load->library('config_reader');
		$this->ci->load->library('notifications');

		// Load models
		$this->ci->load->model( 'gallery/gallery_model' );

        }

        // creates all necessary folders for mentioned path, returns true/false
        private function _create_dir( $path ) {

                $success = true;

                $separator = '/';

                $path = str_replace( '\\', $separator, $path);

                $dirList = explode( $separator, $path );

                $tempDirArray = $dirList;
                $pathsToCreate = array();

                for( $count = count( $dirList ) - 1; $count >= 0; $count-- ) {

                        $tempPath = implode( $separator, $tempDirArray );

                        if( file_exists( $tempPath ) ) {

                                break;
                        }
                        else {
                                $pathsToCreate[] = $tempPath;
                                unset( $tempDirArray[ $count ] );
                        }

                }

                if( count( $pathsToCreate ) > 0 ) {

                        $pathsToCreate = array_reverse( $pathsToCreate );

                        foreach ( $pathsToCreate as $createPath ) {

                                if( ! file_exists( $createPath ) ) {

                                        if( mkdir( $createPath ) == false ) {

                                                $success = false;
                                                break;
                                        }
                                        else {

                                                $success = true;
                                        }

                                }


                        }

                }


                return $success;

        }

        /* returns theme folder path, if not existing --> will create folder path */
        public function get_theme_resource_path() {

                $themeDir = FCPATH . $this->get_theme_resource_uri();


                if( ! file_exists( $themeDir ) ) {

                        $themeDir = $this->_create_dir( $themeDir ) ? $themeDir : false;
                }

                return $themeDir;
        }

        /**
         * returns theme resource uri ( relative to site root )
         */
        public function get_theme_resource_uri() {
                // module_resource_root_path
//                $moduleRootUri = $this->ci->config->item('module_resource_root_uri');
//                $themeRootUri = $this->ci->config->item( 'themes_resource_uri', $this->configFile );
//                $themeUri = "$moduleRootUri/$themeRootUri/" . $this->themeName;
                $themeUri = "/" . upload_module_uri( $this->ci->config->item( 'themes_resource_uri', $this->configFile ) ) . "/" . $this->themeName;

                return $themeUri;
        }

        public function create_theme_dir() {

                $themeDir = $this->get_theme_resource_path();
                return $themeDir === false  ? false : true;

        }

        /**
         * Reads config.ini file and returns array of configuration
         *
         * @param string $rootDirectory root directory where them files are stored
         *
         * @return array|null returns array of config items, returns null if error
         */
        public function get_theme_config( $rootDirectory ) {

                $return = null;



                /* get gallery config values */
                $configFileName = $this->ci->config->item( 'theme_config_filename', $this->configFile );

                /* get path of them config file */
                $themeConfigFile = "$rootDirectory/$configFileName";

                /* read config.ini file, suppress errors or warnings */
                $config = $this->ci->config_reader->read( $themeConfigFile );

                if( ! is_null( $config ) ) {

                        $return = $config;

                }
                else {

                        // errors
                        $return = null;
                        $errors = $this->ci->config_reader->get_notifications( 'error' );


                        foreach ( $errors as $msg ) {

                                $this->ci->notifications->add( 'error', $msg );

                        }
                        $this->ci->notifications->save();

                }


                return $return;
        }
//        public function get_theme_config( $rootDirectory ) {
//
//                $config = null;
///
//
//                /* get gallery config values */
//                $configFileName = $this->ci->config->item( 'theme_config_filename', $this->configFile );
////                $configArrayKeys = $this->ci->config->item( 'theme_config_array_keys', $this->configFile );
////
//                /* Get config constants for config.ini file */
//                $consts = array(
//                        'true'  => $this->ci->config->item( 'theme_config_true', $this->configFile ),
//                        'false' => $this->ci->config->item( 'theme_config_false', $this->configFile ),
//                        'null'  => $this->ci->config->item( 'theme_config_null', $this->configFile )
//                );
//
//                /* get path of them config file */
//                $themeConfigFile = realpath( "$rootDirectory/$configFileName" );
//
//                /* Check if config file exists */
//                if( file_exists( $themeConfigFile ) ) {
//
//                        /* read config.ini file, suppress errors or warnings */
//                        $config = @parse_ini_file( $themeConfigFile, false );
//
//                        /* Check if config file is valid */
//                        if( $config != false AND count( $config ) > 0 ) {
//
//                                /* If value is of type -- true, false of null convert sring to respective data type */
//                                foreach ( $config as $key => $value ) {
//
//                                        if( in_array( $value, $consts['true'] ) )  $config[ $key ] = true;
//                                        if( in_array( $value, $consts['false'] ) )  $config[ $key ] = false;
//                                        if( in_array( $value, $consts['null'] ) )  $config[ $key ] = null;
//                                }
//
//                        }
//                        else {
//
//                                // invalid config file
//                                $config = null;
//                                $this->ci->notifications->add('error', 'Invalid config file provided');
//
//                        }
//
//                }
//                else {
//
//                        // Theme config file NOT found
//                        $this->ci->notifications->add('error', 'Theme config file not found');
//                        $config = null;
//
//                }
//
//                $this->ci->notifications->save();
//
//                return $config;
//        }

        public function set_theme_config( $configArray ) {
                // Set theme config for library
                $this->themeConfig = $configArray;
        }
        public function set_theme_name( $themeName ) {
                $this->themeName = $themeName;
        }
        public function get_theme_name() {
                return $this->themeName;
        }

        /**
         * Seperates the template files ( html, htm ) from other files,
         * returns list of files either 'templates' OR 'resources' OR 'all',
         * depending on 2nd parameter
         *
         * @param array $files List of filepaths
         * @param string $type type of return ( 'templates' OR 'resources' OR 'all' )
         *
         * @return array|null returns null if invalid type provided
         */
        public function separate_pages_from_resources( $files, $type = 'all' ) {

                $return = null;

                $templates = array();
                $resources = array();

                /* Run through uploaded files */
                foreach ( $files as $file ) {

                        $tempFileData = pathinfo($file);
                        $templateFileTypes = $this->ci->config->item( 'theme_template_filetypes', $this->configFile );

//                        echo "<br/>$file<br/>";

                        /* check if file is html / htm */
                        if( isset ( $tempFileData[ 'extension' ] ) AND in_array( $tempFileData[ 'extension' ], explode( '|', $templateFileTypes ) ) ) {
                                /* template / page file --> perform extract processes */

                                $templates[] = realpath( $file );
                        }
                        else {
                                /* resource file --> save details in array for database */
                                $resources[] = realpath( $file );
                        }



                        unset ($tempFileData);
                }

                switch ( $type ) {

                        case 'templates':
                                $return = $templates;
                                break;

                        case 'resources':
                                $return = $resources;
                                break;
                        case 'all':
                                $return = array( 'templates' => $templates, 'resources' => $resources );
                                break;

                        default:
                                $return = null;
                                break;
                }

                return $return;
        }

        /**
         * Copies (provided) resource files to theme resource directory
         * Writes resource data to database
         *
         * @param array $resources      List of resurce file paths
         * @param string $unzipPath     Path of unzipped directory
         * @param referance $db         db ref for transactions
         *
         * @return bool
         */
        public function import_theme_resources( $resources, $unzipPath, & $db ) {

                $resourcesDb = null;

                $themeResourcePath = $this->get_theme_resource_path();

                /* Iterate through all resources */
                foreach ( $resources as $resource ) {

                        /* copying files to theme resource directory */

                        $uri = substr( realpath( $resource ), strlen( realpath( $unzipPath ) ) );       // get relative path to unzipPath root
                        $uri = str_replace( '\\', '/', $uri );                                          // replace '\' with '/'
                        $uri = substr( $uri, 1 );                                                       // remove first char ('/')

                        /* final file to write */
                        $destfile = "$themeResourcePath/$uri";

                        /* Get parent folder for destination file */
                        $destDir = substr_replace( $destfile, '', - strlen( pathinfo( $resource, PATHINFO_BASENAME ) ) );

                        /* Create directories if they do not exist */
                        $this->_create_dir( $destDir );

                        /* copy files from unzipPath to required theme folders */
                        copy( $resource, $destfile );

                        /* Set database arrays */
                        $resourcesDb[] = array(

                                //    'gallery_resource_id' => null,
                                'gallery_resource_name' => pathinfo( $resource, PATHINFO_BASENAME ),
                                'gallery_resource_filetype' => pathinfo( $resource, PATHINFO_EXTENSION ),
                                'gallery_resource_uri' => $uri,
                                'gallery_resource_theme_name' => $this->themeName

                        );

                }


                $this->ci->gallery_model->set_db( $db );

                /* Write to database, returns true/false */
                return $this->ci->gallery_model->create_resources( $resourcesDb );

        }


        /**
         * gets global script text for theme ( from main theme file )
         * - checks if main theme file exists
         * - scans file for extract tags ( <gallery> )
         * - concatinates all innerhtml of tags with <gallery type='script' >
         * - returns final string
         *
         * @param string $rootDir Directory root containing theme related files
         *
         * @return string|null returns string on success, null on failure
         *
         */
        public function get_theme_script( $rootDir ) {

                $script = null;

                /* Check if main file provided in config AND if file exists in uploaded path */
                if( isset( $this->themeConfig['main'] ) AND file_exists( $rootDir . "/" . $this->themeConfig['main'] ) ) {

                        $themeFile = realpath( $rootDir . "/" . $this->themeConfig['main'] );        // gets file name containing theme related information

                        /* Create dom object */
                        $dom = $this->ci->domparser->file_get_html( $themeFile );

                        $galleryTag = $this->ci->config->item( 'themes_extract_tag', $this->configFile );                       // = gallery
                        $extractTypeIdent = $this->ci->config->item( 'themes_extract_tag_type_ident', $this->configFile );      // = type
                        $tagType = $this->ci->config->item( 'themes_extract_type_script', $this->configFile );              // = script

                        /* Find <gallery> tags in html */
                        $tags = $dom->find( $galleryTag );

                        /* Check if extract tags found in main file */
                        if( count( $tags ) > 0 ) {

                                $isScriptFound = false;

                                /* concatinate all extract tags */
                                foreach ( $tags as $tag ) {

                                        if( $tag->hasAttribute( $extractTypeIdent ) AND $tag->getAttribute( $extractTypeIdent ) == $tagType ) {

                                                $script .= $tag->innertext . "\n";
                                                $isScriptFound = true;

                                        }

                                }

                                /* If type='script' attributes found in tag, set warning */
                                ! $isScriptFound ? $this->ci->notifications->add( 'warning', "No theme scripts saved - Script tag not found in main theme file" ) : null;
                        }
                        else {

                                /* no extract tags found, set warning */
                                $script = null;
                                $this->ci->notifications->add( 'warning', "No theme scripts saved - Script tag not found in main theme file" );

                        }


                        $dom->clear();
                        unset ($dom);

                }
                else {

                        /* main them file not provided or incorrect config set */
                        $script = null;
                        $this->ci->notifications->add( 'warning', "No theme scripts saved - Main theme file not found" );
                }


                $this->ci->notifications->save();

                return $script;
        }


        /**
         * Removes the main theme file from list of files,
         * also deletes main file from folder location
         *
         * @param array $templates List of files to scan
         * @param $rootDir Root directory of unzipped files
         *
         * @return array $templates
         */
        public function remove_main_theme_file( $templates, $rootDir ) {

                if( isset( $this->themeConfig['main'] ) AND file_exists( $rootDir . "/" . $this->themeConfig['main'] ) ) {

                        $mainFile = realpath( $rootDir . "/" . $this->themeConfig['main'] );
                        $matchKey = array_search( $mainFile, $templates, true ); // strict search

                        /* Check if value found in array, if found --> delete from array and delete file */
                        if( ( $matchKey != false ) OR ( $matchKey != '' ) AND isset( $templates[ $matchKey ] ) ) {

                                unset( $templates[ $matchKey ] );
                                unlink( $mainFile );

                        }
                }

                return $templates;

        }

        /**
         * Extracts gallery tags from provided pages
         * and saves in array.
         * returns null if error occured.
         * sets notification warnings and errors
         *
         * array format:
         * templatesData[type][temp_name] = array (
         *      'html' = '..........'
         *      'scripts' = '........'
         *      )
         *
         * @param array $templates List of file paths to process
         *
         * @return array|null returns completed array in above format, OR returns null on failure
         */
        private function _extract_tags( $templates ) {

                $success = true;
                $tempData = array();

                /* Get config values */
                $galleryTag = $this->ci->config->item( 'themes_extract_tag', $this->configFile );                       // = gallery
                $extractTempIdent = $this->ci->config->item( 'themes_extract_tag_temp_ident', $this->configFile );      // = template
                $extractTypeIdent = $this->ci->config->item( 'themes_extract_tag_type_ident', $this->configFile );      // = type
                $tagTypeScript = $this->ci->config->item( 'themes_extract_type_script', $this->configFile );                  // = script
                $types = $this->ci->config->item( 'themes_extract_type_values', $this->configFile );                    // array ( categories, galleries, images )
                $tempName = $this->ci->config->item( 'settings_default_template', $this->config_database );     /* set template name to default */

                /* Run through each file */
                foreach ( $templates as $file ) {

                        $dom = $this->ci->domparser->file_get_html( $file );

                        $searchString = $galleryTag . "[$extractTypeIdent]";    // = gallery[type] --> search for all <gallery type='...'>

                        $tags = $dom->find( $searchString );

                        /* Check if extract tags found */
                        if( count( $tags ) > 0 ) {

                                $temp = array(
                                        'scripts' => null,
                                        'html' => null
                                );

                                /* Run through all tags found in current page */
                                foreach ( $tags as $tag ) {

                                        /* get value for 'template' attribute */
                                        $type = $tag->getAttribute( $extractTypeIdent );

                                        /* Check if type = categories OR galleries OR image */
                                        if( in_array( $type, $types) ) {

                                                /* Check if extract tag has 'template' attribute */
                                                if( $tag->hasAttribute( $extractTempIdent ) ) {

                                                        // template mentioned, set template
                                                        $tempName = $tag->getAttribute( $extractTempIdent );

                                                }

                                                /* create template array data */
//                                                $tempData[ $type ][ $tempName ]['scripts'] = null;
//                                                $tempData[ $type ][ $tempName ]['html'] = null;

                                                /* check if 'script' attribute present */
                                                if( $tag->hasAttribute( $tagTypeScript ) ) {

                                                        /* Concatinate scripts for similar templates */
                                                        if( ! isset ( $temp['scripts'] ) ) {

                                                                $temp['scripts'] = $tag->innertext;

                                                        }
                                                        else {

                                                                $temp['scripts'] .= "\n" . $tag->innertext;
                                                        }
//                                                        /* Concatinate scripts for similar templates */
//                                                        if( ! isset ( $tempData[ $type ][ $tempName ]['scripts'] ) ) {
//
//                                                                $tempData[ $type ][ $tempName ]['scripts'] = $tag->innertext;
//
//                                                        }
//                                                        else {
//
//                                                                $tempData[ $type ][ $tempName ]['scripts'] .= "\n" . $tag->innertext;
//                                                        }

                                                }
                                                else {

                                                        /* save html for given type and template */
                                                        $temp['html'] = $tag->innertext;

                                                }

                                                $tempData[ $type ][ $tempName ]['scripts'] = $temp['scripts'];
                                                $tempData[ $type ][ $tempName ]['html'] = $temp['html'];

                                        }
                                        else {

                                                // Tags that do not have a valid type
                                                $filename = pathinfo( $file, PATHINFO_BASENAME );
                                                $this->ci->notifications->add( 'error', "Invalid tag found in '$filename'" );
                                                $success = false;
                                        }


                                } // end foreach ( for template iteration )

                        }
                        else {
                                // no extract tags found in file
                                $filename = pathinfo( $file, PATHINFO_BASENAME );
                                $this->ci->notifications->add( 'warning', "No valid extract tags found in '$filename'" );
                                $success = true;
                        }

                        $dom->clear();
                        unset ($dom);

                } // end foreach ( for file iteration )


//                echo "completed";
//
//                echo '<div style="padding-left: 100px;">';
//                foreach( $tempData as $template => $data ){
//
//                        echo "<hr/><br/><b><font color='blue' size='6'>Template : $template</font></b><br/>";
//
//                        echo "<label>script :</label><br/>";
//                        echo "<textarea cols='70' rows='20'>". $data['scripts'] ."</textarea><br/>";
//
//                        echo "<label>html :</label><br/>";
//                        echo "<textarea cols='70' rows='20'>". $data['html'] ."</textarea><br/>";
//
//                }
//
//                echo "</div>";

                $this->ci->notifications->save();

                return $success ? $tempData : null;
        }


        /**
         * extracts content for template data,
         * processes href and src for relative parsetag links,
         * Stores templates in database,
         *
         * @param array $templates List of template files to process,
         * @param ref $db database object for transactions
         *
         * @return bool true on success, false on failure
         */
        public function import_theme_templates( $files, & $db ) {

                /**
                 * - extract tags
                 * - process scripts/html -- convert links to relative paths
                 * - insert in db
                 *
                 */

                $success =  true;

                $templateData = $this->_extract_tags( $files );

                /* check if valid data array sent */
                if( ! is_null( $templateData ) ) {

                        $galleryTempDB = array();

                        /* Run through template data, save in database array */
                        foreach( $templateData as $type => $templates ) {

                                foreach ( $templates as $tempName => $temp ) {

//                                        /* Make resource links relative to theme folder  -- add {theme:resource} to begining of link */
//                                        $temp['scripts'] = $this->process_links( $temp['scripts'] );

                                        $galleryTempDB[] = array(

                                                //     'gallery_template_id' => $someVal,
                                                'gallery_template_theme_name' => $this->themeName,
                                                'gallery_template_type' => $type,
                                                'gallery_template_name' => $tempName,
                                                'gallery_template_scripts' => $temp['scripts'],
                                                'gallery_template_html' => $temp['html'],
                                                'gallery_template_created' => get_gmt_time(),
                                                'gallery_template_modified' => get_gmt_time(),
                                                //      'gallery_template_is_visible' => 1

                                        );

                                }

                        }


                        $this->ci->gallery_model->set_db( $db );

                        $success = $this->ci->gallery_model->create_templates( $galleryTempDB );

                }
                else {

                        // some error found in extraction process
                        $success = false;
                        $this->ci->notifications->add( 'error', 'Unable to extract theme, errors found' );

                }

                $this->ci->notifications->save();

                return $success;

        }


        /**
         * Creates parse tag for given type and template
         *
         * @param string $type
         * @param string $tempName
         *
         * @return string
         *
         */
        private function _get_parse_tag( $type, $tempName ) {

                /* parse tag = gallery:type#tempname */
                return strtolower( $this->module . ":$type#$tempName" );

        }

        /**
         * Processes href and srcs in given html
         *
         * @param string $html
         *
         * @return string processed html
         */
        public function process_links( $html ) {

                $dom = $this->ci->domparser->str_get_html( $html );

                /* get list of tags whose href and src needs to be modified */
                $searchTagList = $this->ci->config->item( 'search_tag_list', $this->configFile );                   // img|link|script|input
                $externalPrefixes = $this->ci->config->item( 'search_external_prefix_list', $this->configFile );    // http, https, www
                $searchAttribs = $this->ci->config->item( 'search_attrib_list', $this->configFile );                // href, src
                $themeResource = $this->ci->config->item( 'theme_resource_root_parse_tag', $this->configFile );

                foreach ( $searchAttribs  as $attrib ) {

                        /* Search for current attrib Eg. href or src */
                        $tags = $dom->find( "[$attrib]" );

                        /* Run through all tags for current attribute */
                        foreach ( $tags as $tag ) {

                                /* Check if current tag needs to be processed ( check from tag list )*/
                                if(  in_array( $tag->tag , $searchTagList) ) {

                                        if( ! $this->_string_starts_with( $tag->getAttribute( $attrib ), $externalPrefixes )) {

                                                $uri = $tag->getAttribute($attrib);

                                                /**
                                                 * @todo PENDING
                                                 *
                                                 * remove any of these chars ( '.', '/', '\' ) from start of string till text or any other chars starts
                                                 */

                                                $tag->setAttribute( $attrib, "$themeResource/$uri" );

                                        }
                                        else {
                                                /**
                                                 * @todo http OR https OR www found at start of resource url
                                                 * use parse_url() to get host name and perform necessary steps.
                                                 */
                                        }

                                }

                        } // end foreach

                }

                // Save modified html
                $html = $dom->save();

                $dom->clear();
                unset ($dom);

                return $html;

        }


        /**
         * Case insensitive search to check if array elements are present at start of string (haystack)
         * @param array $needle List of start to elements to search FOR
         * @param string $haystack string to search in
         * @return bool true if found at start of string, false if NOt.
         */
        private function _string_starts_with( $haystack = null, $needles = array() ) {

                $isFound = false;
                foreach ($needles as $value) {
                        if( substr( strtolower($haystack), 0, strlen($value) ) == strtolower($value) ) {
                                $isFound = true;
                                break;
                        }
                }
                return $isFound;
        }


        /********** Backups *****************/


////////        /**
////////         * Extracts gallery tags from provided pages
////////         * and saves in array.
////////         * returns null if error occured.
////////         * sets notification warnings and errors
////////         *
////////         * array format:
////////         * templatesData[temp_name] = array (
////////         *      'type' = 'categories'
////////         *      'html' = '..........'
////////         *      'scripts' = '........'
////////         *      )
////////         *
////////         * @param array $templates List of file paths to process
////////         *
////////         * @return array|null returns completed array in above format, OR returns null on failure
////////         */
////////        private function _extract_tags( $templates ) {
////////
////////                $success = true;
////////                $tempData = array();
////////
////////                /* Get config values */
////////                $galleryTag = $this->ci->config->item( 'themes_extract_tag', $this->configFile );                       // = gallery
////////                $extractTempIdent = $this->ci->config->item( 'themes_extract_tag_temp_ident', $this->configFile );      // = template
////////                $extractTypeIdent = $this->ci->config->item( 'themes_extract_tag_type_ident', $this->configFile );      // = type
////////                $tagTypeScript = $this->ci->config->item( 'themes_extract_type_script', $this->configFile );                  // = script
////////                $types = $this->ci->config->item( 'themes_extract_type_values', $this->configFile );                    // array ( categories, galleries, images )
////////
////////                /* Run through each file */
////////                foreach ( $templates as $file ) {
////////
////////                        $dom = $this->ci->domparser->file_get_html( $file );
////////
////////                        $searchString = $galleryTag . "[$extractTempIdent]";    // = gallery[template] --> search for all <gallery template='...'>
////////
////////                        $tags = $dom->find( $searchString ); // find <gallery template='...'>
////////
////////                        /* Check if extract tags found */
////////                        if( count( $tags ) > 0 ) {
////////
////////                                /* Run through all tags found */
////////                                foreach ( $tags as $tag ) {
////////
////////                                        $tempName = $tag->getAttribute( $extractTempIdent );    // get value for 'template' attribute
////////
////////                                        /* Check if extract tag has 'type' attribute */
////////                                        if( $tag->hasAttribute( $extractTypeIdent ) ) {
////////
////////                                                $type = $tag->getAttribute( $extractTypeIdent );
////////
////////                                                //$tempData[ $tempName ]['template'] = $tag->getAttribute( $extractTempIdent );
////////                                                $tempData[ $tempName ] = array(
////////                                                        'type' => null,
////////                                                        'html' => null,
////////                                                        'scripts' => null
////////                                                );
////////
////////                                                /* Check if type = categories OR category OR image */
////////                                                if( in_array( $type, $types) ) {
////////
////////                                                        // type = categories OR galleries OR images
////////                                                        $tempData[ $tempName ]['type'] = $type;
////////                                                        $tempData[ $tempName ]['html'] = $tag->innertext;
////////
////////                                                }
////////                                                elseif( $type == $tagTypeScript )       // check if type='script'
////////                                                {
////////                                                        // type = script
////////
////////                                                        /* Concatinate scripts for similar templates */
////////                                                        if( ! isset ( $tempData[ $tempName ]['scripts'] ) ) {
////////
////////                                                                $tempData[ $tempName ]['scripts'] = $tag->innertext;
////////
////////                                                        }
////////                                                        else {
////////
////////                                                                $tempData[ $tempName ]['scripts'] .= "\n" . $tag->innertext;
////////                                                        }
////////
////////                                                }
////////                                                else {
////////
////////                                                        // Tags that do not have a valid type are ignored
////////                                                        $filename = pathinfo( $file, PATHINFO_BASENAME );
////////                                                        $this->ci->notifications->add( 'warning', "Invalid tag found in '$filename', template = '$tempName'" );
////////                                                }
////////
////////                                        }
////////                                        else {
////////
////////                                                $filename = pathinfo( $file, PATHINFO_BASENAME );
////////                                                $this->ci->notifications->add( 'error', "Type attribute not specified in '$filename'" );
////////                                                $success = false;
////////                                        }
////////
////////                                } // end foreach ( for tag iteration )
////////
////////                        }
////////                        else {
////////                                // no extract tags found in file
////////                                $filename = pathinfo( $file, PATHINFO_BASENAME );
////////                                $this->ci->notifications->add( 'warning', "No extract tags found in '$filename'" );
////////                        }
////////
////////                        $dom->clear();
////////                        unset ($dom);
////////
////////                } // end foreach ( for file iteration )
////////
////////
//////////                echo "completed";
//////////
//////////                echo '<div style="padding-left: 100px;">';
//////////                foreach( $tempData as $template => $data ){
//////////
//////////                        echo "<hr/><br/><b><font color='blue' size='6'>Template : $template</font></b><br/>";
//////////
//////////                        echo "<label>script :</label><br/>";
//////////                        echo "<textarea cols='70' rows='20'>". $data['scripts'] ."</textarea><br/>";
//////////
//////////                        echo "<label>html :</label><br/>";
//////////                        echo "<textarea cols='70' rows='20'>". $data['html'] ."</textarea><br/>";
//////////
//////////                }
//////////
//////////                echo "</div>";
////////                $this->ci->notifications->save();
////////
////////                return $success ? $tempData : null;
////////        }
////////
////////
////////        /**
////////         * extracts content for template data,
////////         * processes href and src for relative parsetag links,
////////         * Stores templates in database,
////////         *
////////         * @param array $templates List of template files to process,
////////         * @param ref $db database object for transactions
////////         *
////////         * @return bool true on success, false on failure
////////         */
////////        public function import_theme_templates( $templates, & $db ) {
////////
////////                /**
////////                 * - extract tags
////////                 * - process scripts/html -- convert links to relative paths
////////                 * - insert in db
////////                 *
////////                 */
////////
////////                $success =  true;
////////////////
////////                $templateData = $this->_extract_tags( $templates );
////////
////////                /* check if valid data array sent */
////////                if( ! is_null( $templateData ) ) {
////////
////////                        $galleryTempDB = array();
////////
////////                        //$this->ci->load->helper('date');
////////
////////                        /* Run through template data, save in database array */
////////                        foreach( $templateData as $template => $temp ) {
////////
////////                                // $parseTag = $this->_get_parse_tag( $temp['type'], $template );
//////////                                $temp['scripts'] = $this->process_links( $temp['scripts'] );
////////
////////                                $galleryTempDB[] = array(
////////
////////                                        //     'gallery_template_id' => $someVal,
////////                                        'gallery_template_theme_name' => $this->themeName,
////////                                        'gallery_template_type' => $temp['type'],
////////                                        'gallery_template_name' => $template,
////////                                        'gallery_template_scripts' => $temp['scripts'],
////////                                        'gallery_template_html' => $temp['html'],
////////                                        //DELETED THIS COLUMN 'gallery_template_parse_tag' => $parseTag,
////////                                        'gallery_template_created' => get_gmt_time(),
////////                                        'gallery_template_modified' => get_gmt_time(),
////////                                        //      'gallery_template_is_visible' => 1
////////
////////                                );
////////
////////                        }
////////
////////                        $this->ci->gallery_model->set_db( $db );
////////
////////                        $success = $this->ci->gallery_model->create_templates( $galleryTempDB );
////////
////////                }
////////                else {
////////
////////                        // some error found in extraction process
////////                        $success = false;
////////                        $this->ci->notifications->add( 'error', 'Unable to extract theme, errors found' );
////////
////////                }
////////
////////                $this->ci->notifications->save();
////////
////////                return $success;
////////
////////        }


}
?>