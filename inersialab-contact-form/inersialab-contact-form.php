<?php
/**
 * Plugin Name: InersiaLab Contact Form
 * Description: Premium two-column contact form shortcode [inersialab_contact] with AJAX email submission.
 * Version: 1.0.0
 * Author: InersiaLab
 * Author URI: https://inersialab.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Register assets on scripts enqueue hook
add_action( 'wp_enqueue_scripts', 'inersialab_contact_enqueue_assets' );
function inersialab_contact_enqueue_assets() {
    wp_register_style( 
        'inersialab-contact-style', 
        plugins_url( 'css/style.css', __FILE__ ), 
        array(), 
        '1.0.0' 
    );
    wp_register_script( 
        'inersialab-contact-script', 
        plugins_url( 'js/script.js', __FILE__ ), 
        array(), 
        '1.0.0', 
        true 
    );
}

// Shortcode implementation
add_shortcode( 'inersialab_contact', 'inersialab_contact_shortcode_handler' );
function inersialab_contact_shortcode_handler() {
    // Enqueue registered styles and scripts
    wp_enqueue_style( 'inersialab-contact-style' );
    wp_enqueue_script( 'inersialab-contact-script' );

    // Enqueue Montserrat font if it hasn't been enqueued yet
    if ( ! wp_style_is( 'google-font-montserrat', 'enqueued' ) ) {
        wp_enqueue_style( 'google-font-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap', array(), null );
    }

    // Pass AJAX URL and security nonce to our JavaScript file
    wp_localize_script( 'inersialab-contact-script', 'inersialabContactData', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'inersialab_contact_nonce' )
    ) );

    ob_start();
    ?>
    <section class="inersialab-contact-section">
        <div class="inersialab-contact-container">
            
            <!-- Left Side Column: Info and Heading -->
            <div class="inersialab-contact-left">
                <h2 class="inersialab-contact-heading">Get in —<br>touch with us</h2>
                <p class="inersialab-contact-desc">
                    We're here to help! Whether you have a question about our services, need assistance with your account, or want to provide feedback, our team is ready to assist you.
                </p>
                
                <div class="inersialab-contact-info-list">
                    <div class="inersialab-info-item">
                        <span class="inersialab-info-label">Email:</span>
                        <a href="mailto:contact@inersialab.com" class="inersialab-info-value">contact@inersialab.com</a>
                    </div>
                    <div class="inersialab-info-item">
                        <span class="inersialab-info-label">Phone:</span>
                        <a href="tel:+123456778" class="inersialab-info-value">+1 234 567 78</a>
                    </div>
                </div>
                
                <div class="inersialab-contact-note">
                    Available Monday to Friday, 9 AM - 6 PM GMT
                </div>
                
                <a href="https://inersialab.com/live-chat" class="inersialab-btn-livechat" target="_blank" rel="noopener noreferrer">
                    <span class="btn-text">Live Chat</span>
                    <span class="btn-arrow-circle">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </a>
            </div>

            <!-- Right Side Column: Form Card -->
            <div class="inersialab-contact-right">
                <div class="inersialab-contact-card">
                    <form id="inersialab-contact-form" novalidate>
                        
                        <!-- Side-by-side Row for Names -->
                        <div class="inersialab-form-row">
                            <div class="inersialab-form-group">
                                <label for="inersialab-first-name" class="inersialab-form-label">First Name</label>
                                <input type="text" name="first_name" id="inersialab-first-name" class="inersialab-form-control" placeholder="Enter your first name..." required>
                                <span class="inersialab-error-msg" id="err-first-name"></span>
                            </div>
                            <div class="inersialab-form-group">
                                <label for="inersialab-last-name" class="inersialab-form-label">Last Name</label>
                                <input type="text" name="last_name" id="inersialab-last-name" class="inersialab-form-control" placeholder="Enter your last name..." required>
                                <span class="inersialab-error-msg" id="err-last-name"></span>
                            </div>
                        </div>
                        
                        <!-- Full-width Email field -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-email" class="inersialab-form-label">Email</label>
                            <input type="email" name="email" id="inersialab-email" class="inersialab-form-control" placeholder="Enter your email address..." required>
                            <span class="inersialab-error-msg" id="err-email"></span>
                        </div>
                        
                        <!-- Full-width Message Textarea -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-message" class="inersialab-form-label">How can we help you?</label>
                            <textarea name="message" id="inersialab-message" class="inersialab-form-control text-area" placeholder="Enter your message..." required></textarea>
                            <span class="inersialab-error-msg" id="err-message"></span>
                        </div>
                        
                        <!-- Form Submission Action Row -->
                        <div class="inersialab-form-submit-row">
                            <button type="submit" class="inersialab-btn-submit" id="inersialab-submit-btn">
                                <span class="btn-text">Send Message</span>
                                <span class="btn-arrow-circle">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        
                        <!-- AJAX Response Indicator / Messages -->
                        <div class="inersialab-form-response" id="inersialab-form-response"></div>
                        
                    </form>
                </div>
            </div>
            
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// Process AJAX submissions
add_action( 'wp_ajax_inersialab_send_contact', 'inersialab_send_contact_ajax_handler' );
add_action( 'wp_ajax_nopriv_inersialab_send_contact', 'inersialab_send_contact_ajax_handler' );

function inersialab_send_contact_ajax_handler() {
    // 1. Verify nonce security token
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'inersialab_contact_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security token is invalid or expired. Please refresh the page and try again.' ) );
    }

    // 2. Fetch and sanitize form data
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
    $email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    // 3. Server-side validation checks
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $message ) ) {
        wp_send_json_error( array( 'message' => 'All form fields are required.' ) );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'The email address provided is invalid.' ) );
    }

    // 4. Construct Email Parameters
    $to      = 'kmlmes13@gmail.com';
    $subject = 'New Contact Message from InersiaLab Website';
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    // Email HTML Template
    $body  = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a2550;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f2f8; border-radius: 8px; background-color: #ffffff;">';
    $body .= '<h2 style="color: #FF6B35; border-bottom: 2px solid #f0f2f8; padding-bottom: 10px; margin-top: 0;">New Contact Form Submission</h2>';
    $body .= '<p><strong>First Name:</strong> ' . esc_html( $first_name ) . '</p>';
    $body .= '<p><strong>Last Name:</strong> ' . esc_html( $last_name ) . '</p>';
    $body .= '<p><strong>Email Address:</strong> <a href="mailto:' . esc_attr( $email ) . '" style="color: #FF6B35; text-decoration: none;">' . esc_html( $email ) . '</a></p>';
    $body .= '<p><strong>Message:</strong></p>';
    $body .= '<div style="background-color: #f0f2f8; padding: 15px; border-radius: 6px; white-space: pre-wrap; font-style: italic; color: #333333;">' . esc_html( $message ) . '</div>';
    $body .= '</div></body></html>';

    // 5. Send using built-in wp_mail()
    $mail_sent = wp_mail( $to, $subject, $body, $headers );

    if ( $mail_sent ) {
        wp_send_json_success( array( 'message' => "Your message has been sent! We'll get back to you soon." ) );
    } else {
        wp_send_json_error( array( 'message' => 'An error occurred while sending the email. Please try again later.' ) );
    }
}
