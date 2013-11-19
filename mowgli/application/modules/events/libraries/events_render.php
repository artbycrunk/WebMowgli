<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of events_render
 *
 * @author Lloyd
 * @license Copyright Encube Web Solutions
 * @link http://encube.co.in
 */
class events_render {

	private $ci;
	private $module = 'events';
	private $wordCountLimit = 10;
	private $templateViewDir;
	private $eventTags = array();

	public function __construct($params = null) {

		$this->ci = & get_instance();

		$this->templateViewDir = $this->module . "/page_templates";

		$this->ci->load->library('date_time');
		$this->ci->load->helper('text');
	}

	public function render($template) {

		$events = $this->ci->events_model->get_events();
		$tags = $this->_prepare_tags($events);

		return $this->ci->parser->parse($template, $this->eventTags, true);
	}

	// gets appropriate view file name from tag ( Eg. 'page_templates/sidebar' )
	public function get_view_from_tag($tag) {

		// tag = events:main OR events:sidebar

		$tagParts = explode(":", $tag);
		$viewName = isset($tagParts[1]) ? $tagParts[1] : null;

		return $this->templateViewDir . "/$viewName";
	}

	// processes events from database, creates parse tags array, sets array in $this->events
	public function prepare_tags($events) {

		$parseArray = null;

		if (!is_null($events) AND is_array($events)) {

			foreach ($events as $event) {

				$start = $this->ci->date_time->gmt_to_local($event['start']);
				$end = $this->ci->date_time->gmt_to_local($event['end']);

				$parseArray[] = array(

				    'event:id' => $event['id'],
				    'event:name' => $event['name'],
				    'event:slug' => $event['slug'],
				    'event:venue' => $event['venue'],

				    'event:description' => $event['description'],
				    'event:excerpt' => word_limiter($event['description'], $this->wordCountLimit),

				    'event:start' => $this->ci->date_time->datetime( $start ),
				    'event:start:date' => $this->ci->date_time->format($start, 'j'),
				    'event:start:month' => $this->ci->date_time->format($start, 'M'),
				    'event:start:year' => $this->ci->date_time->format($start, 'Y'),
				    'event:start:time' => $this->ci->date_time->time($start),

				    'event:end' => $this->ci->date_time->datetime( $end ),
				    'event:end:date' => $this->ci->date_time->format($end, 'j'),
				    'event:end:month' => $this->ci->date_time->format($end, 'M'),
				    'event:end:year' => $this->ci->date_time->format($end, 'Y'),
				    'event:end:time' => $this->ci->date_time->time($end)
				);
			}

//                        $this->eventTags = array_merge( $this->eventTags, $parseArray );
		}

		return $parseArray;
	}

}

/* End of file events_render.php */
/* Location: ./application/.... events_render.php */
?>
