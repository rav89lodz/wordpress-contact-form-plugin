<?php

require_once(CONTACT_PLUGIN_PATH . '/src/classes/FormValidator.php');
require_once(CONTACT_PLUGIN_PATH . '/src/classes/Utils.php');

use ContactPlugin\src\classes\FormValidator;
use ContactPlugin\src\classes\Utils;

if (! defined('ABSPATH')) {
    exit;
}

// Hooks

add_shortcode('contact-07265', 'show_contact_form');

add_action('rest_api_init', function(){
    register_rest_route('v1/contact-form', 'submit', [
        'methods' => 'POST',
        'callback' => 'handle_enquiry'
    ]);
});

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_submission_posts_columns', 'custom_submission_columns'); // manage_{post-type}_posts_columns

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2); // manage_{post-type}_posts_custom_column

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function show_contact_form() {
    include CONTACT_PLUGIN_PATH . '/src/templates/contact-form.php';
}

function handle_enquiry($data) {
    $utils = new Utils;
    $validator = new FormValidator;

    $params = $validator->sanitize_data_array(json_decode($data->get_params()['data']));

    // if (! wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
    //     return new WP_Rest_Response('There was an error submitting your form', 422);
    // }

    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    $utils->send_email_with_store_data($params);

    return new WP_Rest_Response($utils->set_success_message($params), 200);
}

function create_submissions_page() {
    $args = [
        'public' => true,
        'has_archive' => true,
        'menu_position' => 80,
        'publicly_queryable' => false,
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => 'Submission',
            'edit_item' => 'View Submission',
        ],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => false, //'do_not_allow',
        ],
        'supports' => false, // ['custom_fields'],
        'map_meta_cap' => true,
    ];

    register_post_type('submission', $args); // submission - post type
}

function create_meta_box() {
    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission() {
    $data = get_post_meta(get_the_ID());
    unset($data['_edit_lock']);
    unset($data['_edit_last']);

    echo "<ul>";

    foreach ($data as $key => $value) {
        echo "<li><strong>" . ucfirst($key) . "</strong>:<br>" . $value[0] . "</li>";
    }

    echo "</ul>";
}

function custom_submission_columns($columns) {
    return [
        'cb' => $columns['cb'],
        'name' => __('Name', 'contact-plugin'), // contact-plugin = Text Domain from contact-plugin.php
        'email' => __('Email', 'contact-plugin'),
        'phone' => __('Phone', 'contact-plugin'),
        'message' => __('Message', 'contact-plugin'),
    ];
}

function fill_submission_columns($column, $postId) {
    switch ($column) {
        case 'name':
            echo get_post_meta($postId, 'name', true);
            break;
        case 'email':
            echo get_post_meta($postId, 'email', true);
            break;
        case 'phone':
            echo get_post_meta($postId, 'phone', true);
            break;
        case 'message':
            echo get_post_meta($postId, 'message', true);
            break;
    }
}

function setup_search() {
    global $typenow;

    if ($typenow == 'submission') {
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query) {
    global $wpdb;

    if ($query->is_main_query() && ! empty($query->query['s'])) {
        $sql = "or exists (
            select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
            and meta_key in ('name', 'email', 'phone')
            and meta_value like %s
        )";
        $like = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $wpdb->prepare($sql, $like), $search);
    }

    return $search;
}

function enqueue_custom_scripts() {
    wp_enqueue_style('contact-form-plugin', CONTACT_PLUGIN_URL . 'src/assets/css/contact-plugin.css');
    // wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
}