<?php

namespace ARI\modes;

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
class Native extends Mode implements Mode_Interface {
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
		$main_content = $this->check_tpl( $location_array, $this, 'default-native.tpl' );
		if ( is_wp_error( $main_content ) ) {
			return $html . $main_content->get_error_message();
		}

		$found = false;
		$key   = md5( maybe_serialize( $this->args ) );
		$data  = wp_cache_get( $this->attachment_id . '-' . $key, '', false, $found );
		if ( ! empty( $found ) ) {
			return $data;
		}

		$img_sizes = Image_Sizes::get_instance();

		// Build src : original image
		$original_url = wp_get_attachment_image_url( $this->attachment_id, 'full' );
		if ( empty( $original_url ) ) {
			return $html;
		}

		$all_img_sizes = [];
		// Build srcset
		$location_array = reset( $location_array );
		foreach ( $location_array->srcsets as $location ) {
			if ( ! isset( $location->size ) || empty( $location->size ) ) {
				continue;
			}

			$img_url = $this->get_url_from_size( $img_sizes, $location->size, $this );
			if ( null === $img_url ) {
				continue;
			}

			$img_size = $img_sizes->get_image_size( $location->size );

			$all_img_sizes[] = $img_url . ' ' . $img_size->width . 'w';
		}

		$attributes = [
			'decoding' => 'async',
		];
		// Build Sizes : sizes="auto, (max-width: 784px) 100vw, 784px"
		$big_sizes = end( $location_array->srcsets );
		if ( ! empty( $big_sizes->size ) ) {
			$image_size = (array) $img_sizes->get_image_size( $big_sizes->size );
			if ( empty( $image_size ) ) {
				trigger_error( 'Missing a image size declaration on BEA Images - ' . $big_sizes->size . ' for this location : ' . $this->args['data-location'],
					E_USER_WARNING );
			} else {
				$attributes['sizes'] = 'auto, (max-width: ' . $image_size['width'] . 'px) 100vw, ' . $image_size['width'] . 'px';

				// Build width & height
				$width_value  = false === $image_size['crop'] && '9999' === $image_size['width'] ? 'auto' : $image_size['width'];
				$height_value = false === $image_size['crop'] && '9999' === $image_size['height'] ? 'auto' : $image_size['height'];

				$attributes['width']  = $width_value;
				$attributes['height'] = $height_value;
			}
		}


		// Build fetchpriority or loading
		if ( ! empty( $this->args['fetchpriority'] ) ) {
			$attributes['fetchpriority'] = esc_attr( $this->args['fetchpriority'] );
		} else {
			$attributes['loading'] = 'lazy';
		}

		// Build alt
		$attributes['alt'] = $this->get_alt_text( $this );

		// Build classes
		$classes             = isset( $this->args['class'] ) ? array( $this->args['class'] ) : [];
		$classes             = implode( ' ', $classes );
		$attributes['class'] = $classes;


		$attr = array_map( 'esc_attr', $attributes );
		$html = '';
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}

		// Build caption
		$caption = $this->get_caption( $this );


		//Replace data-location
		$main_content = str_replace( '%%data-location%%', '<!-- data-location="' . $this->args['data-location'] . '" -->', $main_content );

		//Replace src
		$main_content = str_replace( '%%src%%', 'src="' . $original_url . '"', $main_content );

		//Replace srcset
		$main_content = str_replace( '%%srcset%%', 'srcset="' . implode( ',', $all_img_sizes ) . '"', $main_content );

		//Replace attributes
		$main_content = str_replace( '%%attributes%%', $html, $main_content );

		//Replace caption
		$main_content = str_replace( '%%caption%%', $caption, $main_content );

		wp_cache_set( $this->attachment_id . '-' . $key, $main_content, '' );

		return $main_content;
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
		return $html;
	}

	/**
	 * Check TPL
	 *
	 * @param $location_array
	 * @param $mode
	 * @param $tpl_name
	 *
	 * @return string|\WP_Error
	 * @author Alexandre Sadowski
	 */
	public function check_tpl( $location_array, $mode, $tpl_name ) {
		if ( ! is_array( $location_array ) ) {
			return new \WP_Error( 'ari-error', "Location ' . $mode->args['data-location'] . ' not found in image-locations file" );
		}

		$location_array = reset( $location_array );
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			return new \WP_Error( 'ari-error', "No srcsets found or not V2 JSON for location (' . $mode->args['data-location'] . ')" );
		}

		$main_tpl = ARI_JSON_DIR . 'tpl/' . $tpl_name;

		//Check if default tpl is overloaded
		if ( ( isset( $mode->args['data-caption'] ) && ( '1' === $mode->args['data-caption'] || true === $mode->args['data-caption'] ) ) && ! empty( $this->get_caption( $mode ) ) ) {
			$main_tpl = ARI_JSON_DIR . 'tpl/default-native-caption.tpl';
		}

		if ( ! is_readable( $main_tpl ) ) {
			return new \WP_Error( 'ari-error', "Default tpl not exists or not readable (' . $tpl_name . ')" );
		}

		$handle       = fopen( $main_tpl, 'r' );
		$main_content = fread( $handle, filesize( $main_tpl ) );
		fclose( $handle );
		if ( empty( $main_content ) ) {
			return new \WP_Error( 'ari-error', "Empty default tpl : (' . $tpl_name . ')" );
		}

		return $main_content;
	}
}
