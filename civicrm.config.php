<?php

/**
 * Full path to civicrm.settings.php
 *
 * This extension tries to find it automatically, if that fails
 * set path here.
 */
//define( 'CIVICRM_SETTINGS_PATH', '/full/path/to/civicrm.settings.php');

/**
 * Locate civicrm.settings.php configuration file
 */
function find_civi_settings_file(): string
{
    // Candidates
    $candidates[] = '../../sites/default';
    $candidates[] = '../../../sites/default';
    $candidates[] = '../../../../sites/default';
    $candidates[] = '../../../../../sites/default';

    foreach ($candidates as $candidate) {
        $settings_file = "${candidate}/civicrm.settings.php";
        if (is_readable($settings_file)) {
            return $settings_file;
        }
    }

    // Couldn't find file --> error message & close
    http_response_code(500);
    echo 'CiviCRM settings file not found';
    exit;
}

// Settings file not defined --> search it
if (!defined('CIVICRM_SETTINGS_PATH')) {
    $settings_file = find_civi_settings_file();
    define('CIVICRM_SETTINGS_PATH', $settings_file);
}
//require_once $settings_file;
$success = include_once CIVICRM_SETTINGS_PATH;
if ($success == false) {
    echo 'Could not load the settings.';
    exit();
}
