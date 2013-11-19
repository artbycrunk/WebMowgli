<?php

if (!defined('BASEPATH'))
        exit('No direct script access allowed');

/**
 * Description of events -change made here
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class events extends Admin_Controller implements I_Admin_Extract {

        private $module = "events";

        public function __construct() {

                parent::__construct();

//                $this->config->load( $this->configFile, true, true, $this->module );
                // define constants
                !defined('EVENTS_URI_ADD') ? define('EVENTS_URI_ADD', "admin/" . $this->module . "/add") : null;
                !defined('EVENTS_URI_ADD_SAVE') ? define('EVENTS_URI_ADD_SAVE', "admin/" . $this->module . "/save") : null;
                !defined('EVENTS_URI_EDIT') ? define('EVENTS_URI_EDIT', "admin/" . $this->module . "/edit") : null;
                !defined('EVENTS_URI_EDIT_SAVE') ? define('EVENTS_URI_EDIT_SAVE', "admin/" . $this->module . "/save") : null;
                !defined('EVENTS_URI_DELETE') ? define('EVENTS_URI_DELETE', "admin/" . $this->module . "/delete") : null;

                !defined('EVENTS_ACTION_ADD') ? define('EVENTS_ACTION_ADD', "add") : null;
                !defined('EVENTS_ACTION_EDIT') ? define('EVENTS_ACTION_EDIT', "edit") : null;

		!defined('EVENTS_DEFAULT_TEMPLATE') ? define('EVENTS_DEFAULT_TEMPLATE', "default") : null;

                // load libraries
		$this->load->library('templates');
                $this->load->library('date_time');
                $this->load->library('json_response');
                $this->load->library('form_response');
                $this->load->library('form_validation');
                // $this->form_validation->CI = & $this;;           // required for form validation to work with hmvc
                // Load models
                $this->load->model('events/events_model');

                $this->parseData['module:resource'] = site_url(module_resource_uri($this->module));

                $this->parseData['default-message'] = '';
        }

        public function _extract_content($tempId, $parseTag, $innerText, & $db) {

                $tagId = null;
                $tagParts = explode(':', $parseTag);
                $tagName = isset($tagParts[1]) ? $tagParts[0] . ':' . $tagParts[1] : $tagParts[0];
                $tagId = isset($tagParts[2]) ? $tagParts[2] : "null";
		$template = isset($tagParts[1]) ? $tagParts[1] : EVENTS_DEFAULT_TEMPLATE;

		// add template, if template exists --> do not add new one, simply get tempId
		$this->templates->set_db($db);
		$tempId = $this->templates->add_module_template($this->module, $template, $innerText);

                $tag = array(
                    'tag_id' => null,
                    'tag_module_name' => $this->module,
                    'tag_temp_id' => $tempId,
                    'tag_keyword' => $parseTag,
                    'tag_name' => $tagName,
                    'tag_data_id' => $tagId,
//                    'tag_description' => "tag for Events, using module template $template";
                );

                $this->load->model('tags_model');
                $this->tags_model->set_db($db);
                $tagId = $this->tags_model->create_tag($tag);

                return $tagId > 0 ? $tagId : null;
        }

        public function manage() {

                $pageName = "Manage Events";

                $events = $this->events_model->get_events();

                $output = null;

                if (!is_null($events)) {

                        foreach ($events as $event) {

                                $start = $this->date_time->gmt_to_local($event['start']);
                                $end = $this->date_time->gmt_to_local($event['end']);

                                $this->parseData['events'][] = array(
                                    'event:edit_link' => site_url(EVENTS_URI_EDIT . "/" . $event['id']),
                                    'event:delete_link' => site_url(EVENTS_URI_DELETE . "/" . $event['id']),
                                    'event:id' => $event['id'],
                                    'event:name' => $event['name'],
                                    'event:slug' => $event['slug'],
                                    'event:venue' => $event['venue'] == '' ? '<i style="font-size:11px">Not specified</i>' : $event['venue'],
                                    'event:day' => $this->date_time->format($start, 'j'),
                                    'event:month' => $this->date_time->format($start, 'M'),
                                    'event:start' => $this->date_time->datetime($start),
                                    'event:end' => $this->date_time->datetime($end),
                                    'event:description' => $event['description']
                                );
                        }

                        $output = $this->parser->parse('events/manage.php', $this->parseData, true);
                } else {

                        $output = "<p>No events created</p>";
                }

//                $this->parseData['events'] = $parseEvents;


                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
        }

        public function add() {

                $pageName = "Add Event";

                // set form metadata parse values
                $this->parseData['event:post_url'] = site_url(EVENTS_URI_ADD_SAVE);
                $this->parseData['event:action_type'] = EVENTS_ACTION_ADD;
                $this->parseData['event:id'] = 'null';

                // initialize form values
                $this->parseData['event:name'] = '';
                $this->parseData['event:slug'] = '';
                $this->parseData['event:venue'] = '';
                $this->parseData['event:start'] = '';
                $this->parseData['event:end'] = '';
                $this->parseData['event:description'] = '';

                $this->parseData['event:start:time'] = '';
                $this->parseData['event:end:time'] = '';

                $output = $this->parser->parse('events/edit.php', $this->parseData, true);

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
        }

        public function edit($eventId = null) {

                $pageName = "Edit Event";
                $output = null;

                $eventId = isset($_POST['event_id']) ? $this->input->post('event_id') : $eventId;

                $this->parseData['event:post_url'] = site_url(EVENTS_URI_EDIT_SAVE);
                $this->parseData['event:action_type'] = EVENTS_ACTION_EDIT;
                $this->parseData['event:id'] = $eventId;

                $event = $this->events_model->get_event($eventId);

                if (!is_null($event)) {

//                        $dateFormat = "m/d/Y H:i";
                        $dateFormat = "Y-m-d";
                        $timeFormat = "H:i";

                        // initialize form values
                        $this->parseData['event:name'] = $event['name'];
                        $this->parseData['event:slug'] = $event['slug'];
                        $this->parseData['event:venue'] = $event['venue'];
                        $this->parseData['event:start'] = $this->date_time->gmt_to_local($event['start'], null, $dateFormat);
                        $this->parseData['event:end'] = $this->date_time->gmt_to_local($event['end'], null, $dateFormat);
                        $this->parseData['event:description'] = $event['description'];

                        $this->parseData['event:start:time'] = $this->date_time->gmt_to_local($event['start'], null, $timeFormat);
                        $this->parseData['event:end:time'] = $this->date_time->gmt_to_local($event['end'], null, $timeFormat);

                        $output = $this->parser->parse('events/edit.php', $this->parseData, true);
                } else {
                        // event NOT available in database OR error
                        $output = "<p>Event unavailable</p>";
                }

                $this->load->library('admin/admin_views');
                echo $this->admin_views->get_main_content($this->parseData, $pageName, $output);
        }

        public function save() {

                /*
                 * @todo
                 * Validation
                 *      - check slug is in correct form
                 *      - start/end time should be in correct format
                 *      - start/end time, mandatory
                 *      - end time greater than start time
                 *      - convert date time to local time
                 */

                $actionType = $this->input->post('action_type');
                $eventId = $this->input->post('event_id');

                $this->form_validation->set_rules('name', 'Name', 'required');
                $this->form_validation->set_rules('slug', 'Slug', 'required');
                $this->form_validation->set_rules('venue', 'Venue', '');
                $this->form_validation->set_rules('start', 'Start Date-time', 'required');
                $this->form_validation->set_rules('end', 'End Date-time', 'required|callback__validate_start_end_date');
                $this->form_validation->set_rules('description', 'Description', '');

                /* Validations - validate form */
                if ($this->form_validation->run()) {

                        /* Validation successfull */

                        $startTime = $this->input->post('start') == '' ? null : $this->input->post('start');
                        $endTime = $this->input->post('end') == '' ? null : $this->input->post('end');

                        // conver to GMT time for database storing
                        $startTime = $this->date_time->local_to_gmt($startTime);
                        $endTime = $this->date_time->local_to_gmt($endTime);

                        $eventDb = array(
                            //"event_id" => null,
                            "event_name" => $this->input->post('name'),
                            "event_slug" => $this->input->post('slug'),
                            "event_venue" => $this->input->post('venue'),
                            "event_start" => $startTime,
                            "event_end" => $endTime,
                            "event_description" => $this->input->post('description')
                        );

                        $success = false;

                        switch ($actionType) {
                                case EVENTS_ACTION_ADD:

                                        $success = $this->events_model->create_event($eventDb);
                                        break;

                                case EVENTS_ACTION_EDIT:

                                        $success = $this->events_model->edit_event($eventId, $eventDb);
                                        break;

                                default:
                                        $success = false;
                                        break;
                        }
//                        $success = ( $actionType == EVENTS_ACTION_ADD ) ? $this->events_model->create_event( $eventDb ) : false;
//                        $success = ( $actionType == EVENTS_ACTION_EDIT ) ? $this->events_model->edit_event( $eventId, $eventDb ) : false;

                        if ($success) {

                                $this->form_response->set_message(WM_STATUS_SUCCESS, "Event saved");
                        } else {

                                $this->form_response->set_message(WM_STATUS_ERROR, "Unable to save event");
                        }
                } else {
                        // form validation fail

                        $this->form_response
                                ->set_message( WM_STATUS_ERROR, "Invalid form fields")
                                ->set_redirect(null)
                                ->add_validation_msg("name", form_error("name"))
                                ->add_validation_msg("slug", form_error("slug"))
                                ->add_validation_msg("venue", form_error("venue"))
                                ->add_validation_msg("start", form_error("start"))
                                ->add_validation_msg("end", form_error("end"))
                                ->add_validation_msg("description", form_error("description"));
                }

                $this->form_response->send();
        }

        public function delete($eventId = null) {

//                $pageName = "Delete Event";
                // if post value set, use post value, else use parameter value
                $eventIds = isset($_POST['event_ids']) ? $this->input->post('event_ids') : $eventId;
                $eventIds = is_array($eventIds) ? $eventIds : array($eventIds);

                // delete event, check if successfull
                if ($this->events_model->delete_events($eventIds)) {

//                        echo "success";
                        $this->json_response->set_message(WM_STATUS_SUCCESS, "Event(s) deleted");
                } else {
                        // event NOT available in database OR error
//                        echo "error";
                        $this->json_response->set_message( WM_STATUS_ERROR, "Event(s) could not be deleted");
                }

                $this->json_response->send();
//                $this->load->library('admin/admin_views' );
//                echo $this->admin_views->get_main_content( $this->parseData, $pageName, $output );
        }

        public function _validate_start_end_date() {

                $start = $this->input->post('start');
                $end = $this->input->post('end');

                $startUnix = strtotime($start);
                $endUnix = strtotime($end);

                $success = true;

                if ($endUnix < $startUnix) {

                        $this->form_validation->set_message('_validate_start_end_date', 'The End date-time cannot be before the Start date-time');

                        $success = false;
                }

                return $success;
        }

}

?>
