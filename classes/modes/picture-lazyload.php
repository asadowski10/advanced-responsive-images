<?php

namespace ARI\Modes;

use ARI\Mode_Interface;
use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class Picture_Lazyload
 *
 * List of vars replaced :
 *      - %%srcset%% : white pixel html
 *      - %%attributes%% : composed of classes and alt for default img
 *      - %%sources%% : list of sources composed of image sizes
 *      - %%img-617-333%% : exemple of image size to replace by URL
 *      - %%srcgif%% :
 *      - %%data-location%% : info of current location for template
 *
 * @package ARI\Modes
 */
class Picture_Lazyload extends Mode implements Mode_Interface {
	/**
	 * Use the trait
	 */
	use \ARI\Singleton;

	/**
	 * @var []
	 */
	protected $args = array();

	/**
	 * @var int
	 */
	protected $attachment_id = 0;

	/**
	 *
	 * @author Alexandre Sadowski
	 */
	protected function init() {
	}

	/**
	 * @param $args
	 *
	 * @author Alexandre Sadowski
	 */
	public function set_args( $args ) {
		$this->args = $args;
	}

	public function set_attachment_id( $id ) {
		$this->attachment_id = (int) $id;
	}

	/**
	 * @param string $html
	 *
	 * @author Alexandre Sadowski
	 */
	public function render_image( $html = '' ) {
		if ( empty( $html ) ) {
			return $this->default_img( $html );
		}
		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $this->args['data-location'] );

		//check all tpl
		$check_tpl = $this->check_tpl( $location_array, $this, 'default-picture.tpl');
		if ( is_wp_error( $check_tpl ) ) {
			return $html . $check_tpl->get_error_message();
		}

		$found = false;
		$key = md5( maybe_serialize( $this->args ) );
		$data  = wp_cache_get( $this->attachment_id . '-' . $key, 'ari-' . $this->attachment_id, false, $found );
		if ( ! empty( $found ) ) {
			return $data;
		}

		$img_size = Image_Sizes::get_instance();


		$location_content = $check_tpl['location_content'];
		$main_content     = $check_tpl['main_content'];

		$classes        = isset( $this->args['class'] ) ? array( $this->args['class'] ) : [];
		$location_array = reset( $location_array );
		foreach ( $location_array->srcsets as $location ) {
			if ( ! isset( $location->size ) || empty( $location->size ) ) {
				continue;
			}

			$img_url = $this->get_url_from_size( $img_size, $location->size, $this );
			if ( null === $img_url ) {
				continue;
			}

			// Replace size in content
			$location_content = str_replace( '%%' . $location->size . '%%', $img_url, $location_content );

			// Get classes for each size
			if ( isset( $location->class ) && ! empty( $location->class ) ) {
				$classes[] = $location->class;
			}
		}

		// Add default img url
		if ( isset( $location_array->img_base ) && ! empty( $location_array->img_base ) ) {
			$imgsizedefault = function_exists( 'wpthumb' ) ? (array) $img_size->get_image_size( $location_array->img_base ) : $location_array->img_base;
			$default_img    = wp_get_attachment_image_src( $this->attachment_id, $imgsizedefault, false );
		} else {
			$default_img = wp_get_attachment_image_src( $this->attachment_id, 'thumbnail', false );
		}

		if ( is_array( $default_img ) ) {
			$main_content = str_replace( '%%default_img%%', reset( $default_img ), $main_content );
		}

		// Add sources in main content tpl
		$content_with_sources = str_replace( '%%sources%%', $location_content, $main_content );

		// Add all attributes : classes, alt...
		$alt     = $this->get_alt_text( $this );
		$classes = implode( ' ', $classes );

		$attributes              = 'class="lazyload ' . esc_attr( $classes ) . '" alt="' . esc_attr( $alt ) . '"';
		$content_with_attributes = str_replace( '%%attributes%%', $attributes, $content_with_sources );

		$caption = $this->get_caption( $this );
		$content_with_caption = str_replace( '%%caption%%', $caption, $content_with_attributes );

		// Add pixel on all
		$image = str_replace( [ '%%srcset%%', '%%srcgif%%', '%%data-location%%' ], [
			'srcset="' . ARI_PIXEL . '"',
			'src="' . ARI_PIXEL . '"',
			'<!-- data-location="' . $this->args['data-location'] . '" -->',

		], $content_with_caption );

		wp_cache_set( $this->attachment_id . '-' . $key, $image, 'ari-' . $this->attachment_id );

		return $image;
	}

	/**
	 * Display default img if empty post_thumbnail
	 *
	 * @param $html
	 * @param $post_id
	 * @param $post_thumbnail_id
	 * @param $size
	 * @param $attr
	 *
	 * @return string
	 * @author Alexandre Sadowski
	 */
	public function default_img( $html = '' ) {
		if ( isset( $this->args['placeholder'] ) && false === $this->args['placeholder'] ) {
			return '';
		}

		$img_path = $this->get_default_img_path( $this );
		if ( is_wp_error( $img_path ) ) {
			return $html . '<!-- data-error="' . $img_path->get_error_message() . '" -->';
		}

		$classes   = array( 'attachment-post-thumbnail', 'size-post-thumbnail', 'wp-post-image' );
		$classes[] = isset( $this->args['class'] ) ? $this->args['class'] : '';

		$classes[] = 'lazyload';

		return '<noscript><img src="' . get_stylesheet_directory_uri() . $img_path . '" alt=""/></noscript><img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="' . get_stylesheet_directory_uri() . $img_path . '" class="' . implode( ' ', $classes ) . '" alt="" data-location="' . $this->args['data-location'] . '">';
	}
}
