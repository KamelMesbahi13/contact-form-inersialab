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

    // Detect if current page context is Arabic based on url containing '/ar'
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
    $is_arabic = preg_match( '#(/ar/|/ar$|/ar\?)#', $uri );

    // Translation Dictionary
    $lang = array(
        'direction'   => $is_arabic ? 'rtl' : 'ltr',
        'rtl_class'   => $is_arabic ? 'inersialab-rtl' : '',
        'heading'     => $is_arabic ? 'تواصل —<br>معنا' : 'Prenez —<br>contact avec nous',
        'desc'        => $is_arabic ? 'نحن هنا لمساعدتك! سواء كان لديك سؤال حول خدماتنا، أو تحتاج إلى مساعدة في حسابك، أو تريد تقديم ملاحظاتك، فإن فريقنا مستعد لمساعدتك.' : "Nous sommes là pour vous aider ! Que vous ayez une question sur nos services, besoin d'aide avec votre compte ou que vous souhaitiez nous faire part de vos commentaires, notre équipe est prête à vous aider.",
        'email_lbl'   => $is_arabic ? 'البريد الإلكتروني:' : 'Email :',
        'phone_lbl'   => $is_arabic ? 'الهاتف:' : 'Téléphone :',
        'email_val'   => 'info@inersialab.com',
        'phone_val'   => '+1 234 567 78',
        'note'        => $is_arabic ? 'متاح من الاثنين إلى الجمعة، من 9 صباحًا حتى 6 مساءً بتوقيت غرينتش' : 'Disponible du lundi au vendredi, de 9 AM - 6 PM GMT',
        'first_name'  => $is_arabic ? 'الاسم الأول' : 'Prénom',
        'first_placeholder' => $is_arabic ? 'أدخل اسمك الأول...' : 'Entrez votre prénom...',
        'last_name'   => $is_arabic ? 'اسم العائلة' : 'Nom',
        'last_placeholder'  => $is_arabic ? 'أدخل اسم العائلة...' : 'Entrez votre nom...',
        'email'       => $is_arabic ? 'البريد الإلكتروني' : 'Email',
        'email_placeholder' => $is_arabic ? 'أدخل عنوان بريدك الإلكتروني...' : 'Entrez votre adresse email...',
        'phone'       => $is_arabic ? 'رقم الهاتف' : 'Téléphone',
        'phone_placeholder' => $is_arabic ? 'أدخل رقم هاتفك...' : 'Entrez votre numéro de téléphone...',
        'service'     => $is_arabic ? 'الخدمة المطلوبة' : 'Service requis',
        'service_placeholder' => $is_arabic ? '-- اختر خدمة --' : '-- Choisissez un service --',
        'message'     => $is_arabic ? 'كيف يمكننا مساعدتك؟' : 'Comment pouvons-nous vous aider ?',
        'msg_placeholder'   => $is_arabic ? 'أدخل رسالتك...' : 'Entrez votre message...',
        'send_msg'    => $is_arabic ? 'إرسال الرسالة' : 'Envoyer',
    );

    // List of offered services
    $services_options = $is_arabic ? array(
        'app_dev'          => 'تطوير التطبيقات',
        'web_dev'          => 'تطوير المواقع',
        'marketing_dig'    => 'التسويق الرقمي',
        'branding_id'      => 'الهوية البصرية والعلامة التجارية',
        'seo'              => 'تحسين محركات البحث (SEO)',
        'social_media'     => 'إدارة مواقع التواصل الاجتماعي',
        'content_creation' => 'صناعة المحتوى',
        'consulting'       => 'استشارات الاستراتيجية الرقمية',
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
        'lang_code' => $is_arabic ? 'ar' : 'fr',
        'messages' => array(
            'first_name_required' => $is_arabic ? 'الاسم الأول مطلوب.' : 'Le prénom est requis.',
            'first_name_invalid'  => $is_arabic ? 'يرجى إدخال اسم أول صالح (حرفين على الأقل).' : 'Veuillez entrer un prénom valide (au moins 2 lettres).',
            'last_name_required'  => $is_arabic ? 'اسم العائلة مطلوب.' : 'Le nom est requis.',
            'last_name_invalid'   => $is_arabic ? 'يرجى إدخال اسم عائلة صالح (حرفين على الأقل).' : 'Veuillez entrer un nom valide (au moins 2 lettres).',
            'email_required'      => $is_arabic ? 'البريد الإلكتروني مطلوب.' : "L'adresse email est requise.",
            'email_invalid'       => $is_arabic ? 'يرجى إدخال بريد إلكتروني صالح.' : 'Veuillez entrer une adresse email valide.',
            'phone_required'      => $is_arabic ? 'رقم الهاتف مطلوب.' : 'Le numéro de téléphone est requis.',
            'phone_invalid'       => $is_arabic ? 'يرجى إدخال رقم هاتف صالح.' : 'Veuillez entrer un numéro de téléphone valide.',
            'service_required'    => $is_arabic ? 'يرجى اختيار خدمة.' : 'Veuillez sélectionner un service.',
            'message_required'    => $is_arabic ? 'الرسالة مطلوبة.' : 'Le message est requis.',
            'sending'             => $is_arabic ? 'جاري الإرسال...' : 'Envoi...',
            'error_general'       => $is_arabic ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'Une erreur est survenue. Veuillez réessayer.',
            'error_conn'          => $is_arabic ? 'تعذر إرسال الرسالة بسبب خطأ في الاتصال. يرجى المحاولة لاحقًا.' : "Impossible d'envoyer le message en raison d'une erreur de connexion. Veuillez réessayer plus tard."
        )
    ) );

    ob_start();
    ?>
    <section class="inersialab-contact-section <?php echo esc_attr( $lang['rtl_class'] ); ?>">
        <div class="inersialab-contact-container">
            
            <!-- Left Side Column: Info and Heading -->
            <div class="inersialab-contact-left">
                <h1 class="inersialab-contact-heading"><?php echo wp_kses_post( $lang['heading'] ); ?></h1>
                <p class="inersialab-contact-desc">
                    <?php echo esc_html( $lang['desc'] ); ?>
                </p>
                
                <div class="inersialab-contact-info-list">
                    <div class="inersialab-info-item">
                        <span class="inersialab-info-label"><?php echo esc_html( $lang['email_lbl'] ); ?></span>
                        <a href="mailto:<?php echo esc_attr( $lang['email_val'] ); ?>" class="inersialab-info-value"><?php echo esc_html( $lang['email_val'] ); ?></a>
                    </div>
                    <div class="inersialab-info-item">
                        <span class="inersialab-info-label"><?php echo esc_html( $lang['phone_lbl'] ); ?></span>
                        <a href="tel:<?php echo esc_attr( str_replace( ' ', '', $lang['phone_val'] ) ); ?>" class="inersialab-info-value"><?php echo esc_html( $lang['phone_val'] ); ?></a>
                    </div>
                </div>
                
                <div class="inersialab-contact-note">
                    <?php echo esc_html( $lang['note'] ); ?>
                </div>
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
    $lang_code = isset( $_POST['lang'] ) ? sanitize_text_field( wp_unslash( $_POST['lang'] ) ) : 'fr';
    $is_arabic = ( $lang_code === 'ar' );

    // 4. Honeypot Check (Silently reject by pretending it was successful)
    if ( ! empty( $_POST['website'] ) ) {
        $msg = $is_arabic ? 'تم إرسال رسالتك! سنقوم بالرد عليك قريبًا.' : 'Votre message a été envoyé ! Nous vous répondrons bientôt.';
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
        $limit_msg = $is_arabic ? 'محاولات كثيرة جداً. يرجى المحاولة مرة أخرى بعد ساعة.' : 'Trop de tentatives. Veuillez réessayer dans une heure.';
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
        $msg = $is_arabic ? 'جميع الحقول مطلوبة.' : 'Tous les champs sont requis.';
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // Name validation (minimum 2 letters/accents/spaces/hyphens/apostrophes)
    if ( ! preg_match( '/^[\p{L}\s\-\']{2,}$/u', $first_name ) ) {
        $msg = $is_arabic ? 'الاسم الأول غير صالح (يجب أن يكون حرفين على الأقل ويحتوي على حروف فقط).' : 'Le prénom est invalide (doit contenir au moins 2 lettres).';
        wp_send_json_error( array( 'message' => $msg ) );
    }
    if ( ! preg_match( '/^[\p{L}\s\-\']{2,}$/u', $last_name ) ) {
        $msg = $is_arabic ? 'اسم العائلة غير صالح (يجب أن يكون حرفين على الأقل ويحتوي على حروف فقط).' : 'Le nom est invalide (doit contenir au moins 2 lettres).';
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // Email check
    if ( ! is_email( $email ) ) {
        $msg = $is_arabic ? 'يرجى إدخال بريد إلكتروني صالح.' : "L'adresse email n'est pas valide.";
        wp_send_json_error( array( 'message' => $msg ) );
    }

    // 8. Service Whitelist Validation
    $services_whitelist = array(
        'app_dev'          => 'Développement d\'application',
        'web_dev'          => 'Développement web',
        'marketing_dig'    => 'Marketing digital',
        'branding_id'      => 'Branding & identité visuelle',
        'seo'              => 'Référencement SEO',
        'social_media'     => 'Gestion des réseaux sociaux',
        'content_creation' => 'Création de contenu',
        'consulting'       => 'Conseil en stratégie digitale',
    );

    if ( ! array_key_exists( $service, $services_whitelist ) ) {
        $msg = $is_arabic ? 'الخدمة المحددة غير صالحة.' : 'Le service sélectionné est invalide.';
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

        $msg = $is_arabic ? 'تم إرسال رسالتك! سنقوم بالرد عليك قريبًا.' : "Votre message a été envoyé ! Nous vous répondrons bientôt.";
        wp_send_json_success( array( 'message' => $msg ) );
    } else {
        $msg = $is_arabic ? 'حدث خطأ أثناء إرسال البريد الإلكتروني. يرجى المحاولة لاحقًا.' : "Une erreur est survenue lors de l'envoi de l'email. Veuillez réessayer plus tard.";
        wp_send_json_error( array( 'message' => $msg ) );
    }
}
