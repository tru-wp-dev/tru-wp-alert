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

register_activation_hook( ALT_FILE, array( 'Tru_Wp_Alert', 'alt_activate' ) );
register_deactivation_hook( ALT_FILE, array( 'Tru_Wp_Alert', 'alt_deactivate' ) );
/**
 * Class Tru_Wp_Alert
 */
final class Tru_Wp_Alert {
	/**
	 * Plugin instance.
	 *
	 * @var Tru_Wp_Alert
	 * @access private
	 */
	private static $instance = null;
	/**
	 * Get plugin instance.
	 *
	 * @return Tru_Wp_Alert
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
        add_filter( 'plugin_action_links_' . plugin_basename( ALT_FILE ), array( $this, 'alt_template_settings_page' ) );
		// $this->alt_include_file();
		add_action( 'plugins_loaded', array( $this, 'alt_plugins_loaded' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'alt_admin_script' ) );
        add_action( 'wp_ajax_alt_regenerate_token', array( $this, 'alt_regenerate_token' ) );
    }
    public function alt_regenerate_token(){
        check_ajax_referer('tra-nonce-submission');
        $output         = false;
            $encrypt_method = "AES-256-CBC";
            $nonce          = wp_create_nonce(' updates-alert-token ');
            $key            = hash('sha256', time() );
            $iv             = substr(hash('sha256', md5( time() ) ), 0, 16);
           
            $output = openssl_encrypt($nonce, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
            update_option( 'alt_secret_token', $output, false );
            $return = array(
                'token'  => $output,
                'status'       => 200
            );
            wp_send_json($return);
        
        wp_die();

    }
    public function alt_admin_script() {
        $current_screen = get_current_screen();
        $screen_name    = isset( $current_screen->base ) ? esc_html( $current_screen->base ) : '';
        if ( $screen_name == 'tru-wp-alert' || $screen_name == 'toplevel_page_tru-wp-alert' ) {
            wp_enqueue_script( 'alt-settings-js', ALT_URL . 'assets/js/alt-settings.js', array( 'jquery' ), ALT_VERSION, true );
            wp_enqueue_style( 'alt-settings-css', ALT_URL . 'assets/css/alt-settings.css', array(), ALT_VERSION, 'all' );
            wp_localize_script('alt-settings-js', 'alt_object', 
                array(
                    'alt_ajax_url'          => admin_url( 'admin-ajax.php' ),
                    'alt_nonce_submission'  => wp_create_nonce( 'tra-nonce-submission' )
                ));
           
        }
        
    }
    public function alt_template_settings_page( $links ) {
        $links[] = '<a style="font-weight:bold" href="' . esc_url( get_admin_url( null, 'admin.php?page=tru-wp-alert' ) ) . '">Plugin Settings</a>';
        return $links;
    }
    
	/**
	 * Code you want to run when all other plugins loaded.
	 */
	function alt_plugins_loaded() {
		// Require the settings file
		require ALT_PATH . '/includes/alt-settings.php';
        require ALT_PATH . '/includes/alt-api-data.php';
        
	}   // end of ctla_loaded()
   
	/**
	 * Run when activate plugin.
	 */
	public static function alt_activate() {
		update_option( 'alt-v', ALT_VERSION );
		update_option( 'alt-installDate', gmdate( 'Y-m-d h:i:s' ) );
        if ( ! get_option( 'alt_secret_token' ) ) {
            $bearer_token =  Tru_Wp_Alert::alt_generate_secret_token();
            update_option( 'alt_secret_token', $bearer_token, false );
        }
	}
    
        /**
         * Generates token string
         */
        public static function alt_generate_secret_token() {
            $output         = false;
            $encrypt_method = "AES-256-CBC";
            $nonce          = wp_create_nonce(' updates-alert-token ');
            $key            = hash('sha256', time() );
            $iv             = substr(hash('sha256', md5( time() ) ), 0, 16);
           
            $output = openssl_encrypt($nonce, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);

            return $output;
        }
	/**
	 * Run when deactivate plugin.
	 */
	public static function alt_deactivate() {
	}
}
function Tru_Wp_Alert() {
	return Tru_Wp_Alert::get_instance();
}
Tru_Wp_Alert();
