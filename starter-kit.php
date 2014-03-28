<?php
/*
 *	Plugin Name: 	WordPress Starter-Kit
 *	Description:    Plugin starter-kit for rapid plugin development.
 *	Author:         Jer / aut0poietic
 *	Author URI:     http://aut0poietic.us
 *	Version: 		0.6.0
 *	Text Domain: 	starter_kit
 *	Domain Path: 	languages/
 */

define( 'VERSION'		, '0.6.0' ) ;
define( 'PLUGIN_FILE'	,  __FILE__ ) ;
define( 'PLUGIN_DIR'	, plugin_dir_path( __FILE__ ) ) ;
define( 'PLUGIN_URL' 	, plugin_dir_url( __FILE__ ) ) ;

include PLUGIN_DIR . '/kit/silence.php' ;

// We include the drop-in's base class here so the drop-in's themselves don't need to.
require_once( 'kit/drop-in.php' ) ;
require_once( 'kit/template.php' ) ;

/**
 * The central plugin class and bootstrap for the application.
 *
 * While this class is primarily boilerplate code and can be used without alteration,
 * there are a few things you need to edit to get the most out of this kit:
 *  * Add any initialization code that must run *before* the plugins_loaded action in the constructor.
 *  * Edit the return value of the defaults function so that the array contains all your default plugin values.
 *  * Add any plugin activation code to the activate_plugin method -- be sure to leave upgrade_plugin_options
 *  * Add any plugin deactivation code to the deactivate_plugin method.
 *
 * The kit's main class shouldn't need much of your code -- keep your logic in drop-ins so it's ultra portable.
 */
class starter_kit {

	/**
	 * Constructor: Main entry point for your plugin. Runs during the plugins_loaded action.
	 */
	public function __construct( ) {
		Template::set_path( PLUGIN_DIR . '/__inc/templates/' ) ;
	}

	/**
	 * Provides the default settings for the plugin.
	 *
	 * The defaults method is only ever run on plugin activation and is used to populate the default options
	 * for the plugin. When you update the options for your plugin in this method when adding functionality,
	 * the kit will ensure that the user's options are up to date.
	 *
	 * @static
	 * @return array The default preferences and settings for the plugin.
	 */
	private static function defaults( ) {
		return array(

		) ;
	}

	/**
	 * Plugin activation hook
	 *
	 * Add any activation code you need to do here, like building tables and such.
	 * You won't need to worry about your options so long as you updated them using the defaults method.
	 *
	 * @static
	 * @hook register_activation_hook
	 */
	public static function activate_plugin( ) {

	}

	/**
	 * Plugin deactivation hook
	 *
	 * Need to clean up your plugin when it's deactivated?  Do that here.
	 * Remember, this isn't when your plugin is uninstalled, just deactivated
	 * ( so it happens when the plugin is updated too ).
	 *
	 * @static
	 * @hook register_deactivation_hook
	 */
	public static function deactivate_plugin( ){

	}

/*	*****************************************************************************************************************
	         HERE BEGINS THE BOILERPLATE CODE: READ IT, LOVE IT, BUT THERE'S NOT MUCH REASON TO CHANGE IT
	***************************************************************************************************************** */

	/**
	 * Initializes the plug-in as a singleton.
	 *
	 * This method is the entry-point for the plugin, instantiating the plugin class as well as:
	 *   * Initializing the text domain for the plugin's translations
	 *   * Initializing the template engine
	 *   * Loading all functionality classes
	 *
	 * @static
	 * @hook plugins_loaded
	 * @do_action starter_kit_init
	 * @return starter_kit
	 */
	private static $__instance ;
	public static function init( ) {
		if ( ! self::$__instance )
		{
			self::$__instance = new starter_kit ;
			self::$__instance->init_templates( ) ;
			self::$__instance->load_plugin_classes( ) ;
			load_plugin_textdomain( 'starter_kit', false , starter_kit_PLUGIN_DIR . '/languages' ) ;
		}
		return self::$__instance ;
	}

/*	*****************************************************************************************************************
	Plugin Options
	***************************************************************************************************************** */






	/**
	 * Boiler plate wrapper for the activate_plugin method. Calls upgrade_plugin_options to
	 * ensure your plugins's options data are maintained through upgrades.
	 * @static
	 */
	public static function _activate_plugin( )
	{
		self::upgrade_plugin_options( ) ;
		self::activate_plugin( ) ;
	}

/*	*****************************************************************************************************************
	Class Loading
	***************************************************************************************************************** */

	/**
	 * Loads plugin functionality classes appropriate for the current view-state.
	 *
	 * Classes are loaded from one of three folders:
	 *   * /core   - Core functionality that must be loaded.
	 *   * /admin  - Classes that should be loaded for the WordPress Admin/Dashboard only.
	 *   * /client - Classes that are only used during rendering of the client only.
	 *
	 * To instantiate these classes, use the action hook <kbd>starter_kit_init</kbd> in the class file.
	 */
	private function load_plugin_classes( )
	{
		$load_list = array( ) ;
		$load_list = array_merge( $load_list , self::glob_php( starter_kit_PLUGIN_DIR . '/core' ) );

		if( is_admin( ) )
		{
			$load_list = array_merge( $load_list , self::glob_php( starter_kit_PLUGIN_DIR . '/admin' ) );
		}
		else
		{
			$load_list = array_merge( $load_list , self::glob_php( starter_kit_PLUGIN_DIR . '/client' ) );
		}
		foreach ( $load_list as $file )
		{
			include $file ;
		}

		// firing the starter_kit_init action triggers initialization of the classes
		do_action( 'starter_kit_init' , array( 'starter_kit_DropIn' , 'register_class' ) ) ;
	}

	/**
	 * Returns an array of all PHP files in the specified directory path.
	 *
	 * Shamelessly borrowed from the Automattic's Jetpack plug-in
	 * and re-purposed for my nefarious schemes.
	 *
	 * @static
	 * @param string $absolute_path The absolute path of the directory to search.
	 * @return array Array of absolute paths to the PHP files.
	 */
	private static function glob_php( $absolute_path )
	{
		$absolute_path = untrailingslashit( $absolute_path ) ;
		$files = array( ) ;
		if ( ! $dir = @opendir( $absolute_path ) )
		{
			return $files ;
		}

		while ( false !== $file = readdir( $dir ) )
		{
			if ( '.' == substr( $file , 0 , 1 ) || '.php' != substr( $file , -4 ) )
			{
				continue ;
			}

			$file = "$absolute_path/$file" ;

			if ( ! is_file( $file ) )
			{
				continue;
			}

			$files[ ] = $file ;
		}

		closedir( $dir ) ;

		return $files ;
	}
} // End Class

//...and away we go!
add_action( 'plugins_loaded' , array( 'starter_kit', 'init' ) ) ;
register_activation_hook( __FILE__ , array( 'starter_kit' , '_activate_plugin' ) ) ;
register_deactivation_hook( __FILE__ , array( 'starter_kit' , 'deactivate_plugin' ) ) ;


