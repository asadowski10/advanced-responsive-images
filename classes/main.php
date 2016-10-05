<?php
namespace ARI;

use ARI\Image_Locations;
use ARI\Modes;

/**
 * The purpose of the main class is to init all the plugin base code like :
 *  - Taxonomies
 *  - Post types
 *  - Shortcodes
 *  - Posts to posts relations etc.
 *  - Loading the text domain
 *
 * Class Main
 * @package ARI
 */
class Main {
	/**
	 * Use the trait
	 */
	use Singleton;

	protected function init() {
		add_action( 'init', array( $this, 'init_translations' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'get_attributes' ), 10, 2 );

		// Override the calculated image sizes
		add_filter( 'wp_calculate_image_sizes', '__return_false', PHP_INT_MAX );

		// Override the calculated image sources
		add_filter( 'wp_calculate_image_srcset', '__return_false', PHP_INT_MAX );

		// Remove the reponsive stuff from the content
		remove_filter( 'the_content', 'wp_make_content_images_responsive' );
	}

	/**
	 * Remove the thumbnail dimensions on images
	 *
	 * @param string : the html of the image
	 *
	 * @return string : the removed width/height attributres
	 * @author Nicolas Juen
	 */
	public static function remove_thumbnail_dimensions( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		return preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );
	}

	/**
	 * Load the plugin translation
	 */
	public static function init_translations() {
		// Load translations
		load_plugin_textdomain( 'ari', false, ARI_DIR . 'languages' );
	}

	/**
	 * @param array $args
	 * @param \WP_Post $attachment
	 *
	 * @return array
	 * @author Alexandre Sadowski
	 */
	public function get_attributes( $args = array(), \WP_Post $attachment ) {
		if ( ! isset( $args['data-location'] ) ) {
			$args['data-location'] = 'No location filled in';

			return $args;
		}

		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $args['data-location'] );
		if ( empty( $location_array ) ) {
			$args['data-location'] = 'No location found in source file';

			return $args;
		}

		/**
		 * @var $mode Modes
		 */
		$mode = Modes::get_instance();
		try {
			$_mode_instance = $mode->get_mode( $args );
			if ( false === $_mode_instance ) {
				return $args;
			}
			$_mode_instance->set_attachment_id( $attachment->ID );
			$_mode_instance->add_filters();
		} catch ( \Exception $e ) {
			$args['data-location'] = $e->getMessage();
		}

		return $args;
	}
}