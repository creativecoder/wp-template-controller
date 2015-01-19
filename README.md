Template Controller for WordPress
=======================================

This class allows you to abstract the data you need for your theme templates from the  template files, themselves (like `page.php` and `single.php`). Use a controller file to generate the data, and let your template files focus on displaying that data.

Install
-------

Include the class and a controller file within your theme or plugin

```php
// Example:
require_once( get_template_directory() . '/lib/classes/class-template-controller.php' );
require_once( get_template_directory() . '/controller.php' );
```

Usage
-----

Within the controller file, extend the class to create a controller for your theme. Each method within the class should match a body class of a template where you want to display the data. The `common()` method fires for all templates. Don't forget to init the class.

```php
class My_Controller extends Template_Controller {

	// Generates data for all templates
	public function common() {
		$this->add( 'hi', 'I load for every template on the site.' );
	}

	public function page() {
		$this->add( 'yo', 'I load for page.php and custom page templates.' );

		// Get recent posts to display on the page
		$this->add( 'recent_posts', get_posts( array(
			'post_type' => 'post',
			'posts_per_page' => 2,
				)
			)
		);
	}
}

My_Controller::init();
```

Within your template files, call the data you've generated.

```php
// within page.php

// Store the data
$yo = get_tpl_data( 'yo' );

// Echo out the data
tpl_data( 'hi' );

// Use data as you normally would in template files
$recent_posts = tpl_data( 'recent_posts' );
foreach( $recent_posts as $post ) {
	echo $post->post_title;
}
```

Alternatively, you can call a global to get all of the data available for that template.

```php
// within page.php

global $template_data;
extract( $template_data, EXTR_SKIP );

echo $yo;

foreach( $recent_posts as $post ) {
	echo $post->post_title;
}
```

Multiple Controllers
--------------------

You can create and init as many child classes as you would like. All will follow the same pattern, loading any methods that match any body class on a template. All store data statically in the parent class so that it can be easily called with the tpl_data functions.

Thanks to @DesignPlug for the idea: https://github.com/DesignPlug/wxp-dom-router
