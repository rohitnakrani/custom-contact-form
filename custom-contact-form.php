<?php
/*
Plugin Name: Custom Contact Form
Description: AJAX-powered contact form with Figma-based design and CPT saving.
Version: 1.0
Author: Rohit Nakrani 
*/

defined('ABSPATH') or die('No script kiddies please!');

// Register Custom Post Type for contact entries
function ccf_register_contact_cpt() {
    register_post_type('contact_entries', array(
        'labels' => array(
            'name' => 'Contact Entries',
            'singular_name' => 'Contact Entry'
        ),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_icon' => 'dashicons-email'
    ));
}
add_action('init', 'ccf_register_contact_cpt');

// Enqueue frontend scripts and styles
function ccf_enqueue_scripts() {
    wp_enqueue_style('ccf-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('ccf-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), null, true);
    wp_localize_script('ccf-script', 'ccf_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ccf_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'ccf_enqueue_scripts');

// Shortcode to display form
function ccf_contact_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'form.php';
    return ob_get_clean();
}
add_shortcode('custom_contact_form', 'ccf_contact_form_shortcode');

// AJAX handler
add_action('wp_ajax_ccf_submit_form', 'ccf_handle_form');
add_action('wp_ajax_nopriv_ccf_submit_form', 'ccf_handle_form');

function ccf_handle_form() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ccf_nonce')) {
        wp_send_json_error('Security check failed');
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    $post_id = wp_insert_post(array(
        'post_type' => 'contact_entries',
        'post_title' => $name,
        'post_content' => $message,
        'meta_input' => array(
            'email' => $email,
            'submitted_on' => current_time('mysql')
        )
    ));

    if ($post_id) {
        wp_send_json_success('Form submitted successfully');
    } else {
        wp_send_json_error('Failed to save entry');
    }
}
