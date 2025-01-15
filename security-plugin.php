<?php
// Add this line after loading other classes
require_once plugin_dir_path(__FILE__) . 'includes/class-registration-security.php';

// Add this inside the CustomSecurityPlugin class constructor
if (!is_admin()) {
    new RegistrationSecurity();
}