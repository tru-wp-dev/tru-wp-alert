<?php
/**
 * Plugin Name: Alertio
 * Description: Alertio will provides real-time data for WordPress plugins, themes, and components.
 * Plugin URI:  https://mysenseinc.com/
 * Version:     1.0
 * Author:      Tru Agency
 * Author URI:  https://mysenseinc.com/
 * Text Domain: alt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'ALT_VERSION' ) ) {
	return;
}
define( 'ALT_VERSION', '1.0' );
define( 'ALT_FILE', __FILE__ );
define( 'ALT_PATH', plugin_dir_path( ALT_FILE ) );
define( 'ALT_URL', plugin_dir_url( ALT_FILE ) );
/**
 * Class Alertio
 */
final class Alertio {
	/**
	 * Plugin instance.
	 *
	 * @var Alertio
	 * @access private
	 */
	private static $instance = null;
	/**
	 * Get plugin instance.
	 *
	 * @return Alertio
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {

        register_activation_hook( ALT_FILE, array($this, 'alt_activate' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( ALT_FILE ), array( $this, 'alt_template_settings_page' ) );
		add_action( 'plugins_loaded', array( $this, 'alt_plugins_loaded' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'alt_admin_script' ) );
        add_action( 'wp_ajax_alt_regenerate_token', array( $this, 'alt_regenerate_token' ) );

    }

    /**
     * This function is used to refresh Wp Secret Token.
     */

    public static function alt_regenerate_token(){
        
        check_ajax_referer('alt-nonce-submission');
        $bearer_token =  Alertio::generate_token();
        update_option( 'alt_secret_token', $bearer_token, false );
        $return = array(
            'token'  => $bearer_token,
            'status'       => 200
        );
        wp_send_json($return);
        wp_die();
    }

    /**
     * This function is used to add scripts and styles to the admin side.
     */

    public function alt_admin_script() {

        $current_screen = get_current_screen();
        $screen_name    = isset( $current_screen->base ) ? esc_html( $current_screen->base ) : '';
        if ( $screen_name == 'alertio' || $screen_name == 'toplevel_page_alertio' ) {
            wp_enqueue_script( 'alt-settings-js', ALT_URL . 'assets/js/alt-settings.js', array( 'jquery' ), ALT_VERSION, true );
            wp_enqueue_style( 'alt-settings-css', ALT_URL . 'assets/css/alt-settings.css', array(), ALT_VERSION, 'all' );
            wp_localize_script('alt-settings-js', 'alt_object', 
                array(
                    'alt_ajax_url'          => admin_url( 'admin-ajax.php' ),
                    'alt_nonce_submission'  => wp_create_nonce( 'alt-nonce-submission' )
                ));
        }

    }

    /**
    * This function is used to add plugin settings.
    */
    public function alt_template_settings_page( $links ) {

        $links[] = '<a style="font-weight:bold" href="' . esc_url( get_admin_url( null, 'admin.php?page=alertio' ) ) . '">Plugin Settings</a>';
        return $links;

    }
    
	/**
	 * This function is used to Include settings, api data files.
	 */
	function alt_plugins_loaded() {
		
		require ALT_PATH . '/includes/alt-settings.php';
        require ALT_PATH . '/includes/alt-api-data.php';
        
	}  
   
	/**
	 * This function is Run when activate plugin.
	 */
	public static function alt_activate() {

		update_option( 'alt-v', ALT_VERSION );
		update_option( 'alt-installDate', gmdate( 'Y-m-d h:i:s' ) );
        if ( ! get_option( 'alt_secret_token' ) ) {
            $bearer_token =  Alertio::generate_token();
            update_option( 'alt_secret_token', $bearer_token, false );
        }
    }
    /**
    * Generates token string
    */
    public static function generate_token() {

        $output         = false;
        $encrypt_method = "AES-256-CBC";
        $nonce          = wp_create_nonce(' updates-alert-token ');
        $key            = hash('sha256', time() );
        $iv             = substr(hash('sha256', md5( time() ) ), 0, 16);
        $output = openssl_encrypt($nonce, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;

    }
    
}
function Alertio() {
	return Alertio::get_instance();
}
Alertio();
