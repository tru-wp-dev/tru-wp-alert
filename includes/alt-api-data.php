<?php

/**
* This file helps to make an API and gives back data about plugins, themes, and components in a JSON format.
* 
* includes/alt-apis-data
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('Alt_Apis')){
class Alt_Apis {
    public function __construct() {
        
        add_action( 'rest_api_init', array( $this, 'alt_connection_api' ), 10 );
      
    }
    public function alt_connection_api(){
       
        register_rest_route( 'alertio/v1', 'wpdata', array(
            'methods'  =>'POST',
            'callback' => array($this,'check_connection_status'),
            'permission_callback' => '__return_true',
        ) );
    }
   
    // Callback function for checking connection status
    function check_connection_status( $request ) {
        
        $headers = $request->get_headers();
        // Check if the Authorization header is set
        if ( isset(  $headers['authorization'] ) && ! empty(  $headers['authorization'] ) ) {
            // Extract the token from the Authorization header
            $bearer_token = trim(str_replace('Bearer', '', $headers['authorization'][0]));
            if ( $bearer_token != get_option('alt_secret_token') ) {

                return new WP_Error( 'wrong_bearer_token', esc_html__('Bearer token is wrong in the Authorization header.','alt'), array( 'status' => 401 ) );
               
            }else{
                
                $dashboard_secret_token = $request->get_header('dashboard-secret-token');
                if( $dashboard_secret_token == get_option('alt_dashboard_secret_key') ){

                    return new WP_REST_Response( array('data' => $this->alt_get_data() ) );
                    
                }else{
                   
                   return new WP_Error( 'invalid_secret_key', esc_html__( 'Invalid Secret Key', 'tru-wp-alert' ), ['status' => 401] );
                    
                }

            }
        } else {
            return new WP_Error( '401', esc_html__( 'Bearer token is missing in the Authorization header.', 'tru-wp-alert' ), ['status' => 401] );
        }
    }
        /**
         * Counts | Plugins | Themes 
         * @return array 
         */
        public function alt_get_data() {
            
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins    = get_plugins();
            $all_themes     = wp_get_themes();
            
            $plugins = $themes = [];
            
            if( count( $all_plugins ) > 0 ) {
                $i = 0;
                foreach( $all_plugins as $k => $v ) {
                    
                    $plugins[$i]['name']       = isset( $v['Name'] ) ? sanitize_text_field($v['Name']) : '';
                    $plugins[$i]['author']     = isset( $v['Author'] ) ? sanitize_text_field( $v['Author'] ) : '';
                    $plugins[$i]['uri']        = isset( $v['PluginURI'] ) ? filter_var( $v['PluginURI'],FILTER_SANITIZE_URL ) : '';
                    $plugins[$i]['version']    = isset( $v['Version'] ) ? sanitize_text_field( $v['Version'] ) : '';
                    $plugins[$i]['is_active']  = is_plugin_active($k) ? true : false;
                    $i++;
                }
            }
            
            if( count( $all_themes ) > 0 ) {
                $i = 0;
                foreach( $all_themes as $k => $v ) {
                    $my_theme = wp_get_theme( $k );
                    $themes[$i]['name']       = isset($my_theme) && method_exists($my_theme, 'get') ? sanitize_text_field( $my_theme->get('Name') ) : '';
                    $themes[$i]['author']     = isset($my_theme) && method_exists($my_theme, 'get') ? sanitize_text_field( $my_theme->get('Author') ) : '';
                    $themes[$i]['version']    = isset($my_theme) && method_exists($my_theme, 'get') ? sanitize_text_field( $my_theme->get('Version') ) : '';
                    $themes[$i]['is_active']  = (get_template() == $k) ? true : false;
                    $i++;
                }
            }
            global $wp_version;
            $data['counts']               = $this->counts();
            $data['wordpress']['version'] = $wp_version; 
            $data['plugins']              = $plugins;
            $data['themes']               = $themes;
            $data['components']           = $this->alt_components();
            return $data;
        }
        /**
         * Components
         * Theme components
         * Name | Slug | Version
         * 
         * @return array 
         */
        public function alt_components() {

            if ( ! class_exists( 'FLBuilder' ) ) {
                return array();
            }

            global $fp_loaded_components;
            $result = array(); $i = 0;
            if( $fp_loaded_components ){
                foreach ( $fp_loaded_components as $key => $custom_component_file ) {
                    $component_name = str_replace( '.php', '', basename( $custom_component_file ) );
                    $module_class   = 'fp\components\\' . $component_name;
                    if ( class_exists( $module_class ) ) {
                        $module = new $module_class( true );
                        $result[$i]['name']    = isset( $module->component_name ) ? sanitize_text_field( $module->component_name ) : null;
                        $result[$i]['slug']    = isset( $module->component ) ? sanitize_text_field( $module->component ) : null;
                        $result[$i]['version'] = isset( $module->version ) ? sanitize_text_field( $module->version ) : null;
                        $i++;
                    }
                }
            }
            
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
                'components'   => count($this->alt_components()),
            );
            return $counts;
        }
}
}
// Instantiate your plugin class
new Alt_Apis();
