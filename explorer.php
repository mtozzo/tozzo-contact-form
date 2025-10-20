<?php

// Define mock WordPress functions BEFORE including the file
function add_action() {}
function add_shortcode() {}
function plugin_dir_path($file) {
    return __DIR__ . '/';
}
function sanitize_textarea_field($str) {
    return strip_tags($str);
}
function wp_mail($to, $subject, $message, $headers = '') {
    echo "Email would be sent to: $to\n";
    echo "Subject: $subject\n";
    return true;
}
function wp_redirect($location) {
    echo "Would redirect to: $location\n";
}

// Set up required $_SERVER variables
$_SERVER['HTTP_HOST'] = 'www.allen-law.ca';
$_SERVER['REMOTE_ADDR'] = '94.23.183.172';
$_SERVER['HTTP_REFERER'] = 'http://www.allen-law.ca/contact/';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

// Now include the contact form
include 'tozzo-contact-form.php';

$email_str = <<<EMAIL
Site: 	www.allen-law.ca
Name: 	Tania Castaneda
Email address: 	tania.castaneda@outlook.com
Phone number: 	676887150
Best time to reach you: 	Tania Castaneda
Subject: 	Want Targeted Visitors? See How in 60 Seconds
Message: 	Need more clicks and conversions for Allen Law Ca? Watch this short video about our AI-powered traffic service: https://www.youtube.com/watch?v=VOdZEKK52Rw
Sent from IP Address (domain): 	142.91.118.80 (ip80.ip-142-91-118.mpp.wa.com)
Sent at: 	Fri, 10 Oct 2025 12:05:02 +0000
From page: 	http://www.allen-law.ca/contact/
Using: 	Mozilla/5.0 (Windows NT 10.0; WOW64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/89.0.4447.51
EMAIL;

$email_str_parts = explode("\n", $email_str);

// Initialize POST array
$form_field_data = tozzo_contact_form_field_data();
foreach($form_field_data as $form_field_datum) {
    $_POST['tozzo_contact_' . $form_field_datum['id']] = '';
}

// Parse email string and populate POST data
foreach($email_str_parts as $email_str_part) {
    if (strpos($email_str_part, ':') === false) {
        continue;
    }
    
    $parts = explode(':', $email_str_part, 2);
    $label = trim($parts[0]);
    $rest = trim($parts[1]);
    
    foreach($form_field_data as $form_field_datum) {
        if ($label == $form_field_datum['name']) {
            $_POST['tozzo_contact_' . $form_field_datum['id']] = $rest;
            break;
        }
    }
    
    if ($label == 'Using') {
        $_SERVER['HTTP_USER_AGENT'] = $rest;
    }
}

echo "POST data:\n";
print_r($_POST);
echo "\n";

// Test spam detection
$reason = null;
$is_spam = tozzo_determine_if_probably_spam($reason);
print_r(['is_spam' => $is_spam, 'reason' => $reason]);
echo "\n";