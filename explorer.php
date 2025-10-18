<?php

include 'tozzo-contact-form.php';

$email_str = <<<EMAIL

Site: 	www.allen-law.ca
Name: 	Guest Posting Service for Premium Results
Email address: 	van.savage@msn.com
Phone number: 	02742 69 80 66
Best time to reach you: 	Van Savage
Subject: 	
Message: 	
Sent from IP Address (domain): 	94.23.183.172 (94.23.183.172)
Sent at: 	Sun, 19 Nov 2023 20:37:43 +0000
From page: 	http://www.allen-law.ca/contact/
Using: 	Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/89.0.4447.51
Spam Reason: 	full banned word found (click here)

EMAIL;

$email_str_parts = explode("\n", $email_str);

// print_r($email_str_parts);

$form_field_data = tozzo_contact_form_field_data();

foreach($form_field_data as $form_field_datum)
{
	$_POST['tozzo_contact_' . $form_field_datum['id']] = '';
}

foreach($email_str_parts as $email_str_part) 
{
	[$label, $rest] = explode(':', $email_str_part, 2);
	$label = trim($label); 
	$rest = trim($rest);

	// print_r([$label, $rest]);

	foreach($form_field_data as $form_field_datum)
	{
		if ($label == $form_field_datum['name']) {
			$_POST['tozzo_contact_' . $form_field_datum['id']] = $rest;
			continue;
		}

		if ($label == 'Using') {
			$_SERVER['HTTP_USER_AGENT'] = $rest;
			continue;
		}
	}
}

// tozzo_contact

$is_spam = tozzo_determine_if_probably_spam($reason);

print_r(['is_spam' => $is_spam, 'reason' => $reason]);

// fake functions so the include doesn't crash
function add_action() {}
function add_shortcode() {};
function plugin_dir_path() {};