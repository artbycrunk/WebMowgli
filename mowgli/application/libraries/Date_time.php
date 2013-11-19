<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of datetime
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
! defined( 'DATETIME_FORMAT' ) ? define( 'DATETIME_FORMAT', 'Y-m-d H:i:s' ) : null; // 2012-01-09 15:03:59
! defined( 'DATETIME_ONE_HOUR' ) ? define( 'DATETIME_ONE_HOUR', 3600 ) : null; // convert 1 hour to seconds
! defined( 'DATETIME_IDENT_LOCAL' ) ? define( 'DATETIME_IDENT_LOCAL', 'local' ) : null;
! defined( 'DATETIME_IDENT_GMT' ) ? define( 'DATETIME_IDENT_GMT', 'gmt' ) : null;

! defined( 'DATETIME_IDENT_FORMAT_DATE' ) ? define( 'DATETIME_IDENT_FORMAT_DATE', 'date' ) : null;
! defined( 'DATETIME_IDENT_FORMAT_TIME' ) ? define( 'DATETIME_IDENT_FORMAT_TIME', 'time' ) : null;
// DATETIME_IDENT_LOCAL

class Date_Time {

        /**
         *
         *
         * General
         - set php, mysql timezones to UTC 0:0 / GMT (  this is for functions like now or time to get server time in GMT )
         - If DST = true ( add DST offset ) to get actual time

         Input
         - get time ( in any format / current time )
         - convert time to UTC/GMT
         - save in db

         Output
         - get datetime from db
         - get offset from settings
         - add offset to time from db
         - display time
         *
         *
         * Functions
         *
         * - construct
         * - get_setting_timezone
         * - get_setting_offset
         *
         * - now( $format = "default format" )
         * - to_gmt( $string, $format = "default" )
         * - to_local( $datetime, $timezone = null, $dst = false, $dstOffset = +1hr )
         * - date()
         * - time()
         * - timestamp()
         */

        private $ci;
        private $timezone;      // ci timezone code, value set by user preference, from db
        private $offset;        // number of hours offset ( Eg. -2, 0, 2, 3.5  etc )
        private $isDst;         // boolean to use dst or not
        private $dstOffset;     // number of hours offset

        private $dateformat;    // date format set in db
        private $timeformat;    // time format set in db
        private $format;        // constant ( DATETIME_FORMAT ) system format for date time calculations, can be overridden

        private $timestamp;     // timestamp being processed


        public function __construct() {

                $this->ci = & get_instance();

                $this->ci->load->library('site_settings');
                $this->ci->load->helper('date');

                $this->_initialize();
        }

        private function _initialize() {

                $settingsData = $this->ci->site_settings->get_data( WM_SET_CATEG_DATETIME );

                $settings = array();
                foreach ( $settingsData as $set ) {
                        $settings[ $set['key'] ] = $set['value'];
                }

                // set timezone, note: this also sets offset internally
                $this->set_timezone( $settings['timezone'] );
                $this->set_offset( timezones( $settings['timezone'] ) );

                $this->set_isDst( $settings['dst_used'] );
                $this->set_dstOffset( $settings['dst_offset'] );

                $this->set_format( DATETIME_FORMAT );           // set default system format
                $this->set_dateformat( $settings['format_date'] );
                $this->set_timeformat( $settings['format_time'] );

                $this->set_timestamp( $this->now( DATETIME_IDENT_GMT, DATETIME_FORMAT ) );
        }

        public function set_timezone($timezone) {

                $this->timezone = $timezone;

                // set timezone offset internally
                $this->set_offset( timezones( $timezone ) );

                return $this;
        }
        public function set_offset($offset) {
                $this->offset = $offset;
                return $this;
        }
        public function set_isDst($isDst) {
                $this->isDst = $isDst;
                return $this;
        }
        public function set_dstOffset($dstOffset) {
                $this->dstOffset = $this->isDst ? $dstOffset : 0;
                return $this;
        }
        public function set_format($format) {
                $this->format = $format;
                return $this;
        }
        public function set_dateformat($dateformat) {
                $this->dateformat = $dateformat;
                return $this;
        }
        public function set_timeformat($timeformat) {
                $this->timeformat = $timeformat;
                return $this;
        }
        public function set_timestamp($timestamp) {
                $this->timestamp = $timestamp;
                return $this;
        }


        /**
         * Gets current GMT time, using set format
         *
         * @param string $zone 'gmt' OR 'local'
         * @param string $format format for date time, default will be used if not provided
         *
         * @return string current datetime string in required format
         */
        public function now( $zone = 'gmt', $format = null  ) {

                $now = null;

                $format = is_null( $format ) ? $this->format : DATETIME_FORMAT;

                // get GMT time
                $nowGmt = date_format( date_create(), $format );

                // if zone is set as 'local' convert gmt time to local time
                if( strtolower( $zone ) == DATETIME_IDENT_LOCAL ) {

                        $now = $this->gmt_to_local( $nowGmt, $this->offset, $this->format );

                }
                else{
                        $now = $nowGmt;
                }

                return $now;

        }

        /**
         * convert provided date time string to either date OR time or 'date time' format
         *
         * @param string $dateTime date time string
         * @param string $format 'date' or 'time' or null
         *
         * @return string date/time string
         */
        private function _format( $dateTime, $format = null ) {

                $combinedFormat = $this->dateformat . ", " . $this->timeformat;

                switch ( $format ) {

                        // date
                        case DATETIME_IDENT_FORMAT_DATE:

                                $format = $this->dateformat;
                                break;

                        // time
                        case DATETIME_IDENT_FORMAT_TIME:

                                $format = $this->timeformat;
                                break;

                        // use format provided by user
                        default:

                                // if no format provided, use combined format by default,
                                // else use user provided format
                                $format = is_null( $format ) ? $combinedFormat : $format;
                                break;
                }

                return date( $format, strtotime( $dateTime ) );

        }

        /**
         * Display date in date format ( set by user )
         * if no date provided, use gmt( or preset ) datetime string
         *
         * @param string $dateTime datetime string
         *
         * @return string date in user defined format
         */
        public function date( $dateTime = null ){

                $dateTime = is_null( $dateTime ) ? $this->timestamp : $dateTime;

                return $this->_format( $dateTime, DATETIME_IDENT_FORMAT_DATE );

        }

        /**
         * Display time in time format ( set by user )
         * if no time provided, use gmt( or preset ) time
         *
         * @param string $dateTime datetime string
         *
         * @return string time in user defined format
         */
        public function time( $dateTime = null ){

                $dateTime = is_null( $dateTime ) ? $this->timestamp : $dateTime;

                return $this->_format( $dateTime, DATETIME_IDENT_FORMAT_TIME );

        }

        /**
         * Display combined date & time in combined datetime format ( set by user )
         * if no datetime provided, use gmt( or preset ) time
         *
         * @param string $dateTime datetime string
         *
         * @return string 'date time' in user Combined defined format
         */
        public function datetime( $dateTime = null ){

                $dateTime = is_null( $dateTime ) ? $this->timestamp : $dateTime;

                return $this->_format( $dateTime, null );

        }

        /**
         * Convert date time string to provided/default format
         *
         * @param string $dateTime datetime string
         * @param string $format php standard formats
         *
         * @return string 'date time' in user Combined defined format
         */
        public function format( $dateTime = null, $format = null ){

                $dateTime = is_null( $dateTime ) ? $this->timestamp : $dateTime;
                $format = is_null( $format ) ? $this->format : $format;

                return $this->_format( $dateTime, $format );

        }

        /**
         * Converts date time to local,
         * depending on timezone and dst set in settings
         *
         * @param string(datetime) $datetime date time string, will use current time if not specified
         * @param float $offset number of hours offset from UTC, ( Eg. +5.5, -2 etc )
         * @param string $format php format for date time display
         *
         * @return string retuns local date time
         */
        public function gmt_to_local( $gmtDateTime, $offset = null, $format = null ) {

//                $datetime = is_null( $datetime ) ? $this->timestamp : $datetime;
                $offset = is_null( $offset ) ? $this->offset : $offset;
                $format = is_null( $format ) ? $this->format : $format;

                $unixTime = strtotime( $gmtDateTime );
                $localUnixTime = $unixTime + DATETIME_ONE_HOUR * ( + $offset - $this->dstOffset ); // substract dst --> since local time should reflect dst
                $localTime = date( $format, $localUnixTime );
                return $localTime;
        }

        public function local_to_gmt( $localDateTime, $offsetHours = null, $format = null ) {

                $offsetHours = is_null( $offsetHours ) ? $this->offset : $offsetHours;
                $format = is_null( $format ) ? $this->format : $format;

                $unixTime = strtotime( $localDateTime );
                // add dst --> since gmt should not reflect dst
                $gmtUnixTime = $unixTime + DATETIME_ONE_HOUR * ( - $offsetHours + $this->dstOffset );
                $gmtTime = date( $format, $gmtUnixTime );

                return $gmtTime;
        }

}


/* End of file datetime.php */
/* Location: ./application/libraries/datetime.php */
?>
