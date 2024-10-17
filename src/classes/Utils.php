<?php

namespace ContactPlugin\src\classes;

class Utils
{
    private $adminEmail;
    private $adminName;

    public function __construct() {
        $this->adminEmail = strtolower(trim(get_plugin_options('contact_plugin_recipients') ?? get_bloginfo('admin_email')));
        $this->adminName = get_bloginfo('name');
    }

    public function set_success_message($params) {
        $message = get_plugin_options('contact_plugin_message') ?? 'The message was sent successfully';
        // $message = mb_convert_encoding($message, 'UTF-8', 'ISO-8859-2');
        $message = $this->set_up_polish_letters($message);
        return str_replace('{name}', $params['name'], $message);
    }

    public function send_email_with_store_data($params) {
        $message = $this->store_data_and_create_message($params);
        $headers = $this->set_custom_headers($params);
        $subject = "New enquiry from {$params['name']}";
        wp_mail(
            $this->adminEmail,
            $subject,
            $message,
            $headers
        );
    }

    private function set_custom_headers($params) {
        return [
            "From: {$this->adminName} <{$this->adminEmail}>",
            "Replay-to: {$params['name']} <{$params['email']}>",
            "Content-Type: text/html",
        ];
    }

    private function store_data_and_create_message($params) {
        $message = "<h2>Message has been sent from {$params['name']}</h2>";

        $postId = wp_insert_post([
            'post_title' => $params['name'],
            'post_type' => 'submission',
            'post_status' => 'publish',
        ]);
    
        foreach ($params as $label => $value) {    
            add_post_meta($postId, $label, $value);

            $message .= "<div><strong>" . ucfirst($label) . "</strong>: " . $value . "</div>";
        }

        return $message;
    }

    private function set_up_polish_letters($string) {
        $specialChars = [
            '\u0105', # ą
            '\u0107', # ć
            '\u0119', # ę
            '\u0142', # ł
            '\u0144', # ń
            '\u00f3', # ó
            '\u015b', # ś
            '\u017a', # ź
            '\u017c', # ż
            '\u0104', # Ą
            '\u0106', # Ć
            '\u0118', # Ę
            '\u0141', # Ł
            '\u0143', # Ń
            '\u00d3', # Ó
            '\u015a', # Ś
            '\u0179', # Ż
            '\u017b', # Ż
        ];
    
        $polishHtmlCodes = [
            '&#261;', # ą
            '&#263;', # ć
            '&#281;', # ę
            '&#322;', # ł
            '&#324;', # ń
            '&#243;', # ó
            '&#347;', # ś
            '&#378;', # ź
            '&#380;', # ż
            '&#260;', # Ą
            '&#262;', # Ć
            '&#280;', # Ę
            '&#321;', # Ł
            '&#323;', # Ń
            '&#211;', # Ó
            '&#346;', # Ś
            '&#377;', # Ż
            '&#379;', # Ż
        ];

        $result = str_replace($specialChars, $polishHtmlCodes, json_encode($string));
        return json_decode($result);
    }
}