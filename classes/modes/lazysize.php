<?php
namespace ARI\Modes;

use ARI\Mode_Interface;
use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class Lazysize
 * @package ARI\Modes
 */
class Lazysize extends Mode implements Mode_Interface {

	/**
	 * Use the trait
	 */
	use \ARI\Singleton;

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
	 * @param $location
	 *
	 * @author Alexandre Sadowski
	 */
	public function render_image( $html = '' ) {
		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $this->args['data-location'] );
		if ( ! is_array( $location_array ) ) {
			$this->args['data-location'] = 'No location found in source file';

			return $this->args;
		}

		$location_array = reset( $location_array );
		$srcset_attrs   = array();
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			$this->args['data-location'] = 'No srcsets found or not V2 JSON';
		} else {
			// add lazyload on all medias
			$this->args['class'] = $this->args['class'] . ' lazyload';

			foreach ( $location_array->srcsets as $location ) {
				if ( ! isset( $location->size ) || empty( $location->size ) ) {
					continue;
				}
				/**
				 * @var $img_size Image_Sizes
				 */
				$img_size = Image_Sizes::get_instance();
				$img      = wp_get_attachment_image_src( $this->attachment_id, (array) $img_size->get_image_size( $location->size ) );
				if ( empty( $img ) ) {
					continue;
				}

				// Verif SSL
				$img[0] = ( function_exists( 'is_ssl' ) && is_ssl() ) ? str_replace( 'http://', 'https://', $img[0] ) : $img[0];

				if ( isset( $location->class ) && ! empty( $location->class ) ) {
					$this->args['class'] = $this->args['class'] . ' ' . $location->class;
				}
				$srcset_attrs[] = $img[0] . ' ' . $location->srcset;
			}
		}

		if ( ! empty( $srcset_attrs ) ) {
			$this->args['data-srcset'] = implode( ', ', $srcset_attrs );
			$this->args['src']         = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
		}

		return $this->args;
	}

	/**
	 * @author Alexandre Sadowski
	 */
	public function add_filters() {
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'get_attributes' ), self::$priority, 2 );
		add_filter( 'post_thumbnail_html', array( $this, 'default_img' ), self::$priority, 5 );
		add_filter( 'post_thumbnail_html', array( 'ARI\Main', 'remove_thumbnail_dimensions' ), self::$priority, 5 );
		self::$priority ++;
	}

	/**
	 * @param array $args
	 * @param \WP_Post $attachment
	 *
	 * @return array|string
	 * @author Alexandre Sadowski
	 */
	public function get_attributes( $args = array(), \WP_Post $attachment ) {
		if ( ! isset( $this->args['data-location'] ) ) {
			return $args;
		}

		return $this->render_image();
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
	public function default_img( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( ! empty( $html ) ) {
			return $html;
		}

		if ( ! isset( $this->args['data-location'] ) ) {
			return $html;
		}

		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $this->args['data-location'] );
		if ( empty( $location_array ) ) {
			return $html;
		}

		$location_array = array_shift( $location_array );
		if ( ! isset( $location_array->default_img ) || empty( $location_array->default_img ) ) {
			return $html;
		}

		$default_path = apply_filters( 'ari_responsive_image_default_img_path', '/src/img/default/', $attr );
		$img_path     = $default_path . $location_array->default_img;

		if ( ! is_file( get_stylesheet_directory() . $img_path ) ) {
			return $html;
		}

		$classes   = array( 'attachment-thumbnail', 'wp-post-image' );
		$classes[] = isset( $attr['class'] ) ? $attr['class'] : '';

		$classes[] = 'lazyload';

		return '<noscript><img src="' . get_stylesheet_directory_uri() . $img_path . '"/></noscript><img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="' . get_stylesheet_directory_uri() . $img_path . '" class="' . implode( ' ', $classes ) . '">';
	}

}