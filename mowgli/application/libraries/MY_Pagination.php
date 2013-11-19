<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_pagination
 *
 * @author Lloyd
 */
class MY_Pagination extends CI_Pagination {

        private $offset = null;

        /**
         * Initialize Preferences
         *
         * @access	public
         * @param	array	initialization parameters
         * @return	void
         */
        function initialize($params = array(), $pageNo = 1) {

                // call parent function, this initializes all values based on config provided
                parent::initialize($params);

                // physically set current page
                $this->cur_page = $pageNo;

                // set offset
                $this->offset = $this->get_offset( $pageNo, $this->per_page);
        }

        /**
         * Get the correct offset value for database and pagination calculation
         *
         * @param int $pageNo thisis hte current page that is being accessed
         * @param int $per_page the number of rows per page, this should be same as database limit
         *
         * @return int offset value
         */
        public function get_offset( $pageNo = 1, $per_page = WM_PAGINATION_LIMIT ) {

                // considering page = 0 OR 1 as page 1
                $pageNo = ( $pageNo <= 1 ) ? 1 : $pageNo;

                // note since least value of $pageNo is always 1, implies that Offset will always be 0 for page 0 and page 1
                return (int)(($pageNo - 1) * $per_page);
        }

        /**
         * Create pagination links, this method override default Codeigniter pagination
         * Creates pagination based on page numbers ( '', 2, 3, 4, 5, 6 . . . .) instead of offsets
         * NOTE: MUST call $this->pagination->initialize( $params = array(), $pageNo = 1 ) method before calling this function
         * NOTE: $config['uri_segment'] is no longer needed, it is NOT used for pagination calculation
         * works with uri segment pagination, NOT tested with query string method
         */
        function create_links() {
                // If our item count or per-page total is zero there is no need to continue.
                if ($this->total_rows == 0 OR $this->per_page == 0) {
                        return '';
                }

                // Calculate the total number of pages
                $num_pages = ceil($this->total_rows / $this->per_page);

                // Is there only one page? Hm... nothing more to do here then.
                if ($num_pages == 1) {
                        return '';
                }

                // Determine the Offset
                $CI = & get_instance();

                if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE) {
                        if ($CI->input->get($this->query_string_segment) != 0) {
//				$this->cur_page = $CI->input->get($this->query_string_segment);
                                $this->offset = $CI->input->get($this->query_string_segment);

                                // Prep the current page - no funny business!
                                $this->offset = (int) $this->offset;
                        }
                } else {
                        // @lloyd: commented below code, not needed, as $offset is set in initialize() method
//                        if ($CI->uri->segment($this->uri_segment) != 0) {
//                                $this->offset = $CI->uri->segment($this->uri_segment);
//
//                                // Prep the current page - no funny business!
//                                $this->offset = (int) $this->offset;
//                        }
                }

                // this is number of links on either side of current page of pagination string
                $this->num_links = (int) $this->num_links;

                if ($this->num_links < 1) {
                        show_error('Your number of links must be a positive number.');
                }

                if (!is_numeric($this->offset)) {
                        $this->offset = 0;
                }

                // Is the page number beyond the result range?
                // If so we show the last page
                if ($this->offset > $this->total_rows) {
                        $this->offset = ($num_pages - 1) * $this->per_page;
                }

                $uri_offset_number = $this->offset;
                $this->cur_page = floor(($this->offset / $this->per_page) + 1);

                // Calculate the start and end numbers. These determine
                // which number to start and end the digit links with
                $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
                $end = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

                // Is pagination being used over GET or POST?  If get, add a per_page query
                // string. If post, add a trailing slash to the base URL if needed
                if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE) {
                        $this->base_url = rtrim($this->base_url) . '&amp;' . $this->query_string_segment . '=';
                } else {
                        $this->base_url = rtrim($this->base_url, '/') . '/';
                }

                // And here we go...
                $output = '';

                // Render the "First" link
                if ($this->first_link !== FALSE AND $this->cur_page > ($this->num_links + 1)) {
                        $first_url = ($this->first_url == '') ? $this->base_url : $this->first_url;
                        $output .= $this->first_tag_open . '<a ' . $this->anchor_class . 'href="' . $first_url . '">' . $this->first_link . '</a>' . $this->first_tag_close;
                }

                // Render the "previous" link
                // @lloyd: calculate previous ONLY if current page is NOT 1 and previous link is enabled
                if ($this->prev_link !== FALSE AND $this->cur_page != 1) {

                        // @lloyd:  ( current offset - number of post in previous page ) = offset of previous page ( i.e. one page less )
                        $prev_offset = $uri_offset_number - $this->per_page;

                        // @lloyd: check if current page is page 2 ( i.e. previos page is First page ) AND first_url is NOT set
                        if ($prev_offset == 0 && $this->first_url != '') {

                                // @lloyd: prevOffset is = 0, implies previous page is first page, hence no trailing numbers after uri ( i.e. first URI )

                                $output .= $this->prev_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->first_url . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
                        } else {
                                // @lloyd: if previous page is page 1( that is first page ) set previous offset for url as ''
                                // @lloyd: if not, set offset for uri normally
//                                $prev_offset = ($prev_offset == 0) ? '' : $this->prefix . $prev_offset . $this->suffix;
                                $prevPageNo = ($prev_offset == 0) ? '' : $this->prefix . ( $this->cur_page - 1 ) . $this->suffix;
                                $output .= $this->prev_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $prevPageNo . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
//                                $output .= $this->prev_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $prev_offset . '">' . $this->prev_link . '</a>' . $this->prev_tag_close;
                        }
                }

                // Render the pages ( digits )
                if ($this->display_pages !== FALSE) {

                        // Write the digit links
                        // @lloyd: $loop_page will hold current iteration ( actual page numbers ) that will be displayed, Eg. 1, 2, 3 . . . . $end
                        for ($loop_page = $start - 1; $loop_page <= $end; $loop_page++) {

                                // lloyd: calculate previous page offset of iteration
                                $currOffset = ($loop_page * $this->per_page);
                                $prevOffset = $currOffset - $this->per_page;

                                // @lloyd: proceede only if previous page is a valid page ( i.e. page 1 onwards )
                                if ($prevOffset >= 0) {

                                        if ($this->cur_page == $loop_page) {

                                                // @lloyd: current page is same as loop page in pagination
                                                // highlight current pageNo in pagination
                                                $output .= $this->cur_tag_open . $loop_page . $this->cur_tag_close; // Current page
                                        } else {
                                                // @lloyd: current page is NOT the pagination page number iteration
                                                // if previous page is first page ( 1.e. page 1 ), then $loopUriNo = ''
                                                // else $loopUriNo = $loopPage
//                                                $loopUriNo = ($prevOffset == 0) ? '' : $prevOffset;
//                                                $loopUriNo = ($currOffset == 0) ? '' : $loop_page;
                                                $loopUriNo = ($loop_page <= 1) ? '' : $loop_page;

                                                //@lloyd: check if first page NOT defined --> this is page 1
                                                if ($loopUriNo == '' && $this->first_url != '') {

                                                        // @lloyd: page = 1

                                                        $output .= $this->num_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->first_url . '">' . $loopUriNo . '</a>' . $this->num_tag_close;
//                                                        $output .= $this->num_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->first_url . '">' . $loop_page . '</a>' . $this->num_tag_close;
                                                } else {

                                                        // @lloyd: current page is NOT page 1

                                                        $loopUriNo = ($loopUriNo == '') ? '' : $this->prefix . $loopUriNo . $this->suffix;

                                                        // @lloyd:ignore change $loopUriNo to $loop_page in anchor tag
                                                        $output .= $this->num_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $loopUriNo . '">' . $loop_page . '</a>' . $this->num_tag_close;
                                                }
                                        }
                                }
                        }
                }

                // Render the "next" link
                if ($this->next_link !== FALSE AND $this->cur_page < $num_pages) {

                        // @lloyd: change ( $this->cur_page * $this->per_page ) into ( $this->cur_page + 1 )
                        $nextPageNo = $this->cur_page + 1;
                        $output .= $this->next_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $this->prefix . $nextPageNo . $this->suffix . '">' . $this->next_link . '</a>' . $this->next_tag_close;
//                        $output .= $this->next_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $this->prefix . ($this->cur_page * $this->per_page) . $this->suffix . '">' . $this->next_link . '</a>' . $this->next_tag_close;
                }

                // Render the "Last" link
                if ($this->last_link !== FALSE AND ($this->cur_page + $this->num_links) < $num_pages) {

                        // @lloyd: change $prevOffset = ((($num_pages * $this->per_page) - $this->per_page)) into $prevOffset = $num_pages
//                        $prevOffset = (($num_pages * $this->per_page) - $this->per_page);
                        $output .= $this->last_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $this->prefix . $num_pages . $this->suffix . '">' . $this->last_link . '</a>' . $this->last_tag_close;
//                        $output .= $this->last_tag_open . '<a ' . $this->anchor_class . 'href="' . $this->base_url . $this->prefix . $prevOffset . $this->suffix . '">' . $this->last_link . '</a>' . $this->last_tag_close;
                }

                // Kill double slashes.  Note: Sometimes we can end up with a double slash
                // in the penultimate link so we'll kill all double slashes.
                $output = preg_replace("#([^:])//+#", "\\1/", $output);

                // Add the wrapper HTML if exists
                $output = $this->full_tag_open . $output . $this->full_tag_close;

                return $output;
        }

}

?>
