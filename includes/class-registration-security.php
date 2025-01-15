<?php
class RegistrationSecurity {
    private $options;

    public function __construct() {
        add_action('register_form', array($this, 'add_registration_fields'));
        add_filter('registration_errors', array($this, 'validate_registration_fields'), 10, 3);
        add_action('register_new_user', array($this, 'save_registration_fields'));
        add_action('user_register', array($this, 'send_verification_email'));
        
        // Get options
        $this->options = array(
            'enable_recaptcha' => get_option('security_enable_recaptcha', false),
            'recaptcha_site_key' => get_option('security_recaptcha_site_key', ''),
            'recaptcha_secret_key' => get_option('security_recaptcha_secret_key', ''),
            'enable_age_verification' => get_option('security_enable_age_verification', false),
            'minimum_age' => get_option('security_minimum_age', 18),
            'enable_terms' => get_option('security_enable_terms', false),
            'terms_text' => get_option('security_terms_text', ''),
            'enable_email_verification' => get_option('security_enable_email_verification', false),
            'allowed_email_domains' => get_option('security_allowed_email_domains', '')
        );
    }

    public function add_registration_fields() {
        // reCAPTCHA
        if ($this->options['enable_recaptcha'] && $this->options['recaptcha_site_key']) {
            ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($this->options['recaptcha_site_key']); ?>"></div>
            <?php
        }

        // Age Verification
        if ($this->options['enable_age_verification']) {
            ?>
            <p>
                <label for="birthdate">Date of Birth<br/>
                <input type="date" name="birthdate" id="birthdate" class="input" required /></label>
            </p>
            <?php
        }

        // Terms and Conditions
        if ($this->options['enable_terms']) {
            ?>
            <p>
                <label>
                    <input type="checkbox" name="terms_accepted" required />
                    I accept the Terms and Conditions
                </label>
                <div class="terms-text" style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">
                    <?php echo wp_kses_post($this->options['terms_text']); ?>
                </div>
            </p>
            <?php
        }
    }

    public function validate_registration_fields($errors, $sanitized_user_login, $user_email) {
        // Validate reCAPTCHA
        if ($this->options['enable_recaptcha']) {
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            if (!$this->verify_recaptcha($recaptcha_response)) {
                $errors->add('recaptcha_error', 'Please complete the reCAPTCHA verification.');
            }
        }

        // Validate Age
        if ($this->options['enable_age_verification']) {
            $birthdate = isset($_POST['birthdate']) ? sanitize_text_field($_POST['birthdate']) : '';
            if (!$this->verify_age($birthdate)) {
                $errors->add('age_error', sprintf('You must be at least %d years old to register.', $this->options['minimum_age']));
            }
        }

        // Validate Terms
        if ($this->options['enable_terms'] && empty($_POST['terms_accepted'])) {
            $errors->add('terms_error', 'You must accept the Terms and Conditions.');
        }

        // Validate Email Domain
        if ($this->options['enable_email_verification']) {
            if (!$this->is_allowed_email_domain($user_email)) {
                $errors->add('email_domain_error', 'This email domain is not allowed for registration.');
            }
        }

        return $errors;
    }

    private function verify_recaptcha($response) {
        if (empty($response) || empty($this->options['recaptcha_secret_key'])) {
            return false;
        }

        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $this->options['recaptcha_secret_key'],
                'response' => $response
            )
        ));

        if (is_wp_error($verify)) {
            return false;
        }

        $verify_response = json_decode(wp_remote_retrieve_body($verify));
        return isset($verify_response->success) && $verify_response->success;
    }

    private function verify_age($birthdate) {
        if (empty($birthdate)) {
            return false;
        }

        $birth_time = strtotime($birthdate);
        $age = (time() - $birth_time) / (60 * 60 * 24 * 365.25);
        return $age >= $this->options['minimum_age'];
    }

    private function is_allowed_email_domain($email) {
        if (empty($this->options['allowed_email_domains'])) {
            return true;
        }

        $domain = strtolower(substr(strrchr($email, "@"), 1));
        $allowed_domains = array_map('trim', explode("\n", strtolower($this->options['allowed_email_domains'])));
        
        return in_array($domain, $allowed_domains);
    }

    public function send_verification_email($user_id) {
        if (!$this->options['enable_email_verification']) {
            return;
        }

        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        // Generate verification code
        $code = wp_generate_password(20, false);
        update_user_meta($user_id, 'email_verification_code', $code);
        update_user_meta($user_id, 'email_verified', false);

        // Send verification email
        $verify_link = add_query_arg(array(
            'action' => 'verify_email',
            'user' => $user_id,
            'code' => $code
        ), home_url());

        $subject = sprintf('[%s] Verify your email address', get_bloginfo('name'));
        $message = sprintf(
            "Hello %s,\n\nPlease verify your email address by clicking the link below:\n\n%s\n\nIf you didn't register at our site, please ignore this email.\n\nRegards,\n%s",
            $user->display_name,
            esc_url_raw($verify_link),
            get_bloginfo('name')
        );

        wp_mail($user->user_email, $subject, $message);
    }

    public function save_registration_fields($user_id) {
        if ($this->options['enable_age_verification'] && isset($_POST['birthdate'])) {
            update_user_meta($user_id, 'birthdate', sanitize_text_field($_POST['birthdate']));
        }
    }
}