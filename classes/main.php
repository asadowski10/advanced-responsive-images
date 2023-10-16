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
		add_filter( 'post_thumbnail_html', array( $this, 'post_thumbnail_html' ), 10, 5 );
		add_filter( 'wp_get_attachment_image', array( $this, 'wp_get_attachment_image' ), 10, 5 );

		if ( function_exists( 'wpthumb' ) ) {
			// Override the calculated image sizes
			add_filter( 'wp_calculate_image_sizes', '__return_false', PHP_INT_MAX );

			// Override the calculated image sources
			add_filter( 'wp_calculate_image_srcset', '__return_false', PHP_INT_MAX );

			// Remove the reponsive stuff from the content
			remove_filter( 'the_content', 'wp_make_content_images_responsive' );

			// Disable the "BIG image" threshold value
			add_filter( 'big_image_size_threshold', '__return_false' );
		}

		add_action( 'attachment_updated', array( $this, 'attachment_updated' ) );
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
	 * @param $html
	 * @param $attachment_id
	 * @param $size
	 * @param $icon
	 * @param $attr
	 *
	 * @return string HTML of img
	 * @author Alexandre Sadowski
	 */
	public function wp_get_attachment_image( $html, $attachment_id, $size, $icon, $attr ){
		return $this->attachment_html( $html, $attachment_id, $attr );
	}
	/**
	 * @param $html
	 * @param $post_id
	 * @param $post_thumbnail_id
	 * @param $size
	 * @param $attr
	 *
	 * @return string HTML of img
	 * @author Alexandre Sadowski
	 */
	public function post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		return $this->attachment_html( $html, $post_thumbnail_id, $attr );
	}

	/**
	 * Generate HTML markup for attachment.
	 *
	 * @param $html
	 * @param $attachment_id
	 * @param $attr
	 *
	 * @return array|mixed|string|string[]
	 * @author Alexandre Sadowski
	 */
	protected function attachment_html( $html, $attachment_id, $attr ){
		if ( is_feed() ) {
			return $html;
		}

		if ( ! isset( $attr['data-location'] ) ) {
			return $html . '<!-- data-error="No data-location found in arguments" -->';
		}

		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $attr['data-location'] );
		if ( empty( $location_array ) ) {
			return $html . '<!-- data-error="Location ' . $attr['data-location'] . ' not found in image-locations file" -->';
		}

		/**
		 * @var $mode Modes
		 */
		$mode = Modes::get_instance();
		try {
			$_mode_instance = $mode->get_mode( $attr );
			if ( false === $_mode_instance ) {
				return $html . '<!-- data-error="No mode found" -->';
			}

			$_mode_instance->set_attachment_id( $attachment_id );

			return $_mode_instance->render_image( $html );
		} catch ( \Exception $e ) {
			$attr['data-location'] = $e->getMessage();
		}

		return $html . '<!-- data-error="Error to render image, manual debug is needed" -->';
	}

	/**
	 * Flush group cache on update attachment
	 *
	 * @param int $post_id
	 * 
	 * @return void
	 * @author Alexandre Sadowski
	 */
	public function attachment_updated( $post_id ) {
		if ( ! \wp_cache_supports( 'flush_group' ) ) {
			return;
		}

		\wp_cache_flush_group( 'ari-' . $post_id );
	}
}