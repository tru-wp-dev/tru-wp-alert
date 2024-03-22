<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('Alt_Wp_Settings')){
class Alt_Wp_Settings {
    public function __construct() {
        // Hook the method to add admin menu
        add_action('admin_menu', array($this, 'alt_api_add_admin_menu'));
        // Hook the method to register settings
        add_action('admin_init', array($this, 'alt_register_custom_settings'));
    }

    // Callback function to add admin menu
    public function alt_api_add_admin_menu() {
        add_menu_page(
            __( 'Tru WP Alert', 'tru-wp-alert' ), // Page title
            'Tru WP Alert', // Menu title
            'manage_options',     // Capability required
            'tru-wp-alert', // Menu slug
            array($this, 'render_settings_page'),
            'dashicons-admin-page' // Callback function to render the page
        );
    }

    // Callback function to register custom settings
    public function alt_register_custom_settings() {
        $settings = array(
            'alt_secret_token',
            'alt_dashboard_secret_key'
        );
    
        // Loop through the array and register each setting
        foreach ($settings as $setting) {
            register_setting('alt_settings_group', $setting);
        }
      
        // Add settings section and fields if needed
        add_settings_section(
            'alt_settings_section', // Section ID
            'Tru Wp Alert Settings', // Section title
            array($this, 'section_callback'), // Callback function to render the section description
            'tru-wp-alert' // Menu slug of the page where the section should be displayed
        );

        // Add your settings field
        add_settings_field(
            'alt_setting_name', // Field ID
            'Wp Secret Token', // Field label
            array($this, 'field_callback'), // Callback function to render the field input
            'tru-wp-alert', // Menu slug of the page where the field should be displayed
            'alt_settings_section' // Section ID where the field should be displayed
        );
        // Add your settings field
        add_settings_field(
            'alt_dashboard_secret_key', // Field ID
            'Dashboard Secret Token', // Field label
            array($this, 'dashboard_secret_key'), // Callback function to render the field input
            'tru-wp-alert', // Menu slug of the page where the field should be displayed
            'alt_settings_section' // Section ID where the field should be displayed
        );
    }

    // Callback function to render the settings page
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2>Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields('alt_settings_group'); ?>
                <?php do_settings_sections('tru-wp-alert'); ?>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    // Callback function to render the section description
    public function section_callback() {
        // echo 'Enter your settings below:';
    }

    // Callback function to render the field input
    public function field_callback() {
        $setting_value = get_option('alt_secret_token');
        echo '<input type="text" name="alt_secret_token" value="' . esc_attr($setting_value) . '" readonly="true" class="tra-wp-secret">
        <button type="button" class="button button-primary tra-generate-token">Refresh Token</button>';
        echo '
        <div class="tra-popup" id="tra-popup">
        <div class="tra-loader"></div>
            <p>Are you sure you want to proceed?</p>
            <button type="button" class="tra-confirm-action">Yes</button>
            <button type="button" class="tra-cancelled-action">No</button>
        </div>';
    }
    public function dashboard_secret_key() {
        $setting_value = get_option('alt_dashboard_secret_key');
        echo '<input type="text" name="alt_dashboard_secret_key" value="' . esc_attr($setting_value) . '" class="tra-dashboard-secret">';
    }
}
}
// Instantiate your plugin class
$your_plugin_instance = new Alt_Wp_Settings();
