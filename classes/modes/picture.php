<?php

namespace ARI\Modes;

use ARI\Mode_Interface;
use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class Picture
 *
 * List of vars replaced :
 *      - %%srcset%% : white pixel html
 *      - %%attributes%% : composed of classes and alt for default img
 *      - %%sources%% : list of sources composed of image sizes
 *      - %%img-617-333%% : exemple of image size to replace by URL
 *      - %%src%% :
 *      - %%data-location%% : info of current location for template
 *
 * @package ARI\Modes
 */
class Picture extends Mode implements Mode_Interface {

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
		$check_tpl = $this->check_tpl( $location_array, $this, 'default-picture-nolazyload.tpl' );
		if ( is_wp_error( $check_tpl ) ) {
			return $html . $check_tpl->get_error_message();
		}

		$found = false;
		$key   = md5( maybe_serialize( $this->args ) );
		$data  = wp_cache_get( $this->attachment_id . '-' . $key, '', false, $found );
		if ( ! empty( $found ) ) {
			return $data;
		}

		$img_size = Image_Sizes::get_instance();


		$location_content = $check_tpl['location_content'];
		$main_content     = $check_tpl['main_content'];

		$classes        = isset( $this->args['class'] ) ? array( $this->args['class'] ) : [];
		$location_array = reset( $location_array );
		$first_location = $location_array->srcsets[0];

		$main_img_url = $this->get_url_from_size( $img_size, $first_location->size, $this );
		if ( null === $main_img_url ) {
			return $html;
		}

		$first_location_img_size = (array) $img_size->get_image_size( $first_location->size );
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

		// Add sources in main content tpl
		$content_with_sources = str_replace( '%%sources%%', $location_content, $main_content );

		// Add all attributes : classes, alt...
		$alt     = $this->get_alt_text( $this );
		$classes = implode( ' ', $classes );

		$width  = 'width="' . $first_location_img_size['width'] . '"';
		$height = 'height="' . $first_location_img_size['height'] . '"';

		$attributes              = $width . ' ' . $height . ' class="priority ' . esc_attr( $classes ) . '" alt="' . esc_attr( $alt ) . '"';
		$content_with_attributes = str_replace( '%%attributes%%', $attributes, $content_with_sources );

		// Add pixel on all
		$image = str_replace( [ '%%src%%', '%%data-location%%' ], [
			'src="' . $main_img_url . '"',
			'<!-- data-location="' . $this->args['data-location'] . '" -->',

		], $content_with_attributes );

		wp_cache_set( $this->attachment_id . '-' . $key, $image, '' );

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

		return '<img src="' . get_stylesheet_directory_uri() . $img_path . '" alt="" class="' . implode( ' ', $classes ) . '" data-location="' . $this->args['data-location'] . '"/>';
	}
}
