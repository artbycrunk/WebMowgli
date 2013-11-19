<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// note: type='ajax_content' has been depricated, works by default, can be safely deleted
$inlineLinks = "type='ajax_content'";

// default number of rows per page
$config['per_page'] = WM_PAGINATION_LIMIT;

$config['num_links'] = 5;

/*
// display config
//$config['full_tag_open'] = '<div class="pagination">';
//$config['full_tag_close'] = '<span class="clear"></span></div>';

$config['first_link'] = '&laquo; First';
$config['first_tag_open'] = "<a title='First Page' class='pagination-link' $inlineLinks >";
$config['first_tag_close'] = '</a>&nbsp;&nbsp;';

$config['prev_link'] = '&laquo; Previous';
$config['prev_tag_open'] = "&nbsp;&nbsp;<a title='Previous Page' class='pagination-link' $inlineLinks>";
$config['prev_tag_close'] = '</a>&nbsp;&nbsp;';

$config['next_link'] = 'Next &raquo;';
$config['next_tag_open'] = "&nbsp;&nbsp;<a title='Next Page' class='pagination-link' $inlineLinks ";
$config['next_tag_close'] = '</a>&nbsp;&nbsp;';

$config['last_link'] = 'Last &raquo;';
$config['last_tag_open'] = "&nbsp;&nbsp;<a title='Last Page' class='pagination-link' $inlineLinks>";
$config['last_tag_close'] = '</a>&nbsp;&nbsp;';

// digit tags
$config['num_tag_open'] = "&nbsp;&nbsp;<a $inlineLinks>";
$config['num_tag_close'] = '</a>&nbsp;&nbsp;';

// current page tag
$config['cur_tag_open'] = ' <b>';
$config['cur_tag_close'] = '</b> ';

*/

// display config
//$config['full_tag_open'] = '<div class="pagination">';
//$config['full_tag_close'] = '<span class="clear"></span></div>';

$config['first_link'] = 'First';
$config['first_tag_open'] = "<li>";
$config['first_tag_close'] = '</li>';

$config['prev_link'] = 'Previous';
$config['prev_tag_open'] = "<li>";
$config['prev_tag_close'] = '</li>';

$config['next_link'] = 'Next';
$config['next_tag_open'] = "<li>";
$config['next_tag_close'] = '</li>';

$config['last_link'] = 'Last';
$config['last_tag_open'] = "<li>";
$config['last_tag_close'] = '</li>';

// digit tags
$config['num_tag_open'] = "<li>";
$config['num_tag_close'] = '</li>';

// current page tag
$config['cur_tag_open'] = '<li class="active"><a href="#">';
$config['cur_tag_close'] = '</a></li>';

?>