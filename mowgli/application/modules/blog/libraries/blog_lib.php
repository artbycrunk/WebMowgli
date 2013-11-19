<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of blog_lib
 *
 * @author Lloyd
 */
class blog_lib {

        private $module = 'blog';
        private $ci;

        public function __construct() {

                $this->ci = & get_instance();

                // constants
                // delimiter for tags
                !defined('BLOG_TAGS_DELIMITER') ? define('BLOG_TAGS_DELIMITER', ",") : null;

                // load models
                $this->ci->load->model('blog/blog_model');
        }

        /**
         * Processes tagString ( comma separated )
         *
         * @param int $postId
         * @param string $tagString
         * @param ref $db referance of current db object being used ( required to maintain instance of transaction )
         *
         * @return bool
         */
        public function set_post_tag_assoc($postId, $tagString, & $db) {

                $success = true;

                $this->ci->blog_model->set_db($db);

                // separate tags by comma ( , )
                $tagNames = explode(BLOG_TAGS_DELIMITER, $tagString);



                if (is_array($tagNames)) {

                        $tagsDb = array();
                        $tagSlugs = array();

                        // create tag db array using tag names
                        foreach ($tagNames as $tagName) {

                                // ignore tag if it has only white-spaces or empty string
                                if (trim($tagName) != '') {
                                        // convert to slug type ( remove special chars, add dash wherever required )
                                        $slug = $this->get_tag_slug_from_name($tagName);

                                        $tagsDb[] = array(
                                            'blog_tag_name' => $tagName,
                                            'blog_tag_slug' => $slug
                                        );

                                        $tagSlugs[] = $slug;
                                }
                        }

                        if (count($tagsDb) > 0) {

                                // Insert tags, required for foreign key constraint
                                // INSERT IGNORE used here ( will NOT insert if a particular tag_slug already exists )
                                $this->ci->blog_model->insert_tags($tagsDb);

                                $tagSlugs = array_unique($tagSlugs);

                                // create association between post and tag_slug
                                $success = $this->ci->blog_model->set_post_tag_assoc($postId, $tagSlugs);
                        }
                } else {
                        $success = true; // no tags added
                }

                return $success;
        }

        /**
         * Gets Tags for a particular post in the string format
         * Eg. Tag 1, Tag 2, Tag 3
         *
         * @return string
         */
        public function get_post_tags_assoc($postId) {

                $tagString = null;

                $tagDetails = $this->ci->blog_model->get_post_tags_assoc($postId);

                if (!is_null($tagDetails)) {

                        $seperator = BLOG_TAGS_DELIMITER . " ";
                        foreach ($tagDetails as $tags) {

                                $tagString .= $tags['name'] . $seperator;
                        }

                        // remove extra ending ", " from string
                        $tagString = substr($tagString, 0, - strlen($seperator));
                }

                return $tagString;
        }

        /**
         * Converts a tag Name to its slug form
         * - trims whitespaces
         * - converts to lowercase
         * - convert to slug form
         *
         * @param string $tagName
         * @return string
         */
        private function get_tag_slug_from_name($tagName) {

                return url_title(strtolower(trim($tagName)));
        }

}

?>
