<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| EMAIL CONFING
| -------------------------------------------------------------------
| Configuration of outgoing mail server.
| This config file will override user and programming settings for email.
| To activate this file copy paste this file in the '/encube/application/config' folder and rename it to 'email.php'
| */

/* Using mail() */
$config['useragent']    = 'WebMowgli';
$config['protocol']     = 'mail';
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['mailtype']     = "html";
$config['validate']     = TRUE;

/* Using gmail */
//$config['useragent']    = 'WebMowgli';
//$config['protocol']     = 'smtp';
//$config['smtp_host']    = 'ssl://smtp.gmail.com';
//$config['smtp_port']    = '465';
//$config['smtp_timeout'] = '30';
//$config['smtp_user']    = '';		// enter your gmail username, Eg. username@gmail.com
//$config['smtp_pass']    = '';		// enter your gmail password
//$config['charset']      = 'utf-8';
//$config['newline']      = "\r\n";
//$config['mailtype']     = "html";
//$config['validate']     = TRUE;

/* To use Bluehost as your SMTP */
//$config['useragent']     = 'WebMowgli';
//$config['protocol']     = 'smtp';
//$config['smtp_host']    = 'ssl://box567.bluehost.com';
//$config['smtp_port']    = '465';
//$config['smtp_timeout'] = '30';
//$config['smtp_user']    = '';		// enter your bluehost email id Eg. yourname@yourdomain.com
//$config['smtp_pass']    = '';		// enter your password
//$config['charset']      = 'utf-8';
//$config['newline']      = "\r\n";
//$config['mailtype']     = "html";
//$config['validate']     = TRUE;

/* End of file email.php */
/* Location: ./system/application/config/email.php */
?>