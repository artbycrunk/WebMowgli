<?php

// Simple Form Script
// Copyright (C) 2005  Eric Zhang
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Please send bugs/questions to erkzh@yahoo.com.

//--------------------------Set these paramaters--------------------------


$subject = 'Form Submission';                // Subject of email sent to you.
$emailadd = 'info@empowermission.org';        // Your email address. This is where the form information will be sent.
$url = 'http://www.empowermission.org';               // Where to redirect after form is processed.
$req = '1';                                  // Makes all fields required. If set to '1' no field can not be empty. If set to '0' any or all fields can be empty.

// --------------------------Do not edit below this line--------------------------
$text = "Results from form:\n\n";       
$space = '  ';
$line = '
';
foreach ($_POST as $key => $value)
{
	if ($req == '1')
	{
		if ($value == '')
		{echo "$key is empty";die;}
	}
	$j = strlen($key);
		if ($j >= 20)
		{echo "Name of form element $key cannot be longer than 20 characters";die;}
	$j = 20 - $j;
		for ($i = 1; $i <= $j; $i++)
		{$space .= ' ';}
	$value = str_replace('\n', "$line", $value);
	$conc = "{$key}:$space{$value}$line";
	$text .= $conc;
	$space = '  ';
}
mail($emailadd, $subject, $text, 'From: '.$emailadd.'');
echo '<META HTTP-EQUIV=Refresh CONTENT="0; URL='.$url.'">';
?>