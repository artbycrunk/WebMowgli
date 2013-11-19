<?php

/**
 * Description of test
 *
 * @author Lloyd
 */
class _Test extends MY_Controller {

	public function index() {

		echo "this is a test page, you can create any kind of controllers here to test out your code";
	}

	public function directory() {

		$keys = array(WM_MODULE_HAS_UNIQUE_URLS);

		$this->load->helper('config');

		$config = get_config_array($keys);

		var_dump($config);
	}

	public function regex() {

		$posts = array();
		$slugs = array('blah', 'hello', 'howdy');

		for ($count = 0; $count < 5; $count++) {

			$posts[$count]['name'] = "Lloyd Saldanha - $count";
			$posts[$count]['slug'] = ( isset($slugs[$count]) ) ? $slugs[$count] : null;
		}

		$data['posts'] = $posts;

		$string = $this->parser->parse('test_regex', $data, true);

		$pattern = "/{posts}(.*){\/posts}/";

		if (preg_match($pattern, $string, $matches)) {

			var_dump($matches);
		} else {
			echo "No Match";
		}
	}

	public function encode() {

		$view = "

<form action='encode_do' method='POST'>

    <input type='submit' name='submit' value='submit'>
    <br/>
    <textarea name='text' rows='30' cols='150'></textarea>

</form>


";
		echo $view;
	}

	public function encode_do() {

		$text = "(we ♥ ☺ ☻ ♥ hearts)";

//            $text = $this->input->post('text');


		$text = utf8_encode($text);

		echo $text;
	}

	public function parse() {

		$this->load->library('parser');

		$data['status'] = 'published';
		$data['count'] = 520;
		$data['success'] = false;

		$posts = array();
		$slugs = array('blah', 'hello', 'howdy');

		for ($count = 0; $count < 5; $count++) {

			$posts[$count]['name'] = "Lloyd Saldanha - $count";
			$posts[$count]['slug'] = ( isset($slugs[$count]) ) ? $slugs[$count] : null;
		}

		$data['posts'] = $posts;
//	$data['posts'] = null;

		$this->parser->parse('test_view.php', $data);
	}

	public function sql() {

		$start = '2013-10-1';
		$end = '2013-11-1';

		$this->load->model('test_model');
		$rows = $this->test_model->between($start, $end);

		if (!is_null($rows)) {

			foreach ($rows as $row) {

				echo $row['created'] . "<br/>";
			}
		} else {
			echo "no rows";
		}
	}

	public function bool($string = null) {

		var_dump(string_to_bool($string));
	}

	public function comments($dummy = null) {

		// load comments
//				$comments = new Comments();
		$comments = Comments::get_instance();
		echo $comments->get_comment_script();
		echo $comments->get_count_script();
	}

	public function comments_count($dummy = null) {
		// load comments
//		$comments = new Comments();
		$comments = Comments::get_instance();

		$url = site_url("_test/comments/$dummy");
		$url = $comments->get_count_url($url);

		$comments->load_count();

		echo "<a href='$url'>test</a>";
		echo $comments->get_count_script();
	}

	public function email() {

		$this->load->library('email');

		$emailTo = "encubetech@gmail.com";
		$fromEmail = "test123@webmowgli.com";
		$fromName = "Admin @ WM";
		$bcc = null;

		$this->email->to($emailTo);
		$this->email->from($fromEmail, $fromName);
		$this->email->reply_to($fromEmail, $fromName);
		$this->email->bcc($bcc);
		$this->email->subject("Email from " . $fromName);
		$this->email->message("This is a test mail");

		if ($this->email->send()) {

			echo "email sent";
		} else {
			echo "Fail !!!<br/><br/>";
			echo $this->email->print_debugger();
		}
	}

	public function port_test($port, $host = '127.0.0.1', $secure = null ) {

		$host = !is_null($secure) ? "$secure://$host" : $host;

		echo "Host : $host<br/>";
		echo "Port : $port<br/>";
		echo "Secure : $secure<br/><br/>";

		$fp = @fsockopen($host, $port, $errno, $errstr, 5);

		if (!$fp) {
			// port is closed or blocked
			echo "port Closed <br/><br/> Error : $errstr";
		} else {
			// port is open and available
			fclose($fp);
			echo "port OPEN";
		}
	}

	public function js(){

		$this->parser->parse('test_js', $this->parseData);

	}

	public function url_title(){


		$strings = array(
		    'abc xyz',
		    '_abc-xyz',
		    'blah%blah'
		);

		foreach ($strings as $string) {

			echo "before : $string" . " | After : " . url_title($string, '-') . "<br/>";

		}

	}

	public function increment_string(){

		$this->load->helper('string');
		$strings = array(
		    'index',
		    'index-1',
		    'index_1'
		);

		foreach ($strings as $string) {

			echo "before : $string" . " | After : " . increment_string($string, '-') . "<br/>";

		}
	}
}

?>
