<?php
/*
Plugin Name: Tozzo Contact Form
Plugin URI:  https://developer.wordpress.org/plugins/tozzo-contact-form/
Description: A simple contact form that doesn't rely on external styles or CSS.
Version:     0.01
Author:      Michael Tozzo
Author URI:  https://michaeltozzo.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages

*/

function tozzo_contact_form_handler($atts, $content = null) {
    global $errors;
    extract(shortcode_atts(['class_prefix' => ''], $atts));
    $fields = tozzo_contact_form_field_data();

    $the_form = '
    <style>
    .tozzo_contact_form {
        width: 340px;
    }
    .tozzo_contact_form_errors {
        color: red; display: block;
    }
    .tozzo_contact_form_field_wrapper {
        width: 100%;
        padding: 5px 0px;
    }
    .tozzo_contact_form_field_wrapper label {
        display: block;
    }
    .tozzo_contact_form_field_wrapper textarea {
        width: 100%;
        height: 150px;
    }
    input[type="text"] {
        width: 100%;
    }
    </style>    
    <form method="post" id="tozzo_contact_form" class="tozzo_contact_form" action="" validate="validate">
<div class="tozzo_contact_form_wrapper">' . "\n";

    if (!empty($errors)) {
        $the_form .= '<div><span class="tozzo_contact_form_errors">Please correct the following errors and try again.</span></div>' . "\n";
    }

    foreach($fields as $field) {
        $required_attr = '';

        $the_form .= '<div class="tozzo_contact_form_field_wrapper"><label for="tozzo_contact_' . $field['id'] . '">' . $field['name'] . ': ';
        if (!empty($field['required'])) {
            $the_form .= '<span class="required">*</span>';
            $required_attr = ' required="required"';
        }

        if (in_array($field['id'], $errors)) {
            $the_form .= '<span class="tozzo_contact_form_errors">&quot;' . $field['name'] . '&quot; is required</span>';
        }
        $the_form .= '</label>';

        switch ($field['type']) { 
            case 'text': 
                $the_form .= '<input type="text" value="" name="tozzo_contact_' . $field['id'] . '" id="tozzo_contact_' . $field['id'] . '"' . $required_attr . ' />';
                break;
            case 'email': 
                $the_form .= '<input type="email" value="" name="tozzo_contact_' . $field['id'] . '" id="tozzo_contact_' . $field['id'] . '"' . $required_attr . ' />';
                break;
            case 'textarea': 
                $the_form .= '<textarea name="tozzo_contact_' . $field['id'] . '" id="tozzo_contact_' . $field['id'] . '"' . $required_attr . '></textarea>';
                break;
            case 'radio': 
                $the_form .= '<div id="tozzo_contact_' . $field['id'] . '">';
                foreach($field['options'] as $option) {
                    $the_form .= '<div nowrap="nowrap"><input type="radio" name="tozzo_contact_' . $field['id'] . '" id="' . str_replace(' ', '_', strtolower($option)) . '" value="' . $option . '"' . $required_attr . ' />
                    <label for="' .str_replace(' ', '_', strtolower($option)) . '">' . $option . '</label></div>';
                }
                $the_form .= "</div>\n";
                break;
        }
        $the_form .= "</div>\n";
    }
$the_form .= '
<div>
<input type="hidden" name="tozzo_contact_form_action" value="send">
<input type="hidden" name="version" value="1.0">
<button type="submit" class="tozzo_contact_form_submit">Submit</button>
</div>
</div>
</form>';
    return $the_form;
}

function tozzo_contact_init() {
    global $errors;
    if (empty($errors)) {
        $errors = [];
    }

    $fields = tozzo_contact_form_field_data();
    $valid_submission = false;

    if (isset($_POST['tozzo_contact_form_action'])) {
        $valid_submission = true;
        $errors = [];
        $mail_message = "Contact Form Submission Details: <br />\n<br />\n<table>\n";

        $subject = 'New Contact Form Submission';

        $mail_message .= '<tr><td nowrap="nowrap">Site: </td><td>' . tozzo_sanitize_string($_SERVER['HTTP_HOST']) . "</td></tr>\n";

        foreach($fields as $field) {
            $field_name = tozzo_construct_field_name($field);

            if (true === $field['required'] && empty($_POST[$field_name])) {
                $valid_submission = false;
                $errors[] = $field['id'];
            } elseif (!empty($_POST[$field_name])) { 
                $mail_message .= '<tr><td nowrap="nowrap">' . $field['name'] . ': </td><td>' . tozzo_sanitize_string($_POST[$field_name]) . "</td></tr>\n";
                if ('tozzo_contact_subject' == $field_name) {
                    $subject .= ' - ' . tozzo_sanitize_string($_POST[$field_name]);
                }
            }
        }

        if ($remote_addr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP )) {
            $userdomain = @gethostbyaddr($remote_addr);
            $mail_message .= '<tr><td nowrap="nowrap" valign="top">Sent from IP Address (domain): </td><td>' . $remote_addr;
            if ($userdomain) {
                $mail_message .= ' (' . $userdomain . ')';
            }
            $mail_message .= "</td></tr>\n";
        }
        $mail_message .= '<tr><td nowrap="nowrap">Sent at: </td><td>' . date('r') . "</td></tr>\n";
        $mail_message .= '<tr><td nowrap="nowrap">From page: </td><td>' . tozzo_sanitize_string($_SERVER['HTTP_REFERER']) . "</td></tr>\n";
        $mail_message .= '<tr><td nowrap="nowrap">Using: </td><td>' . tozzo_sanitize_string($_SERVER['HTTP_USER_AGENT']) . "</td></tr>\n";
        $mail_message .= "</table>\n";
    }

    if ($valid_submission) {
        /*echo "mail_message is: $mail_message";

        add_action( 'wp_mail_failed', 'onMailError', 10, 1 );
        function onMailError( $wp_error ) {
            echo "<pre>";
            print_r($wp_error);
            echo "</pre>";
        }*/           

        $ret = wp_mail( $email = 'xxxxxx@xxxx.com', $subject, $message_text_for_user = $mail_message, 
            $headers = 'Content-type: text/html; charset=utf-8' . "\n");

        wp_redirect('/contact-form-thankyou?mt=1');
        exit;
    }
}

function tozzo_construct_field_name($field) {
    return 'tozzo_contact_' . $field['id'];
}

function tozzo_sanitize_string($str) {
    // return sanitize_text_field($str);
    return str_replace("\'", "'", sanitize_textarea_field($str));
}
 
function tozzo_contact_form_field_data() {
    return [
        [
            'id' => 'name',
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
        ],
        [
            'id' => 'email',
            'name' => 'Email address',
            'type' => 'email',
            'required' => true,
        ],
        [
            'id' => 'phone',
            'name' => 'Phone number',
            'type' => 'text',
            'required' => true,
        ],
        [
            'id' => 'best_time',
            'name' => 'Best time to reach you',
            'type' => 'text',
            'required' => true,
        ],
        [
            'id' => 'how_did',
            'name' => 'How did you hear about us',
            'type' => 'text',
            'required' => false,
        ],
        [
            'id' => 'subject',
            'name' => 'Subject',
            'type' => 'text',
            'required' => true,
        ],
        [
            'id' => 'message',
            'name' => 'Message',
            'type' => 'textarea',
            'required' => true,
        ],
    ];
}

add_action( 'init', 'tozzo_contact_init', 55);
add_shortcode('tozzo_contact_form', 'tozzo_contact_form_handler');

