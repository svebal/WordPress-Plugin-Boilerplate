<?php 
! defined( 'ABSPATH' ) AND exit;

if( ! class_exists( 'PluginName' ) ){

	class PluginName {
	
		
		public static $plugin_obj	= false;
		private static $db          = false;
		
		/**
		 * Constructor
		 *
		 * Initializes the plugin by setting localization, filters, and administration functions.
		 * 
		 *
		 * @param array $plugin_data plugin data like Autor, Version, Name ...
		 */
		function __construct( $plugin_basename ) {
            
			//Catch some useful information about the pluign in the $plugin_obj
			self::$plugin_obj->class_name 	= __CLASS__;
			self::$plugin_obj->name         = self::set_plugin_name();
			self::$plugin_obj->base         = $plugin_basename;
			self::$plugin_obj->path         = str_replace( '/inc', '', plugin_dir_path(__FILE__) );
			self::$plugin_obj->include_path = plugin_dir_path(__FILE__);
			self::$plugin_obj->url          = str_replace( '/inc', '', plugin_dir_url(__FILE__) );
			self::$plugin_obj->the_plugin   = self::get_plugin_data();
			
			load_plugin_textdomain( self::$plugin_obj->class_name, false, self::$plugin_obj->name  . '/lang/' );
            
			if(is_admin()){
				
				// add row meta links
				add_filter( 'plugin_row_meta',  array( &$this, 'plugin_row_meta_link' ), 10, 2 );
                add_filter( 'extra_plugin_headers',  array( &$this, 'add_extra_plugin_headers' ) );
                
                register_activation_hook( __FILE__, array( $this, 'activate' ) );
                register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
                register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );
                
				if( defined('WP_UNINSTALL_PLUGIN') || $_GET['action'] == 'unistall' && $_GET['plugin'] == self::$plugin_obj->name ){

					// Include the Database class
					require_once( self::$plugin_obj->include_path  . "/unistall.class.php" );
					
					new PluginName_unistall_class();
                    
				}else{
                    
                    
				
					// Include the Database class
					require_once( self::$plugin_obj->include_path  . "/db.class.php" );

					/*
					 * Init the Database class
					 *
					 * TODO: 
					 * Go to the db.class.php and replace PluginName with your new plugin name,
					 * like this class MyAwsome_Plugin extends MyAwsome_Plugin_db_class 
					*/
					self::$db = new PluginName_db_class();
				
                    // Update and setup Database
					self::update_db();
					
					// Register admin styles and scripts
					add_action( 'admin_print_styles', array( &$this, 'register_admin_styles' ) );
					add_action( 'admin_enqueue_scripts', array( &$this, 'register_admin_scripts' ) );

	            	
					// Add Uninstall action link
					add_action( 'plugin_action_links_' . self::$plugin_obj->base, array( &$this, 'uninstall_action_link' ) );
				}
				
			}else{
			 
				// Register site styles and scripts
				add_action( 'wp_print_styles', array( &$this, 'register_plugin_styles' ) );
				add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_scripts' ) );
				
			}
			
			
		    /*
		     * TODO:
		     * Define the custom functionality for your plugin. The first parameter of the
		     * add_action/add_filter calls are the hooks into which your code should fire.
		     *
		     * The second parameter is the function name located within this class. See the stubs
		     * later in the file.
		     *
		     * For more information: 
		     * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		     */
		    add_action( 'TODO', array( $this, 'action_method_name' ) );
		    add_filter( 'TODO', array( $this, 'filter_method_name' ) );
	
		} // end constructor
		
		
		
		/**
		 * Registers and enqueues admin-specific styles.
		 */
		public function register_admin_styles() {
			wp_register_style( self::$plugin_obj->name . '-admin-styles', plugins_url( self::$plugin_obj->name . '/css/admin.css' ) );
			wp_enqueue_style( self::$plugin_obj->name . '-admin-styles' );
		
		} // end register_admin_styles
	
		/**
		 * Registers and enqueues admin-specific JavaScript.
		 */	
		public function register_admin_scripts() {
			wp_register_script( self::$plugin_obj->name . '-admin-script', plugins_url( self::$plugin_obj->name . '/js/admin.js' ) );
			wp_enqueue_script( self::$plugin_obj->name . '-admin-script' );
		
		} // end register_admin_scripts
		
		/**
		 * Registers and enqueues plugin-specific styles.
		 */
		public function register_plugin_styles() {
			wp_register_style( self::$plugin_obj->name . '-plugin-styles', plugins_url( self::$plugin_obj->name . '/css/display.css' ) );
			wp_enqueue_style( self::$plugin_obj->name . '-plugin-styles' );
		
		} // end register_plugin_styles
		
		/**
		 * Registers and enqueues plugin-specific scripts.
		 */
		public function register_plugin_scripts() {
			wp_register_script( self::$plugin_obj->name . '-plugin-script', plugins_url( self::$plugin_obj->name . '/js/display.js' ) );
			wp_enqueue_script( self::$plugin_obj->name . '-plugin-script' );
		
		}
        
        
        /**
         * Fired when the plugin is activated.
         *
         * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
         */
        public function activate( $network_wide ) {
            // TODO define activation functionality here
        } // end activate

        /**
         * Fired when the plugin is deactivated.
         *
         * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
         */
        public function deactivate( $network_wide ) {
            // TODO define deactivation functionality here		
        } // end deactivate

        /**
         * Fired when the plugin is uninstalled.
         *
         * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
         */
        public function uninstall( $network_wide ) {
            // TODO define uninstall functionality here		
        } // end uninstall

        
		private function set_plugin_name(){
			$plugin_basename = explode( '/', plugin_basename(__FILE__) );
			return $plugin_basename[0];
		}
		
		
		private function get_plugin_data(){
			
			$filePath = self::$plugin_obj->path . self::$plugin_obj->class_name . '.php';
			
			if( is_readable( $filePath ) ){
	      		$fp = fopen( $filePath, 'r');
			
				if(filesize($filePath) > 0){
	      			$content = fread( $fp,filesize($filePath) );
                    preg_match( '/Version:\s(.*?)\s/', $content, $version );
                    preg_match( '/Plugin Name:\s(.*?)\R/', $content, $plugin_name );

                    $plugin_data->name      = $plugin_name[1];
                    $plugin_data->version   = $version[1];
                    
					return $plugin_data;
				}else{
					fclose ($fp);
					return false;
				}
			}
		}
        
		
		private function update_db(){
            
			if( get_option( self::$plugin_obj->class_name  . '_version' ) != self::$plugin_obj->the_plugin->version ){			
                update_option( self::$plugin_obj->class_name  . '_version', self::$plugin_obj->the_plugin->version );
				self::$db->create_db_tables();
			}
			
		}
        
       
		
		/**
		  * Add unistall action link for the Plugin!
		  *
		  * @param 	array	$action_links 	the original links by Wordpress
		  * @return arry 	$action_links 	the filtered links
		  *   
		  */
		public function uninstall_action_link( $action_links ){ 
			
		 $action_links['unistall'] = '<span class="delete"><a href="'. admin_url() .'plugins.php?action=unistall&plugin=' . self::$plugin_obj->name  . '" title="' . __( 'Unistall this plugin', self::$plugin_obj->class_name ) . '" class="delete">' . __( 'Unistall', self::$plugin_obj->class_name )  . '</a></span>';
		 
		 return $action_links;
		
		}
				
		
		
		/**
		* Metalinks
		*
		* @param   array   $data  existing links
		* @param   string  $page  current page
		* @return  array   $data  modified links
		*/
		public function plugin_row_meta_link($data, $page){

            self::$plugin_obj->plugin_data = get_plugin_data( self::$plugin_obj->path . self::$plugin_obj->class_name . '.php' );
            
            
			if ( $page != self::$plugin_obj->base ) {
				return $data;
			}
            
            $meta_data = array();
            
            if( self::$plugin_obj->plugin_data['Author Google Profile ID'] ){
               $meta_data[0] =  '<a href="https://plus.google.com/u/0/' . self::$plugin_obj->plugin_data['Author Google Profile ID'] . '/about" target="_blank">' . __( 'Google+', self::$plugin_obj->class_name ) . '</a>';                   
            }
            
            if( self::$plugin_obj->plugin_data['Author twitter'] ){
               $meta_data[1] =  '<a href="' . self::$plugin_obj->plugin_data['Author twitter'] . '" target="_blank">' . __( 'Twitter', self::$plugin_obj->class_name ) . '</a>';                   
            }
            
            if( self::$plugin_obj->plugin_data['Company Name'] ){
               if( self::$plugin_obj->plugin_data['Company URI'] ){
                $meta_data[2] =  __( 'Company', self::$plugin_obj->class_name ) . ': <a href="' . self::$plugin_obj->plugin_data['Company URI'] . '" target="_blank">' . self::$plugin_obj->plugin_data['Company Name'] . '</a>';  
               }else{
                $meta_data[2] =  __( 'Company', self::$plugin_obj->class_name ) . ': ' . self::$plugin_obj->plugin_data['Company Name']; 
               }
            }
            
			return array_merge( $data, $meta_data );
			
		}
			
        
        public function add_extra_plugin_headers(){
            $extra_headers = array( 
                                'Author Google Profile ID',
                                'Author twitter',
                                'Company Name',
                                'Company URI'
                                );
            

            
            return $extra_headers;
        }
        
				
		/*--------------------------------------------*
		 * Core Functions
		 *---------------------------------------------*/
		
		/**
	 	 * Note:  Actions are points in the execution of a page or process
		 *        lifecycle that WordPress fires.
		 *
		 *		  WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
		 *		  Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
		 *
		 */
		public function action_method_name() {
	    	// TODO define your action method here
		} // end action_method_name
		
		/**
		 * Note:  Filters are points of execution in which WordPress modifies data
		 *        before saving it or sending it to the browser.
		 *
		 *		  WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
		 *		  Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
		 *
		 */
		public function filter_method_name() {
		    // TODO define your filter method here
		} // end filter_method_name
	  
	} // end class

}
?>