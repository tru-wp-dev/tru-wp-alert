<?php

/**
* This file is used to create settings for plugins.
* 
* includes/alt-settings
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('Alt_Wp_Settings')){
class Alt_Wp_Settings {
    public function __construct() {
        
        add_action('admin_menu', array($this, 'alt_api_add_admin_menu'));
        add_action('admin_init', array($this, 'alt_register_custom_settings'));
    }
    /**
     *  Callback function to add admin menu
     */
    public function alt_api_add_admin_menu() {
        
        add_menu_page(
            __( 'Alertio', 'alertio' ), // Page title
            'Alertio', // Menu title
            'manage_options',     // Capability required
            'alertio', // Menu slug
            array($this, 'render_settings_page'),
            ALT_URL.'assets/images/alertio.png' // Callback function to render the page
        );
    }

    /**
     * Callback function to register custom settings
     */
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
            'Alertio Settings', // Section title
            array($this, 'section_callback'), // Callback function to render the section description
            'alertio' // Menu slug of the page where the section should be displayed
        );

        // Add settings field
        add_settings_field(
            'alt_setting_name', // Field ID
            'Wp Secret Token', // Field label
            array($this, 'field_callback'), // Callback function to render the field input
            'alertio', // Menu slug of the page where the field should be displayed
            'alt_settings_section' // Section ID where the field should be displayed
        );

        // Add settings field
        add_settings_field(
            'alt_dashboard_secret_key', // Field ID
            'Dashboard Secret Token', // Field label
            array($this, 'dashboard_secret_key'), // Callback function to render the field input
            'alertio', // Menu slug of the page where the field should be displayed
            'alt_settings_section' // Section ID where the field should be displayed
        );
    }
    
    /**
     * Callback function to render the settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="alt-wrap">
            <form method="post" action="options.php" class="alt-main-form">
                <?php settings_fields('alt_settings_group'); ?>
                <?php do_settings_sections('alertio'); ?>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Callback function to render the section description
     */
    public function section_callback() {
        
    }

    /**
     * Callback function to render the Wp Secret Token input, Refresh Token.
     */   
    public function field_callback() {

        $setting_value = get_option('alt_secret_token');
        echo '<div class="alt-secret-token-wrap"><input type="text" name="alt_secret_token" value="' . esc_attr($setting_value) . '" readonly="true" class="alt-wp-secret">
        <button type="button" class="button button-primary alt-generate-token">' . esc_html__('Refresh Token','alt') . '</button></div>';
        echo '<div class="alt-popup" id="alt-popup">
        <div class="alt-loader"></div>
            <p>Are you sure you want to proceed?</p>
            <button type="button" class="alt-confirm-action">' . esc_html__('Yes','alt') . '</button>
            <button type="button" class="alt-cancelled-action">' . esc_html__('No','alt') . '</button>
        </div>';
    }

    /**
     * Callback function to render the dashboard Secret Token input.
     */
    public function dashboard_secret_key() {

        $setting_value = get_option('alt_dashboard_secret_key');
        echo '<input type="text" name="alt_dashboard_secret_key" value="' . esc_attr($setting_value) . '" class="alt-dashboard-secret">';
    }
}
}
// Instantiate your plugin class
$your_plugin_instance = new Alt_Wp_Settings();
