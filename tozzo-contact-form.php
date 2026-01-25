<?php
/*
Plugin Name: Tozzo Contact Form
Plugin URI:  https://developer.wordpress.org/plugins/tozzo-contact-form/
Description: A simple contact form that doesn't rely on external styles or CSS.
Version:     0.03
Author:      Michael Tozzo
Author URI:  https://michaeltozzo.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg

*/

function tozzo_contact_form_handler($atts, $content = null) {
    global $errors;
    /*if (!in_array($_SERVER['REMOTE_ADDR'], ['192.0.188.119', '192.168.33.1'])) {
        return '';
    }*/

    extract(shortcode_atts(['class_prefix' => ''], $atts));
    $fields = tozzo_contact_form_field_data();

    $the_form = '
    <style>
    --wp--preset--color--background: #F3EFEC;
    --wp--preset--color--foreground: #171511;
    --wp--preset--color--foreground-alt: #090703;
    --wp--preset--font-family--inter: "Inter", sans-serif;
    --wp--custom--typography--line-height--normal: 1.6;

    --wp--style--root--padding-top: 0;
    --wp--style--root--padding-right: var(--wp--preset--spacing--30);
    --wp--style--root--padding-bottom: 0;
    --wp--style--root--padding-left: var(--wp--preset--spacing--30);    

    --wp--preset--font-size--normal: 16px;
    --wp--preset--font-size--huge: 42px;    

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
    input[type="text"], input[type="email"], textarea {
        border: 1px solid #D63637;
        display: block;
        width: 100%;
        box-sizing: border-box;
        font-family: inherit;
        font-style: normal;
        font-weight: 400;
        margin: 0;

        padding: calc(0.667em + 1px);
        width: 100%;
        background-color: var(--wp--preset--color--background);
        color: var(--wp--preset--color--foreground);
        border-color: inherit;        
    }
    .tozzo_contact_form_submit {
        background-color: var(--wp--custom--elements--button--color--background);
        color: var(--wp--custom--elements--button--color--text);
        border-radius: var(--wp--custom--elements--button--border--radius);
        border-width: 0;
        font-family: inherit;
        font-size: inherit;
        font-weight: var(--wp--custom--typography--font-weight--medium);
        line-height: inherit;
        padding-top: calc(0.667em + 2px);
        padding-right: calc(1.333em + 2px);
        padding-bottom: calc(0.667em + 2px);
        padding-left: calc(1.333em + 2px);
        text-decoration: none;
        cursor: pointer;
        margin-top: 1rem;
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
                    $the_form .= '<div><input type="radio" name="tozzo_contact_' . $field['id'] . '" id="' . str_replace(' ', '_', strtolower($option)) . '" value="' . $option . '"' . $required_attr . ' />
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
        $probably_spam = false;
        $errors = [];
		
		$subject = 'New Contact Form Submission';
		$email = 'xxxxxx@xxxx.com';

        $reason = null;
		if (tozzo_determine_if_probably_spam($reason)) {
			$subject = '⛔ New Contact';
			$email = 'yyyyyy@yyyy.com';
            $probably_spam = true;
		}
		
        $mail_message = "Contact Form Submission Details: <br />\n<br />\n<table>\n";
        $mail_message .= '<tr><td nowrap="nowrap">Site: </td><td>' . tozzo_sanitize_string($_SERVER['HTTP_HOST']) . "</td></tr>\n";

        foreach($fields as $field) {
            $field_name = tozzo_construct_field_name($field);

            if (true === $field['required'] && empty($_POST[$field_name])) {
                $valid_submission = false;
                $errors[] = $field['id'];
            } elseif (!empty($_POST[$field_name])) { 
                $mail_message .= '<tr><td nowrap="nowrap">' . $field['name'] . ': </td><td>' . tozzo_sanitize_string($_POST[$field_name]) . "</td></tr>\n";
                if ('tozzo_contact_subject' == $field_name) {
                    $subject .= ' - ' . tozzo_sanitize_string(preg_replace('/\s+/i', ' ', $_POST[$field_name]));
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

        if ($reason !== null) {
            $mail_message .= '<tr><td nowrap="nowrap">Spam Reason: </td><td>' . $reason . "</td></tr>\n";
        }

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

        $ret = wp_mail($email, $subject, $mail_message, 'Content-type: text/html; charset=utf-8' . "\n");

        if (!$probably_spam) {
            $ret = wp_mail('mtozzo@gmail.com', '[*] ' . $subject, $mail_message, 'Content-type: text/html; charset=utf-8' . "\n");
        }

        wp_redirect('/contact-form-thankyou?mt=1');
        exit;
    }
}

function tozzo_construct_field_name($field) {
    return 'tozzo_contact_' . $field['id'];
}

function tozzo_sanitize_string($str) {
    return str_replace("\'", "'", sanitize_textarea_field($str));
}

function tozzo_determine_if_probably_spam(&$reason = null) {
	$fields = tozzo_contact_form_field_data();
	
    $link_count = $banned_word_count = $input_count = $word_count = 0;

    /****************************
    * remember strtolower()!!!  *
    ****************************/

	$banned_words = [
        'adult', 
        'bad',
        'beautiful',
        'best',
        'dating', 
        'easy',
        'financial',
        'free',
        'free', 
        'future',
        'girl', 
        'girls', 
        'google',
        'guarantees',
        'independence',
        'instrument',
        'investment',
        'making', 
        'massager',
        'money',
        'neck',
        'remove',
        'reviews',
        'rich',
        'robot', 
        'sex', 
        'shipping',
        'sites', 
        'women', 
        'world\'s',
        'yelp',
        'free shipping',
        'healthy', 
        'lifestyle',
        'bot',
        'internet',
        'earn',
        'click',
        'dollar',
        'capital',
        'posture',
        'ad',
        'usd',
        'followers',
        'marketing',
    ];

    $full_ban_words = [
        'yabrowser',
        'click here',
        'bit.ly',
        'cutt.ly',
        'contact form blasts',
        'gsa',
        'adcreative.ai',
        'high quality traffic',
        'explainer video',
        'henryfus',
        'eric jones',
        'mail-online.dk',
        '#file_links',
        'dandydemo.com',
        'crypto',
        'anonrerve',
        'thawking.store',
        'expresscapitalcorp.com',
        'express capital',
        'epap', 
        'seo',
        'elevating',
        'bespoke',
        'sleepl.ink',
        'rokl.ink',
        ' ai ',
        ' ai?',
        'leads',
        'free traffic',
        'noticed something',
        'down range',
        'virtual assistance',
        'growth service',
        'a few problems affecting',
        'nextdayworkingcapital.com',
        'working capital',
        'influencers',
        'business reviews',
        'if you are interested',
        'conventional advertising',
        'time and money they can save',
        'performance problems',
        'marketing video',
        'small business loan',
        'unsubscribe',
        'dating service',
        'foolproof money making system',
        'figure business',
        'reading this message',
        'phil stewart',
        'contact form',
        'capitalfundingstore.com',
        'are you looking for',
        'website designing',
        'business loan',
        'fast cash',
        'social media marketing manager',
        'current website',
        'backlinks',
        'video producer',
        'need any videos',
        'online business',
        'sales funnel',
        'just had to drop a message',
        'holloman',
        'pva account',
        'data entry services',
        'online courses',
        'webdesignservices111@outlook.com',
        '.sale',
        'webflow',
        'venture capital',
        'bange',
        'dont-reply.me',
        'exclusive opportunity',
        'dedicated video player',
        'without any questions',
        'gaza',
        'zeep.ly',
        'formblastmarketing.top',
        '.shop',
        'ppv',
        'vps',
        'profitparadigm',
        'raining videos',
        'speed issue',
        'idle connection',
        'surplus network',
        't.ly/',
        'viagra',
        'funding options',
        'youtube',
        'allen-law.ca',
        'bestaitools',
    ];

    $dict_path = plugin_dir_path( __FILE__ ) . 'aspell.dat';
    $dict_words = file($dict_path);
    $dict_words = array_map('trim', $dict_words);

    $last_field_data = null;
    $duplicate_data_count = 0;
	
	foreach($fields as $field) {
		$field_name = tozzo_construct_field_name($field);
		$field_data = strtolower($_POST[$field_name]);

        if ($last_field_data !== null) {
            if ($field_data === $last_field_data) {
                $duplicate_data_count++;
            }
        }

        $last_field_data = $field_data;

		if (strpos($field_data, 'http://') !== false) {
			$link_count++;
		}
			
		if (strpos($field_data, 'https://') !== false) {
			$link_count++;
		}

        if ($field['wordscan']) {
            $input_words = preg_split('/\s+/i', trim(preg_replace('/\W/', ' ', strtolower($field_data))));

            foreach($input_words as $input_word) {
                $input_count++;

                if (in_array($input_word, $dict_words)) {
                    $word_count++;
                }
            }
        }

        foreach($banned_words as $banned_word) {
            if (strpos($field_data, $banned_word) !== false) {
                $banned_word_count++;
            }
        }
        
        foreach($full_ban_words as $banned_word) {
            // echo "checking {$banned_word} in:\n{$field_data}\n";
            if (strpos($field_data, strtolower($banned_word)) !== false) {
                $reason = "full banned word found ({$banned_word}) in {$field_name}";
                return true;
            }
        }
	}

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'yabrowser') !== false) {
        $reason = "yabrowser found";
        return true;
    }

    $percentage = 100;
    if ($input_count >= 0) {
        $percentage = (($word_count / $input_count) * 100);    
    }
    

    if ($link_count > 2) {
        $reason = 'too many links';
    } elseif ($banned_word_count > 7) {
        $reason = 'too many banned words';
    } elseif ($percentage < 50) {
        $reason = "under 50% of content is words ( {$word_count} / {$input_count} ) dict_words: " . count($dict_words);
    } elseif ($duplicate_data_count >= 3) {
        $reason = 'too many duplicate data';
    }
	
	return ($link_count > 2) || ($banned_word_count > 7) || ($percentage < 50) || ($duplicate_data_count >= 3);
}
 
function tozzo_contact_form_field_data() {
    return [
        [
            'id' => 'name',
            'name' => 'Name',
            'type' => 'text',
            'required' => true,
            'wordscan' => false,
        ],
        [
            'id' => 'email',
            'name' => 'Email address',
            'type' => 'email',
            'required' => true,
            'wordscan' => false,
        ],
        [
            'id' => 'phone',
            'name' => 'Phone number',
            'type' => 'text',
            'required' => true,
            'wordscan' => false,
        ],
        [
            'id' => 'best_time',
            'name' => 'Best time to reach you',
            'type' => 'text',
            'required' => true,
            'wordscan' => false,
        ],
        [
            'id' => 'how_did',
            'name' => 'How did you hear about us',
            'type' => 'text',
            'required' => false,
            'wordscan' => false,
        ],
        [
            'id' => 'subject',
            'name' => 'Subject',
            'type' => 'text',
            'required' => true,
            'wordscan' => true,
        ],
        [
            'id' => 'message',
            'name' => 'Message',
            'type' => 'textarea',
            'required' => true,
            'wordscan' => true,
        ],
    ];
}

date_default_timezone_set('America/Toronto');
add_action( 'init', 'tozzo_contact_init', 55);
add_shortcode('tozzo_contact_form', 'tozzo_contact_form_handler');

// find | xargs -I {} pdfimages -j {} {}-extracted-images

// add_action('init', function () {
//     register_block_type('tozzo_contact/form', [
//         // 'attributes' => [
//         //     'formId' => [
//         //         'type' => 'number',
//         //     ],
//         // ],
//         // 'render_callback' => function ($attrs) {
//         //     return myplugin_render_form($attrs['formId'] ?? null);
//         // },
//         'render_callack' => 'tozzo_contact_form_handler',
//     ]);
// });