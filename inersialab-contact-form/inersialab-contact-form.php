<?php
/**
 * Plugin Name: InersiaLab Contact Form
 * Description: Premium two-column contact form shortcode [inersialab_contact] with AJAX email submission.
 * Version: 1.1.0
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
        '1.1.0' 
    );
    wp_register_script( 
        'inersialab-contact-script', 
        plugins_url( 'js/script.js', __FILE__ ), 
        array(), 
        '1.1.0', 
        true 
    );
}

// Shortcode implementation
add_shortcode( 'inersialab_contact', 'inersialab_contact_shortcode_handler' );
function inersialab_contact_shortcode_handler( $atts ) {
    // Parse shortcode attributes
    $atts = shortcode_atts( array(
        'show_map' => 'no',
    ), $atts, 'inersialab_contact' );

    $show_map = ( strtolower( $atts['show_map'] ) === 'yes' );

    // Enqueue registered styles and scripts
    wp_enqueue_style( 'inersialab-contact-style' );
    wp_enqueue_script( 'inersialab-contact-script' );

    // Enqueue Montserrat font if it hasn't been enqueued yet
    if ( ! wp_style_is( 'google-font-montserrat', 'enqueued' ) ) {
        wp_enqueue_style( 'google-font-montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap', array(), null );
    }

    // Detect if current page context is French based on url containing '/fr' (otherwise default to English)
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    $is_english = ! preg_match( '#(/fr/|/fr$|/fr\?)#', $uri );

    // Translation Dictionary — English (default) / French (/fr)
    $lang = array(
        'heading'     => $is_english ? 'Get in —<br>touch with us' : 'Prenez —<br>contact avec nous',
        'desc'        => $is_english ? 'We are here to help! Whether you have a question about our services, need help with your account, or want to share your feedback, our team is ready to assist you.' : "Nous sommes là pour vous aider ! Que vous ayez une question sur nos services, besoin d'aide avec votre compte ou que vous souhaitiez nous faire part de vos commentaires, notre équipe est prête à vous aider.",
        'email_lbl'   => $is_english ? 'Email:' : 'Email :',
        'phone_lbl'   => $is_english ? 'Phone:' : 'Téléphone :',
        'email_val'   => 'info@inersialab.com',
        'phone_val'   => '+1 234 567 78',
        'note'        => $is_english ? 'Available Monday to Friday, 9 AM - 6 PM GMT' : 'Disponible du lundi au vendredi, de 9 AM - 6 PM GMT',
        'first_name'  => $is_english ? 'First Name' : 'Prénom',
        'first_placeholder' => $is_english ? 'Enter your first name...' : 'Entrez votre prénom...',
        'last_name'   => $is_english ? 'Last Name' : 'Nom',
        'last_placeholder'  => $is_english ? 'Enter your last name...' : 'Entrez votre nom...',
        'email'       => $is_english ? 'Email' : 'Email',
        'email_placeholder' => $is_english ? 'Enter your email address...' : 'Entrez votre adresse email...',
        'phone'       => $is_english ? 'Phone' : 'Téléphone',
        'phone_placeholder' => $is_english ? 'Enter your phone number...' : 'Entrez votre numéro de téléphone...',
        'service'     => $is_english ? 'Required Service' : 'Service requis',
        'service_placeholder' => $is_english ? '-- Choose a service --' : '-- Choisissez un service --',
        'message'     => $is_english ? 'How can we help you?' : 'Comment pouvons-nous vous aider ?',
        'msg_placeholder'   => $is_english ? 'Enter your message...' : 'Entrez votre message...',
        'send_msg'    => $is_english ? 'Send Message' : 'Envoyer',
    );

    // List of offered services
    $services_options = $is_english ? array(
        'app_dev'          => 'App Development',
        'web_dev'          => 'Web Development',
        'marketing_dig'    => 'Digital Marketing',
        'branding_id'      => 'Branding & Visual Identity',
        'seo'              => 'SEO Optimization',
        'social_media'     => 'Social Media Management',
        'content_creation' => 'Content Creation',
        'consulting'       => 'Digital Strategy Consulting',
    ) : array(
        'app_dev'          => 'Développement d\'application',
        'web_dev'          => 'Développement web',
        'marketing_dig'    => 'Marketing digital',
        'branding_id'      => 'Branding & identité visuelle',
        'seo'              => 'Référencement SEO',
        'social_media'     => 'Gestion des réseaux sociaux',
        'content_creation' => 'Création de contenu',
        'consulting'       => 'Conseil en stratégie digitale',
    );

    // Pass AJAX URL, security nonce, and language strings to our JavaScript file
    wp_localize_script( 'inersialab-contact-script', 'inersialabContactData', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'inersialab_contact_nonce' ),
        'lang_code' => $is_english ? 'en' : 'fr',
        'messages' => array(
            'first_name_required' => $is_english ? 'First name is required.' : 'Le prénom est requis.',
            'first_name_invalid'  => $is_english ? 'Please enter a valid first name (at least 2 letters).' : 'Veuillez entrer un prénom valide (au moins 2 lettres).',
            'last_name_required'  => $is_english ? 'Last name is required.' : 'Le nom est requis.',
            'last_name_invalid'   => $is_english ? 'Please enter a valid last name (at least 2 letters).' : 'Veuillez entrer un nom valide (au moins 2 lettres).',
            'email_required'      => $is_english ? 'Email address is required.' : "L'adresse email est requise.",
            'email_invalid'       => $is_english ? 'Please enter a valid email address.' : 'Veuillez entrer une adresse email valide.',
            'phone_required'      => $is_english ? 'Phone number is required.' : 'Le numéro de téléphone est requis.',
            'phone_invalid'       => $is_english ? 'Please enter a valid phone number.' : 'Veuillez entrer un numéro de téléphone valide.',
            'service_required'    => $is_english ? 'Please select a service.' : 'Veuillez sélectionner un service.',
            'message_required'    => $is_english ? 'Message is required.' : 'Le message est requis.',
            'sending'             => $is_english ? 'Sending...' : 'Envoi...',
            'error_general'       => $is_english ? 'An error occurred. Please try again.' : 'Une erreur est survenue. Veuillez réessayer.',
            'error_conn'          => $is_english ? 'Unable to send the message due to a connection error. Please try again later.' : "Impossible d'envoyer le message en raison d'une erreur de connexion. Veuillez réessayer plus tard."
        )
    ) );

    ob_start();
    ?>
    <section class="inersialab-contact-section">
        <div class="inersialab-contact-container <?php echo $show_map ? 'inersialab-has-map' : ''; ?>">
            
            <!-- Left Side Column: Info and Heading -->
            <div class="inersialab-contact-left">
                <!-- Contact tag badge -->
                <span class="inersialab-contact-tag"><?php echo $is_english ? 'Contact' : 'Contact'; ?></span>

                <h1 class="inersialab-contact-heading"><?php echo wp_kses_post( $lang['heading'] ); ?></h1>
                <p class="inersialab-contact-desc">
                    <?php echo esc_html( $lang['desc'] ); ?>
                </p>
                
                <div class="inersialab-contact-info-list">
                    <div class="inersialab-info-item">
                        <div class="inersialab-info-text">
                            <span class="inersialab-info-label"><?php echo esc_html( $lang['email_lbl'] ); ?></span>
                            <a href="mailto:<?php echo esc_attr( $lang['email_val'] ); ?>" class="inersialab-info-value"><?php echo esc_html( $lang['email_val'] ); ?></a>
                        </div>
                    </div>
                    <div class="inersialab-info-item">
                        <div class="inersialab-info-text">
                            <span class="inersialab-info-label"><?php echo esc_html( $lang['phone_lbl'] ); ?></span>
                            <a href="tel:<?php echo esc_attr( str_replace( ' ', '', $lang['phone_val'] ) ); ?>" class="inersialab-info-value"><?php echo esc_html( $lang['phone_val'] ); ?></a>
                        </div>
                    </div>
                </div>
                
                <div class="inersialab-contact-note">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo esc_html( $lang['note'] ); ?>
                </div>

                <?php if ( $show_map ) : ?>
                <!-- Google Maps Embed inside left column -->
                <div class="inersialab-map-wrapper">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3196.837958628775!2d3.4590444999999996!3d36.75046040000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x128e699a0a00b593%3A0xeb19e4961ee53cbe!2sUPS%20STORE%20BOUMERDES!5e0!3m2!1sen!2sdz!4v1782986721045!5m2!1sen!2sdz" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Side Column: Form Card -->
            <div class="inersialab-contact-right">
                <div class="inersialab-contact-card">
                    <form id="inersialab-contact-form" novalidate>
                        <!-- Nonce & Honeypot Fields -->
                        <?php wp_nonce_field( 'inersialab_contact_submit', 'inersialab_contact_nonce_field' ); ?>
                        <div style="display:none !important; position:absolute; left:-9999px;">
                            <label for="inersialab-website">Website</label>
                            <input type="text" name="website" id="inersialab-website" tabindex="-1" autocomplete="off">
                        </div>
                        
                        <!-- Side-by-side Row for Names -->
                        <div class="inersialab-form-row">
                            <div class="inersialab-form-group">
                                <label for="inersialab-first-name" class="inersialab-form-label"><?php echo esc_html( $lang['first_name'] ); ?></label>
                                <input type="text" name="first_name" id="inersialab-first-name" class="inersialab-form-control" placeholder="<?php echo esc_attr( $lang['first_placeholder'] ); ?>" required>
                                <span class="inersialab-error-msg" id="err-first-name"></span>
                            </div>
                            <div class="inersialab-form-group">
                                <label for="inersialab-last-name" class="inersialab-form-label"><?php echo esc_html( $lang['last_name'] ); ?></label>
                                <input type="text" name="last_name" id="inersialab-last-name" class="inersialab-form-control" placeholder="<?php echo esc_attr( $lang['last_placeholder'] ); ?>" required>
                                <span class="inersialab-error-msg" id="err-last-name"></span>
                            </div>
                        </div>
                        
                        <!-- Full-width Email field -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-email" class="inersialab-form-label"><?php echo esc_html( $lang['email'] ); ?></label>
                            <input type="email" name="email" id="inersialab-email" class="inersialab-form-control" placeholder="<?php echo esc_attr( $lang['email_placeholder'] ); ?>" required>
                            <span class="inersialab-error-msg" id="err-email"></span>
                        </div>

                        <!-- Full-width Phone field -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-phone" class="inersialab-form-label"><?php echo esc_html( $lang['phone'] ); ?></label>
                            <input type="tel" name="phone" id="inersialab-phone" class="inersialab-form-control" placeholder="<?php echo esc_attr( $lang['phone_placeholder'] ); ?>" required>
                            <span class="inersialab-error-msg" id="err-phone"></span>
                        </div>

                        <!-- Full-width Service dropdown field -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-service" class="inersialab-form-label"><?php echo esc_html( $lang['service'] ); ?></label>
                            <select name="service" id="inersialab-service" class="inersialab-form-control select-field" required>
                                <option value="" disabled selected><?php echo esc_html( $lang['service_placeholder'] ); ?></option>
                                <?php foreach ( $services_options as $key => $label ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="inersialab-error-msg" id="err-service"></span>
                        </div>
                        
                        <!-- Full-width Message Textarea -->
                        <div class="inersialab-form-group">
                            <label for="inersialab-message" class="inersialab-form-label"><?php echo esc_html( $lang['message'] ); ?></label>
                            <textarea name="message" id="inersialab-message" class="inersialab-form-control text-area" placeholder="<?php echo esc_attr( $lang['msg_placeholder'] ); ?>" required></textarea>
                            <span class="inersialab-error-msg" id="err-message"></span>
                        </div>
                        
                        <!-- Form Submission Action Row -->
                        <div class="inersialab-form-submit-row">
                            <button type="submit" class="inersialab-btn-submit" id="inersialab-submit-btn">
                                <span class="inersialab-btn-text"><?php echo esc_html( $lang['send_msg'] ); ?></span>
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
    // 1. Context & Request checks
    if ( ! wp_doing_ajax() ) {
        exit;
    }
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
        exit;
    }

    // 2. CSRF Referer Verification
    $referer  = wp_get_referer();
    $home_url = home_url();
    if ( ! $referer || strpos( $referer, $home_url ) !== 0 ) {
        exit;
    }

    // 3. Nonce Verification (reject silently)
    if ( ! isset( $_POST['inersialab_contact_nonce_field'] ) || ! wp_verify_nonce( $_POST['inersialab_contact_nonce_field'], 'inersialab_contact_submit' ) ) {
        exit;
    }

    // Fetch language code to dynamically translate responses
    $lang_code = isset( $_POST['lang'] ) ? sanitize_text_field( wp_unslash( $_POST['lang'] ) ) : 'en';
    $is_english = ( $lang_code === 'en' );

    // 4. Honeypot Check (Silently reject by pretending it was successful)
    if ( ! empty( $_POST['website'] ) ) {
        $msg = $is_english ? 'Your message has been sent! We will get back to you soon.' : 'Votre message a été envoyé ! Nous vous répondrons bientôt.';
        wp_send_json_success( array( 'message' => $msg ) );
    }

    // 5. Rate Limiting (transient count per IP)
    $ip = '';
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ip_list = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
        $ip = trim( reset( $ip_list ) );
    } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $ip = filter_var( $ip, FILTER_VALIDATE_IP );
    if ( ! $ip ) {
        $ip = '0.0.0.0';
    }

    $transient_key = 'inersialab_contact_' . md5( $ip );
    $submit_count  = get_transient( $transient_key );
    if ( $submit_count === false ) {
        $submit_count = 0;
    }
    if ( $submit_count >= 10 ) {
        $limit_msg = $is_english ? 'Too many attempts. Please try again in an hour.' : 'Trop de tentatives. Veuillez réessayer dans une heure.';
        wp_send_json_error( array( 'message' => $limit_msg ) );
    }

    // 6. Fetch and sanitize form data
    $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
    $email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $service    = isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '';
    $message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    // Phone: strip everything except digits, +, spaces, -, () using preg_replace
    $phone = preg_replace( '/[^0-9+\s\-()]/', '', $phone );

    // 7. Required Fields Enforcement
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $phone ) || empty( $service ) || empty( $message ) ) {
        $msg = $is_english ? 'All fields are required.' : 'Tous les champs sont requis.';
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // Name validation (minimum 2 letters/accents/spaces/hyphens/apostrophes)
    if ( ! preg_match( '/^[\p{L}\s\-\']{2,}$/u', $first_name ) ) {
        $msg = $is_english ? 'Invalid first name (must contain at least 2 letters).' : 'Le prénom est invalide (doit contenir au moins 2 lettres).';
        wp_send_json_error( array( 'message' => $msg ) );
    }
    if ( ! preg_match( '/^[\p{L}\s\-\']{2,}$/u', $last_name ) ) {
        $msg = $is_english ? 'Invalid last name (must contain at least 2 letters).' : 'Le nom est invalide (doit contenir au moins 2 lettres).';
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // Email check
    if ( ! is_email( $email ) ) {
        $msg = $is_english ? 'Please enter a valid email address.' : "L'adresse email n'est pas valide.";
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // 8. Service Whitelist Validation
    $services_whitelist = array(
        'app_dev'          => 'App Development',
        'web_dev'          => 'Web Development',
        'marketing_dig'    => 'Digital Marketing',
        'branding_id'      => 'Branding & Visual Identity',
        'seo'              => 'SEO Optimization',
        'social_media'     => 'Social Media Management',
        'content_creation' => 'Content Creation',
        'consulting'       => 'Digital Strategy Consulting',
    );

    if ( ! array_key_exists( $service, $services_whitelist ) ) {
        $msg = $is_english ? 'The selected service is invalid.' : 'Le service sélectionné est invalide.';
        wp_send_json_error( array( 'message' => $msg ) );
    }

    $service_label = $services_whitelist[ $service ];

    // 9. Construct Email Parameters securely
    $to      = 'info@inersialab.com';
    $subject = 'New Contact Message from InersiaLab Website';
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    
    // Email HTML Template (all outputs escaped)
    $body  = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1a2550;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0f2f8; border-radius: 8px; background-color: #ffffff;">';
    $body .= '<h2 style="color: #FF6B35; border-bottom: 2px solid #f0f2f8; padding-bottom: 10px; margin-top: 0;">New Contact Form Submission</h2>';
    $body .= '<p><strong>First Name:</strong> ' . esc_html( $first_name ) . '</p>';
    $body .= '<p><strong>Last Name:</strong> ' . esc_html( $last_name ) . '</p>';
    $body .= '<p><strong>Email Address:</strong> <a href="mailto:' . esc_attr( $email ) . '" style="color: #FF6B35; text-decoration: none;">' . esc_html( $email ) . '</a></p>';
    $body .= '<p><strong>Phone Number:</strong> ' . esc_html( $phone ) . '</p>';
    $body .= '<p><strong>Service Requested:</strong> ' . esc_html( $service_label ) . '</p>';
    $body .= '<p><strong>Message:</strong></p>';
    $body .= '<div style="background-color: #f0f2f8; padding: 15px; border-radius: 6px; white-space: pre-wrap; font-style: italic; color: #333333;">' . esc_html( $message ) . '</div>';
    $body .= '</div></body></html>';

    // 10. Send using built-in wp_mail()
    $mail_sent = wp_mail( $to, $subject, $body, $headers );

    if ( $mail_sent ) {
        // Increment transient rate limiter count on success
        $submit_count++;
        set_transient( $transient_key, $submit_count, HOUR_IN_SECONDS );

        $msg = $is_english ? 'Your message has been sent! We will get back to you soon.' : "Votre message a été envoyé ! Nous vous répondrons bientôt.";
        wp_send_json_success( array( 'message' => $msg ) );
    } else {
        $msg = $is_english ? 'An error occurred while sending the email. Please try again later.' : "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        wp_send_json_error( array( 'message' => $msg ) );
    }
}
