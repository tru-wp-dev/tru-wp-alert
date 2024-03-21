<?php
/**
 * Plugin Name: Tru Wp Alert
 * Plugin URI:
 * Description: Tru Wp Alert will provides real-time data for WordPress plugins, themes, and components.
 * Author: Munish Thakur
 * Version: 1.0
 * Author URI:
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Tru_Wp_Alert' ) )
{

    class Tru_Wp_Alert
    {
        
        /**
         * Tru_Wp_Alert Constructor.
         * 
         */
        public function __construct() {
            $this->init();
        }

        /**
         * Hook ready to init the REST API as needed.
         */
        public function init() {
            register_activation_hook( __FILE__, array( $this, 'create_bearer_token' ) );
            add_action( 'admin_menu', array( $this,'wpdata_setting_menu_page_hook' ) );
            add_action( 'admin_notices', array( $this, 'show_token_notice' ) );
            add_action( 'rest_api_init', array( $this, 'register_data_rest_routes' ), 10 );
            add_action( 'admin_footer', array( $this,  'script' ) );

            add_action( 'admin_init',  array( $this, 'form_submit' ) );

        }

        /**
         * Register a setting menu page.
         */
        function wpdata_setting_menu_page_hook(){
            add_menu_page( 
                __( 'Tru WP Alert', 'tru-wp-alert' ),
                'Tru WP Alert',
                'manage_options',
                'tru-wp-alert',
                array( $this, 'wpdata_setting_menu_page' ),
                'dashicons-admin-page'
            );
            
        }
        /**
         * Display a setting menu page
         */
        function wpdata_setting_menu_page() {
            if ( current_user_can( 'manage_options' ) ) {
                include(plugin_dir_path( __FILE__ ) .'includes/setting.php');	
            }
        }

        /**
         * One time creation 
         */
        public function create_bearer_token() {
            if ( ! get_option( 'updates_bearer_token' ) ) {
                $bearer_token =  $this->generate_token();
                update_option( 'updates_bearer_token', $bearer_token, false );
                set_transient( 'show-token-notice', true, 5 );
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

        /**
         * Show Token Admin Notice on Activation.
         *
         */
        function show_token_notice() {
            /* Check transient, if available display notice */
            if( get_transient( 'show-token-notice' ) ){
                ?>
                <div class="updated notice is-dismissible">
                    <p><?php _e( sprintf(
                    'Click <a href="%s">here</a> to get access token',
                    menu_page_url('tru-wp-alert', false),
                    ), 'tru-wp-alert' );  ?></p>
                </div>
                <?php
                /* Delete transient, only display this notice once. */
                delete_transient( 'show-token-notice' );
            }
        }

        /**
         * Register Rest Route 
         * callback 
         */
        public function register_data_rest_routes() {
            register_rest_route( 'wpdata/v1', 'alert', array(
                'methods'   => WP_REST_Server::READABLE,
                'callback'  => array( $this, 'wpdata_api_endpoint' ),
                'permission_callback' => '__return_true',
            ) );
        }

        /**
         * Endpoint 
         * @return WP_REST_Response
         */
        public function wpdata_api_endpoint( $request ) {
            $token = get_option( 'updates_bearer_token' );
            $headers = $request->get_headers();

            if( !isset( $headers['authorization'] ) ) {
                return new WP_Error( '401', esc_html__( 'Not Authorized', 'tru-wp-alert' ), ['status' => 401] );
            }

            // Check Token
            $auth_token = trim(str_replace('Bearer', '', $headers['authorization'][0]));
            if ( $token != $auth_token ) {
                return new WP_Error( '401', esc_html__( 'Not Authorized', 'tru-wp-alert' ), ['status' => 401] );
            }

            return new WP_REST_Response( array('data' => $this->get_data() ) );

        }

        /**
         * Counts | Plugins | Themes 
         * @return array 
         */
        public function get_data() {
            if( get_transient( 'get-wp-data' ) ){
                return get_transient( 'get-wp-data' );
            }
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins    = get_plugins();
            $all_themes     = wp_get_themes();
            
            $plugins = $themes = [];
            
            if( count( $all_plugins ) > 0 ) {
                $i = 0;
                foreach( $all_plugins as $k => $v ) {
                    $plugins[$i]['name']       = $v['Name'];
                    $plugins[$i]['author']     = $v['Author'];
                    $plugins[$i]['uri']        = $v['PluginURI'];
                    $plugins[$i]['version']    = $v['Version'];
                    $plugins[$i]['is_active']  = is_plugin_active($k);
                    $i++;
                }
            }

            if( count( $all_themes ) > 0 ) {
                $i = 0;
                foreach( $all_themes as $k => $v ) {
                    $my_theme = wp_get_theme( $k );
                    $themes[$i]['name']       = $my_theme->get( 'Name' );
                    $themes[$i]['author']     = $my_theme->get( 'Author' );
                    $themes[$i]['version']    = $my_theme->get( 'Version' );
                    $themes[$i]['is_active']  = (get_template()==$k) ? true : false;
                    $i++;
                }
            }
            global $wp_version;
            $data['counts']               = $this->counts();
            $data['wordpress']['version'] =  esc_html( $wp_version ); 
            $data['plugins']              = $plugins;
            $data['themes']               = $themes;
            $data['components']           = $this->components();
            
            set_transient( 'get-wp-data', $data, 300 );
            return $data;
        }
    
        /**
         * Components
         * Theme components
         * Name | Slug | Version
         * 
         * @return array 
         */
        public function components() {

            if ( ! class_exists( 'FLBuilder' ) ) {
                return array();
            }

            if( get_transient( 'get-component-data' ) ){
                return get_transient( 'get-component-data' );
            }
            global $fp_loaded_components;
            $result = array(); $i = 0;
            if( $fp_loaded_components ){
                foreach ( $fp_loaded_components as $key => $custom_component_file ) {
                    $component_name = str_replace( '.php', '', basename( $custom_component_file ) );
                    $module_class   = 'fp\components\\' . $component_name;
                    if ( class_exists( $module_class ) ) {
                        $module = new $module_class( true );
                        $result[$i]['name']    = isset( $module->component_name ) ? $module->component_name : null;
                        $result[$i]['slug']    = isset( $module->component ) ? $module->component : null;
                        $result[$i]['version'] = isset( $module->version ) ? $module->version : null;
                        $i++;
                    }
                }
            }
            set_transient( 'get-component-data', $result, 300 );
            return $result;
        }

        /**
         * Counts
         * Plugins | Themes | WordPress 
         * 
         * @return array 
         */
        public function counts() {
            $counts = array(
                'plugins'      => count(get_plugins()),
                'themes'       => count(get_themes()),
                'components'   => count($this->components()),
            );
            return $counts;
        }

        /**
         * Adds custom script.
         */
        public function script() {
            $encoded_token = base64_encode( get_option( 'updates_bearer_token' ) );
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('#copy-token').click(function() {
                    var textToCopy = '<?php echo $encoded_token; ?>';
                    var tempTextarea = jQuery('<textarea>');
                    jQuery('body').append(tempTextarea);
                    tempTextarea.val(atob(textToCopy)).select();
                    document.execCommand('copy');
                    tempTextarea.remove();

                    jQuery(".copied").show();
                    setTimeout(function() { jQuery(".copied").hide(); }, 1500);
                });
                });
            </script>
           <?php
        }

        public function form_submit() {

            if( isset( $_POST['submit'] ) && $_POST['submit'] == 'regenerate_token' ) {
            
                $bearer_token = Tru_Wp_Alert::generate_token();
                if( update_option( 'updates_bearer_token', $bearer_token, false ) ){
                    wp_redirect( menu_page_url('tru-wp-alert', false) . '&msg=1' );
                } else {
                    wp_redirect( menu_page_url('tru-wp-alert', false) . '&msg=2' );
                }  
                exit;
        
            }

            if( isset( $_GET['msg'] ) && $_GET['msg'] == 1 ) {
                ?>
                <div class="updated notice is-dismissible">
                        <p><?php _e( 'Token Regenerated.', 'tru-wp-alert' ); ?></p>
                    </div>
                <?php
            }
            if( isset( $_GET['msg'] ) && $_GET['msg'] == 2 ) {
                ?>
                    <div class="notice error is-dismissible" >
                        <p><?php _e( 'Something went wrong. please try again.', 'tru-wp-alert' ); ?></p>
                    </div>
                <?php
            }

        }
        
    }
    new Tru_Wp_Alert;

}
