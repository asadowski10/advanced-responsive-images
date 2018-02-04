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
	 * @var []
	 */
	private $args = array();

	/**
	 * @var int
	 */
	private $attachment_id = 0;

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
		$check_tpl = $this->check_tpl( $location_array, $html );
		if ( ! is_array( $check_tpl ) ) {
			return $check_tpl;
		}

		$img_size = Image_Sizes::get_instance();


		$location_content = $check_tpl['location_content'];
		$main_content     = $check_tpl['main_content'];

		$classes        = isset( $this->args['classes'] ) ? array( $this->args['classes'] ) : [];
		$location_array = reset( $location_array );
		foreach ( $location_array->srcsets as $location ) {
			if ( ! isset( $location->size ) || empty( $location->size ) ) {
				continue;
			}

			$image_size = (array) $img_size->get_image_size( $location->size );
			if ( empty( $image_size ) ) {
				trigger_error( 'Missing a image size declaration on BEA Images - ' . $location->size . ' for this location : ' . $this->args['data-location'], E_USER_WARNING );
			}
			/**
			 * @var $img_size Image_Sizes
			 */
			$img = wp_get_attachment_image_src( $this->attachment_id, (array) $img_size->get_image_size( $location->size ) );
			if ( empty( $img ) ) {
				continue;
			}

			// Verif SSL
			$img[0] = ( function_exists( 'is_ssl' ) && is_ssl() ) ? str_replace( 'http://', 'https://', $img[0] ) : $img[0];

			// Replace size in content
			$location_content = str_replace( '%%' . $location->size . '%%', $img[0], $location_content );

			// Get classes for each size
			if ( isset( $location->class ) && ! empty( $location->class ) ) {
				$classes[] = $location->class;
			}
		}

		// Add default img url
		if ( isset( $location_array->img_base ) && ! empty( $location_array->img_base ) ) {
			$default_img = wp_get_attachment_image_src( $this->attachment_id, (array) $img_size->get_image_size( $img_size ), false );
		} else {
			$default_img = wp_get_attachment_image_src( $this->attachment_id, 'thumbnail', false );
		}

		if ( is_array( $default_img ) ) {
			$main_content = str_replace( '%%default_img%%', reset( $default_img ), $main_content );
		}

		// Add sources in main content tpl
		$content_with_sources = str_replace( '%%sources%%', $location_content, $main_content );

		// Add all attributes : classes, alt...
		$alt     = trim( strip_tags( get_post_meta( $this->attachment_id, '_wp_attachment_image_alt', true ) ) );
		$classes = implode( ' ', $classes );

		$attributes              = 'class="lazyload ' . esc_attr( $classes ) . '" alt="' . esc_attr( $alt ) . '"';
		$content_with_attributes = str_replace( '%%attributes%%', $attributes, $content_with_sources );

		// Add pixel on all
		return str_replace( [ '%%srcset%%', '%%srcgif%%', '%%data-location%%' ], [
			'srcset="' . ARI_PIXEL . '"',
			'src="' . ARI_PIXEL . '"',
			'<!-- data-location="' . $this->args['data-location'] . '" -->',

		], $content_with_attributes );
	}

	/**
	 * @param $location_array
	 * @param $html
	 *
	 * @return array|mixed
	 * @author Alexandre Sadowski
	 */
	private function check_tpl( $location_array, $html ) {
		if ( ! is_array( $location_array ) ) {
			return $html . '<!-- data-error="Location ' . $this->args['data-location'] . ' not found in image-locations file" -->';
		}

		$location_array = reset( $location_array );
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			return $html . '<!-- data-error="No srcsets found or not V2 JSON for location (' . $this->args['data-location'] . ')" -->';
		}

		//Check if default tpl is overloaded
		if ( isset( $this->args['data-tpl'] ) && ! empty( $this->args['data-tpl'] ) ) {
			$main_tpl_name = $this->args['data-tpl'];
			$main_tpl      = ARI_JSON_DIR . 'tpl/' . $this->args['data-tpl'] . '.tpl';
		} else {
			$main_tpl_name = 'default-picture';
			$main_tpl      = ARI_JSON_DIR . 'tpl/default-picture.tpl';
		}

		if ( ! is_readable( $main_tpl ) ) {
			return $html . '<!-- data-error="Default tpl not exists or not readable (' . $main_tpl_name . ')" -->';
		}

		$main_content = file_get_contents( $main_tpl );
		if ( empty( $main_content ) ) {
			return $html . '<!-- data-error="Empty default tpl : (' . $main_tpl_name . ')" -->';
		}

		//Check if default tpl is overloaded
		$location_tpl = ARI_JSON_DIR . 'tpl/' . $this->args['data-location'] . '.tpl';

		if ( ! is_readable( $location_tpl ) ) {
			return $html . '<!-- data-error="Location tpl not exists or not readable (' . $this->args['data-location'] . ')" -->';
		}

		$location_content = file_get_contents( $location_tpl );
		if ( empty( $location_content ) ) {
			return $html . '<!-- data-error="Empty location tpl : (' . $this->args['data-location'] . ')" -->';
		}

		return array( 'location_content' => $location_content, 'main_content' => $main_content );
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
		if ( ! isset( $this->args['data-location'] ) ) {
			return $html . '<!-- data-error="No data-location found in arguments" -->';
		}

		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $this->args['data-location'] );
		if ( empty( $location_array ) ) {
			return $html . '<!-- data-error="Location ' . $this->args['data-location'] . ' not found in image-locations file" -->';
		}

		$location_array = array_shift( $location_array );
		if ( ! isset( $location_array->default_img ) || empty( $location_array->default_img ) ) {
			return $html . '<!-- data-error="No default_img ( ' . $location_array->default_img . ' ) attribute in json for location : ' . $this->args['data-location'] . '" -->';
		}

		$default_path = apply_filters( 'ari_responsive_image_default_img_path', '/assets/img/default/', $this->args );
		$img_path     = $default_path . $location_array->default_img;

		if ( ! is_readable( get_stylesheet_directory() . $img_path ) ) {
			return $html . '<!-- data-error="Default img (' . $location_array->default_img . ') not exists or not readable" -->';
		}

		$classes   = array( 'attachment-thumbnail', 'wp-post-image' );
		$classes[] = isset( $attr['class'] ) ? $this->args['class'] : '';

		$classes[] = 'lazyload';

		return '<noscript><img src="' . get_stylesheet_directory_uri() . $img_path . '" alt=""/></noscript><img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="' . get_stylesheet_directory_uri() . $img_path . '" class="' . implode( ' ', $classes ) . '" alt="" data-location="' . $this->args['data-location'] . '">';
	}
}