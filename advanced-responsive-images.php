<?php
/*
 Plugin Name: Advanced Responsive Images
 Version: 1.0.0
 Plugin URI: http://www.beapi.fr
 Description: Your plugin description
 Author: BE API Technical team
 Author URI: http://www.beapi.fr
 Domain Path: languages
 Text Domain: advanced-responsive-images

 ----

 Copyright 2016 BE API Technical team (human@beapi.fr)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define( 'ARI_VERSION', '1.0.0' );
define( 'ARI_MIN_PHP_VERSION', '5.4' );
define( 'ARI_VIEWS_FOLDER_NAME', 'ari' );


define( 'ARI_JSON_DIR', get_template_directory() . '/assets/conf-img/' );
if ( ! defined( 'ARI_MODE' ) ) {
	define( 'ARI_MODE', 'srcset' );
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
	// Client
	\ARI\Main::get_instance();
}
