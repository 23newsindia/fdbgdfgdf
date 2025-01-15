<?php
// Add these settings to your existing SecuritySettings class
// Add this inside the $options array in render_settings_page()
'enable_recaptcha' => get_option('security_enable_recaptcha', false),
'recaptcha_site_key' => get_option('security_recaptcha_site_key', ''),
'recaptcha_secret_key' => get_option('security_recaptcha_secret_key', ''),
'enable_age_verification' => get_option('security_enable_age_verification', false),
'minimum_age' => get_option('security_minimum_age', 18),
'enable_terms' => get_option('security_enable_terms', false),
'terms_text' => get_option('security_terms_text', ''),
'enable_email_verification' => get_option('security_enable_email_verification', false),
'allowed_email_domains' => get_option('security_allowed_email_domains', ''),

// Add this section to your settings form, before the closing </table> tag
?>
<tr>
    <th>Registration Security</th>
    <td>
        <h4>reCAPTCHA Settings</h4>
        <label>
            <input type="checkbox" name="enable_recaptcha" value="1" <?php checked($options['enable_recaptcha']); ?>>
            Enable reCAPTCHA on Registration
        </label><br>
        <label>
            Site Key:<br>
            <input type="text" name="recaptcha_site_key" value="<?php echo esc_attr($options['recaptcha_site_key']); ?>" class="regular-text">
        </label><br>
        <label>
            Secret Key:<br>
            <input type="text" name="recaptcha_secret_key" value="<?php echo esc_attr($options['recaptcha_secret_key']); ?>" class="regular-text">
        </label>
        
        <h4>Email Verification</h4>
        <label>
            <input type="checkbox" name="enable_email_verification" value="1" <?php checked($options['enable_email_verification']); ?>>
            Enable Email Verification
        </label><br>
        <label>
            Allowed Email Domains (one per line):<br>
            <textarea name="allowed_email_domains" rows="3" cols="50" class="large-text"><?php echo esc_textarea($options['allowed_email_domains']); ?></textarea>
        </label>
        <p class="description">Leave empty to allow all domains</p>
        
        <h4>Age Verification</h4>
        <label>
            <input type="checkbox" name="enable_age_verification" value="1" <?php checked($options['enable_age_verification']); ?>>
            Enable Age Verification
        </label><br>
        <label>
            Minimum Age:
            <input type="number" name="minimum_age" value="<?php echo esc_attr($options['minimum_age']); ?>" min="13" max="100">
        </label>
        
        <h4>Terms and Conditions</h4>
        <label>
            <input type="checkbox" name="enable_terms" value="1" <?php checked($options['enable_terms']); ?>>
            Enable Terms and Conditions
        </label><br>
        <label>
            Terms and Conditions Text:<br>
            <textarea name="terms_text" rows="5" cols="50" class="large-text"><?php echo esc_textarea($options['terms_text']); ?></textarea>
        </label>
    </td>
</tr>
<?php

// Add these to your save_settings() method
update_option('security_enable_recaptcha', isset($_POST['enable_recaptcha']));
update_option('security_recaptcha_site_key', sanitize_text_field($_POST['recaptcha_site_key']));
update_option('security_recaptcha_secret_key', sanitize_text_field($_POST['recaptcha_secret_key']));
update_option('security_enable_age_verification', isset($_POST['enable_age_verification']));
update_option('security_minimum_age', intval($_POST['minimum_age']));
update_option('security_enable_terms', isset($_POST['enable_terms']));
update_option('security_terms_text', wp_kses_post($_POST['terms_text']));
update_option('security_enable_email_verification', isset($_POST['enable_email_verification']));
update_option('security_allowed_email_domains', sanitize_textarea_field($_POST['allowed_email_domains']));

// Add these to your register_settings() method
register_setting('security_settings', 'security_enable_recaptcha');
register_setting('security_settings', 'security_recaptcha_site_key');
register_setting('security_settings', 'security_recaptcha_secret_key');
register_setting('security_settings', 'security_enable_age_verification');
register_setting('security_settings', 'security_minimum_age');
register_setting('security_settings', 'security_enable_terms');
register_setting('security_settings', 'security_terms_text');
register_setting('security_settings', 'security_enable_email_verification');
register_setting('security_settings', 'security_allowed_email_domains');