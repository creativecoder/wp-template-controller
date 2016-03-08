<?php
/*
Plugin Name: Template Controller
Plugin URI:  https://github.com/creativecoder/wp-template-controller
Description: Separate data generation from presentation in your WordPress templates
Version:     0.1.2
Author:      Grant Kinney
Author URI:  https://github.com/creativecoder
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 'template-controller'
*/

if ( ! class_exists( 'Template_Controller' ) ) {
	/**
	 * Parent class for passing data into template files
	 *
	 * All properties in this class are declared statically so that all data from child
	 * classes can be passed back to this parent class and called directly from here.
	 * (This takes advantage of a PHP quirk that that within child classes, `self` refers
	 * to the parent class)
	 *
	 * @package     WordPress
	 * @subpackage  Template_Controller
	 * @author      Grant Kinney
	 * @license     http://mit-license.org/ MIT
	 */
	class Template_Controller {

		/**
		 * An array of instances used for singleton pattern that stores an instance for this
		 * parent class and separate instances for each child class
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * An array of body class for the current template
		 * @var array
		 */
		protected static $classes = array();

		/**
		 * A key value store for data that is passed into templates
		 * @var array
		 */
		protected static $data = array();

		protected static $data_replace = array();

		/**
		 * Initialize class
		 * @return void
		 */
		public static function init() {
			self::get_instance();
		}

		/**
		 * Singleton pattern: instantiate one and only one instance of this class, and one and
		 * only one instance of each child class that extends it
		 *
		 * @return object instance of class being instantiated
		 */
		final public static function get_instance() {
			// Note, need PHP 5.3 or greater to use `get_called_class()`
			$class = get_called_class();
			if ( ! isset( self::$instances[$class] ) ) {
				self::$instances[$class] = new $class;
			}
			return self::$instances[$class];
		}

		/**
		 * Hooks for setting up methods within class
		 */
		public function __construct() {
			// Load template data before including template files
			add_action( 'template_redirect', array( $this, 'load' ) );
		}

		/**
		 * Get a value from the template data by name
		 *
		 * @param  string $name Name of data stored
		 * @return mixed        Value of data, if it exists, otherwise false
		 */
		public function get( $name ) {
			$data = false;
			if ( isset(self::$data_replace[$name]) ) {
				$data = self::$data_replace[$name];
			} else if ( isset(self::$data[$name]) ) {
				$data = self::$data[$name];
			}
			return $data;
		}

		/**
		 * Load template data
		 *
		 * Loops through each body class for the current template, checks for a method of
		 * the same name as the class, and calls that method if it exists
		 *
		 * @return void
		 */
		public function load() {
			/**
			 * Global variable to store template data.
			 *
			 * Place the following at the top of your template file as an alternative to calling
			 * `tpl_data()` or `get_tpl_data()` functions for each individual data key
			 * `global $template_data;`
			 * `extract( $template_data, EXTR_SKIP );`
			 *
			 * @global array template_data
			 */
			global $template_data;
			// Add `common` to class array so that it loads for every template
			self::$classes = get_body_class( 'common' );
			foreach( self::$classes as $class ) {
				$class = str_replace( '-', '_', $class );
				if ( method_exists( $this, $class ) ) {
					call_user_func( array( $this, $class ) );
				}
			}
			// Push template data out to global variable
			$template_data = array_merge( self::$data, self::$data_replace );
		}

		/**
		 * Add data by name for use in templates
		 *
		 * @param string $name Name of data stored
		 * @param mixed  $data Value of data
		 */
		protected function add( $name, $data ) {
			self::$data[$name] = $data;
		}

		protected function replace( $name, $data ) {
			self::$data_replace[$name] = $data;
		}

	} // end class
} // endif class_exists

/**
 * Return a template data value from the specified name
 *
 * @param  string $name Name of data key to retrieve
 * @return mixed        Data value for use in template
 */
function get_tpl_data( $name ) {
	return Template_Controller::get_instance()->get($name);
}

/**
 * Echo a template data value from the specified name
 *
 * @param  string $name Name of data key to retrieve
 * @return void
 */
function tpl_data( $name ) {
	echo esc_html(Template_Controller::get_instance()->get($name));
}

add_action( 'admin_init', function () {
	if ( ! class_exists('WP_GitHub_Updater') ) {
		include_once( 'WordPress-GitHub-Plugin-Updater/updater.php' );
	}

	if ( ! defined( 'WP_GITHUB_FORCE_UPDATE' ) ) {
		define( 'WP_GITHUB_FORCE_UPDATE', true );
	}

	$config = array(
		'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
		'proper_folder_name' => 'wp-template-controller', // this is the name of the folder your plugin lives in
		'api_url' => 'https://api.github.com/repos/creativecoder/wp-template-controller', // the GitHub API url of your GitHub repo
		'raw_url' => 'https://raw.github.com/creativecoder/wp-template-controller/master', // the GitHub raw url of your GitHub repo
		'github_url' => 'https://github.com/creativecoder/wp-template-controller', // the GitHub url of your GitHub repo
		'zip_url' => 'https://github.com/creativecoder/wp-template-controller/zipball/master', // the zip url of the GitHub repo
		'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
		'requires' => '3.0', // which version of WordPress does your plugin require?
		'tested' => '4.4', // which version of WordPress is your plugin tested up to?
		'readme' => 'README.md', // which file to use as the readme for the version number
		'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	);

	new WP_GitHub_Updater( $config );
});
