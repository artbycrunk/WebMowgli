<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of tags_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class test_model extends Site_Model {

	public function between( $start, $end ){

		$rows = null;
// WHERE (  post.blog_post_created BETWEEN STR_TO_DATE('$start','%Y-%m-%d') AND STR_TO_DATE('$end','%Y-%m-%d') )
		$sql = "

SELECT SQL_CALC_FOUND_ROWS
post.blog_post_title AS title,
post.blog_post_created AS created

FROM (blog_posts AS post)
LEFT JOIN blog_post_tags AS tag_rel ON tag_rel.blog_post_tag_post_id = post.blog_post_id
LEFT JOIN blog_tags AS tag ON tag.blog_tag_slug = tag_rel.blog_post_tag_tag_slug
LEFT JOIN blog_post_categs AS categ_rel ON categ_rel.blog_post_categ_post_id = post.blog_post_id
LEFT JOIN blog_categories AS categ ON categ.blog_categ_slug = categ_rel.blog_post_categ_categ_slug
LEFT JOIN users_auth AS author ON author.user_auth_username = post.blog_post_author_username

WHERE (  post.blog_post_created BETWEEN STR_TO_DATE('$start','%Y-%m-%d') AND STR_TO_DATE('$end','%Y-%m-%d') )
AND `post`.`blog_post_status` = 'published'

GROUP BY post.blog_post_id
ORDER BY post.blog_post_created DESC
";

		$query = $this->db->query($sql);

		if( $query->num_rows() > 0 ){

			$rows = $query->result_array();

		}

		return $rows;
	}
}
?>
