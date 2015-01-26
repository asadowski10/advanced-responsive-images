<?php

/*
  Plugin Name: Advanced Responsive Images
  Description: Implement responsive images in WordPress using SRCSET
  Author: Alexandre Sadowski
  Author URI: https://twitter.com/alex_sadowski
  Version: 1.0
  Text Domain: ari
  Domain Path: /languages/
  Network: false

  ----

  Copyright 2015 Alexandre Sadowski (hello@alexandresadowski.com)

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
if (!defined('ABSPATH')) {
	die('-1');
}

// Plugin constants
define('ARI_VERSION', '1.0');


define('ARI_URL', plugin_dir_url(__FILE__));
define('ARI_DIR', plugin_dir_path(__FILE__));

// Function for easy load files
function _ari_load_files($dir, $files, $prefix = '') {
	foreach ($files as $file) {
		if (is_file($dir . $prefix . $file . ".php")) {
			require_once($dir . $prefix . $file . ".php");
		}
	}
}

// Plugin client classes
_ari_load_files( ARI_DIR . 'classes/', array('main', 'plugin', 'image') );

add_action('plugins_loaded', 'init_ari_plugin');
function init_ari_plugin() {
	// Load client
	new ARI_Image();
	new ARI_Plugin();
	new ARI_Main();
}
