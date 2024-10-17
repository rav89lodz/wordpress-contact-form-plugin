<?php

/**
 * Plugin Name:       Contact Plugin
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Contact Plugin for modern contact form creating
 * Version:           1.0.0
 * Requires at least: 6.6.2
 * Requires PHP:      8.1
 * Author:            Rafał C
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       contact-plugin
 * Domain Path:       /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('ContactPlugin')) {
    class ContactPlugin
    {
        public function __construct() {
            define('CONTACT_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
            define('CONTACT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

            require_once(CONTACT_PLUGIN_PATH . '/vendor/autoload.php');
        }

        public function initialize() {
            include_once CONTACT_PLUGIN_PATH . '/src/includes/utilities.php';
            include_once CONTACT_PLUGIN_PATH . '/src/includes/options-page.php';
            include_once CONTACT_PLUGIN_PATH . '/src/includes/contact-form.php';
        }
    }

    $contactPlugin = new ContactPlugin;
    $contactPlugin->initialize();
}