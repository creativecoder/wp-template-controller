<?php
/**
 * Controller for template data
 * - Each method in the class that matches a template name will be loaded for that template
 * - Ex: `single()` will be loaded for single.php. `common()` is called for all templates
 * - Within each method, use `$this->add( 'name', $data )` for each variable you'd like to pass to a template
 * - Use `tpl_var('name')` or `get_tpl_var('name')` to call the data from within each template file
 *
 * @package    WordPress
 * @subpackage Template_Controller
 * @uses       Template_Controller  Parent class for controller
 */
class My_Controller extends Template_Controller {

	public function common() {
		$this->add( 'hi', 'I load for every template on the site.' );
	}

	public function page() {
		$this->add( 'yo', 'I load for page.php and custom page templates.' );
	}

	public function single() {
		$this->add( 'whazup', 'I load for single.php, for all post types (any template that starts with "single-").' );
	}

	public function single_post() {
		$this->add( 'dude', 'I load only for built in single post templates (aka single-post.php)' );
	}

}

// Initialize controller
My_Controller::init();
