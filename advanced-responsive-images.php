<?php
/*
 Plugin Name: Advanced Responsive Images
 Version: 3.1.2
 Plugin URI: https://github.com/asadowski10/advanced-responsive-images
 Description: WordPress plugin to implement custom HTML markup for responsive images
 Author: Alexandre Sadowski
 Author URI: https://www.alexandresadowski.com/
 Domain Path: languages
 Text Domain: advanced-responsive-images

 ----
 Copyright 2017 Alexandre Sadowski (hello@alexandresadowski.com)
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define( 'ARI_VERSION', '3.1.2' );
define( 'ARI_MIN_PHP_VERSION', '5.4' );
define( 'ARI_VIEWS_FOLDER_NAME', 'ari' );

if ( ! defined( 'ARI_JSON_DIR' ) ) {
	define( 'ARI_JSON_DIR', get_template_directory() . '/src/conf-img/' );
}

if ( ! defined( 'ARI_MODE' ) ) {
	define( 'ARI_MODE', 'picture_lazyload' );
}

if ( ! defined( 'ARI_PIXEL' ) ) {
	define( 'ARI_PIXEL', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' );
}

if ( ! defined( 'ARI_CONTEXT' ) ) {
	define( 'ARI_CONTEXT', 'back' );
}

// Plugin URL and PATH
define( 'ARI_URL', plugin_dir_url( __FILE__ ) );
define( 'ARI_DIR', plugin_dir_path( __FILE__ ) );

// Check PHP min version
if ( version_compare( PHP_VERSION, ARI_MIN_PHP_VERSION, '<' ) ) {
	require_once( ARI_DIR . 'compat.php' );

	// possibly display a notice, trigger error
	add_action( 'admin_init', array( 'ARI\Compatibility', 'admin_init' ) );

	// stop execution of this file
	return;
}

/**
 * Autoload all the things \o/
 */
require_once ARI_DIR . 'autoload.php';

add_action( 'plugins_loaded', 'init_advanced_responsive_images_plugin' );
/**
 * Init the plugin
 */
function init_advanced_responsive_images_plugin() {
	require_once ARI_DIR . 'functions/utils.php';
	// Client
	\ARI\Main::get_instance();
	\ARI\Image_Sizes::get_instance();
}
