<?php
/*
 * Tozzo Contact Form - configuration (SAMPLE)
 *
 * This is a template you can commit to git. It contains placeholder values so
 * that real email addresses never end up in version control.
 *
 * To set up your local copy:
 *     cp config.sample.php config.php
 * Then edit config.php with your real values. config.php is git-ignored, so
 * only this sample stays tracked in the repository.
 */

if (!defined('TOZZO_CONTACT_FORM_EMAIL')) {
    define('TOZZO_CONTACT_FORM_EMAIL', 'recipient@example.com');
}

if (!defined('TOZZO_CONTACT_FORM_SPAM_EMAIL')) {
    define('TOZZO_CONTACT_FORM_SPAM_EMAIL', 'spam-recipient@example.com');
}
