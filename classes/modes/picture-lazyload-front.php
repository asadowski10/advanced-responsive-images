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
 *
 * @package ARI\Modes
 */
class Picture_Lazyload_Front extends Mode implements Mode_Interface {

	/**
	 * @var []
	 */
	private $args = array();

	/**
	 * @var int
	 */
	private $attachment_id = 0;

	private $size_or_img_name = '';

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

	public function set_img_name( $size_or_img_name ) {
		$this->size_or_img_name = $size_or_img_name;
	}

	/**
	 * @author Alexandre Sadowski
	 */
	public function add_filters() {
		if ( ! isset( $this->args['data-location'] ) ) {
			return $this->args;
		}

		return $this->render_image();
	}

	/**
	 * @param string $html
	 *
	 * @author Alexandre Sadowski
	 */
	public function render_image( $html = '' ) {
		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $this->args['data-location'] );

		//check all tpl
		$check_tpl = $this->check_tpl( $location_array );
		if ( ! is_array( $check_tpl ) ) {
			echo $check_tpl;
		}

		$img_size = Image_Sizes::get_instance();


		$location_content = $check_tpl['location_content'];
		$main_content     = $check_tpl['main_content'];

		$classes        = array( $this->args['class'] );
		$location_array = reset( $location_array );
		foreach ( $location_array->srcsets as $location ) {
			if ( ! isset( $location->size ) || empty( $location->size ) ) {
				continue;
			}
			/**
			 * @var $img_size Image_Sizes
			 */
			$img_url = $this->get_attachment_image_src( $this->size_or_img_name, (array) $img_size->get_image_size( $location->size ) );
			if ( empty( $img_url ) ) {
				continue;
			}

			// Verif SSL
			$img_url = ( function_exists( 'is_ssl' ) && is_ssl() ) ? str_replace( 'http://', 'https://', $img_url ) : $img_url;

			// Replace size in content
			$location_content = str_replace( '%%' . $location->size . '%%', htmlspecialchars_decode( $img_url ), $location_content );

			// Get classes for each size
			if ( isset( $location->class ) && ! empty( $location->class ) ) {
				$classes[] = $location->class;
			}
		}

		// Add default img url
		if ( isset( $location_array->img_base ) && ! empty( $location_array->img_base ) ) {
			$default_img = $this->get_attachment_image_src( $this->size_or_img_name, (array) $img_size->get_image_size( $img_size ) );
		} else {
			$default_img = $this->get_attachment_image_src( $this->size_or_img_name, 'thumbnail' );
		}

		if ( is_array( $default_img ) ) {
			$main_content = str_replace( '%%default_img%%', reset( htmlspecialchars_decode( $default_img ) ), $main_content );
		}

		// Add sources in main content tpl
		$content_with_sources = str_replace( '%%sources%%', $location_content, $main_content );

		// Add all attributes : classes, alt...
		$classes = implode( ' ', $classes );

		$attributes              = 'class="lazyload ' . esc_attr( $classes ) . '"';
		$content_with_attributes = str_replace( '%%attributes%%', $attributes, $content_with_sources );

		// Add pixel on all
		echo str_replace( '%%srcset%%', 'src="' . ARI_PIXEL . '"', $content_with_attributes );
	}

	/**
	 * @param $location_array
	 * @param $html
	 *
	 * @return array|mixed
	 * @author Alexandre Sadowski
	 */
	private function check_tpl( $location_array ) {
		if ( ! is_array( $location_array ) ) {
			return 'No location found in source file';
		}

		$location_array = reset( $location_array );
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			return 'No srcsets found or not V2 JSON';
		}

		//Check if default tpl is overloaded
		if ( isset( $this->args['data-tpl'] ) && ! empty( $this->args['data-tpl'] ) ) {
			$main_tpl = ARI_JSON_DIR . 'tpl/' . $this->args['data-tpl'] . '.tpl';
		} else {
			$main_tpl = ARI_JSON_DIR . 'tpl/default-picture.tpl';
		}


		if ( ! is_readable( $main_tpl ) ) {
			return 'Default tpl not exists or not readable';
		}

		$main_content = file_get_contents( $main_tpl );
		if ( empty( $main_content ) ) {
			return 'Empty default tpl';
		}

		//Check if default tpl is overloaded
		$location_tpl = ARI_JSON_DIR . 'tpl/' . $this->args['data-location'] . '.tpl';

		if ( ! is_readable( $location_tpl ) ) {
			return 'Location tpl not exists or not readable';
		}

		$location_content = file_get_contents( $location_tpl );
		if ( empty( $location_content ) ) {
			return 'Empty location tpl';
		}

		return array( 'location_content' => $location_content, 'main_content' => $main_content );
	}
}