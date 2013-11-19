<?php

/**
 * Description of Content
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
define('CONTENT_ALLOWED_TYPES', 'article|image|video|flash|other');

//define( 'MODULE_CONTENT', 'content');

class Content extends Admin_Controller implements I_Admin_Extract {

        private $module = 'content';

        /**
         * creates tag in tags table, returns id of tag,
         * if error occured, returns null
         *
         * @param string $tempId id of current template being processed
         * @param string #parseTag parse tag string found in id='....' attribute of extract tag
         * @param string $innerText inner html of current extract tag
         * @param ref $db current db instance for db transactions
         *
         * @return string|null returns tag id on success OR null on failure
         */
        public function _extract_content($tempId, $parseTag, $innerText, & $db) {

                $tagId = null;
                $parseTagParts = explode(':', $parseTag);


                // Check if 2nd element mentioned in parse tag, return parsetag {first:second:id}
                if (isset($parseTagParts[1])) {

                        // Check if 2nd element is among allowed types Eg. article, image, video, other
                        if (in_array(strtolower($parseTagParts[1]), explode(STRING_TO_ARRAY_DELIMITER, CONTENT_ALLOWED_TYPES))) {

                                $this->load->model('content/content_model');
                                $this->content_model->set_db($db);

                                $dbArray = array(
                                    'content_id' => null,
                                    'content_type' => $parseTagParts[1],
                                    //     'content_uid' => null,
                                    'content_data' => $innerText,
                                    'content_created' => get_gmt_time(),
                                    'content_modified' => get_gmt_time(),
                                    'content_is_visible' => true
                                );
                                $contentId = $this->content_model->create_content($dbArray);

                                $tagName = implode(':', $parseTagParts);
                                $tagKeyword = "$tagName:$contentId";

                                $tag = array(
                                    'tag_id' => null,
                                    'tag_module_name' => $this->module,
                                    //    'tag_temp_id' => null, //$tempId,
                                    'tag_keyword' => $tagKeyword,
                                    'tag_name' => $tagName,
                                    'tag_data_id' => $contentId,
                                        //    'tag_description' => null
                                );

                                $tagId = $this->content_model->create_tag($tag);
                        }
                }

                return $tagId;
        }

        public function update_content() {

                $id = $this->input->post('id');
                $content = $this->input->post('content');

                $this->load->library('json_response');

                $this->load->model('content/content_model');
                $success = $this->content_model->update_content($id, $content);

                if ($success) {

                        $this->json_response->set_message(WM_STATUS_SUCCESS, "Content successfully updated");
                } else {
                        $this->json_response->set_message(WM_STATUS_ERROR, "Unable to update content, please try again.");
                }

                $this->json_response->send();

//                echo ( $success ?  "Content successfully updated" : "Unable to update content, please try again." );
        }

}

?>