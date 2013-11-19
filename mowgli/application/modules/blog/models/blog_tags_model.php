<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_model
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class Blog_Tags_Model extends Blog_Model {

        public function __construct() {

                parent::__construct();

        }

        /**
         * gets Post details for given Post_Id
         *
         * @param int $postId post id
         * @param string $status usually 'draft' OR 'published', if null --> DO NOT apply status condition
         * @return array|false returns result on success, false on no result
         */
        public function get_post_details($postId, $status = null) {

                $postDetails = null;

                $postId = $this->addslashes($postId);

                /*
                  SELECT
                  blog_post_id                    AS id,
                  blog_post_author_username       AS username,
                  blog_post_title                 AS title,
                  blog_post_slug                  AS slug,
                  blog_post_body                  AS body,
                  blog_post_created               AS created,
                  blog_post_modified              AS modified,
                  blog_post_status                AS status,
                  blog_post_is_comments           AS is_comments

                  FROM blog_post_status

                  WHERE blog_post_id = $postId
                  -- AND WHERE blog_post_status = '$status'

                 */

                $select = "
                        blog_post_id                    AS id,
                        blog_post_author_username       AS username,
                        blog_post_title                 AS title,
                        blog_post_slug                  AS slug,
                        blog_post_body                  AS body,
                        blog_post_created               AS created,
                        blog_post_modified              AS modified,
                        blog_post_status                AS status,
                        blog_post_is_comments           AS is_comments                        
                ";

                $this->db->select($select);
                $this->db->from($this->tables['blog_posts']);
                $this->db->where('blog_post_id', $postId);

                // add status in where clause if condition provided
                if (!is_null($status))
                        $this->db->where('blog_post_status', $status);

                $this->db->limit(1);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $postDetails = $query->row_array();

                        // strip slashes
                        foreach ($postDetails as $key => $value) {
                                $postDetails[$key] = stripslashes($value);
                        }
                }

                return $postDetails;
        }

        /**
         * Gets tags related to a particular post
         * 
         * @param int $postId
         * @return array|null
         */
        public function get_post_tags_assoc($postId) {

                $postId = $this->addslashes($postId);

                $tags = null;

                $select = "
                        blog_tag_name AS name,
                        blog_tag_slug AS slug
                ";

                $this->db->select($select);
                $this->db->from($this->tables['blog_post_tags']);
                $this->db->join($this->tables['blog_tags'], 'blog_post_tag_tag_slug = blog_tag_slug', 'left');
                $this->db->where('blog_post_tag_post_id', $postId);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {

                        $tags = $query->result_array();

                        // strip slashes
                        foreach ($tags as & $tag) {

                                foreach ($tag as $key => $value) {

                                        $tag[$key] = stripslashes($value);
                                }
                        }
                }

                return $tags;
        }

}

?>
