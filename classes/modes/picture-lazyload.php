<?php
namespace ARI\Modes;

use ARI\Mode_Interface;
use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class Picture_Lazyload
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
	 * @param $location
	 *
	 * @author Alexandre Sadowski
	 */
	public function render_image() {
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
			$this->args['srcset'] = implode( ', ', $srcset_attrs );
		}

		return $this->args;
	}

	/**
	 * @author Alexandre Sadowski
	 */
	public function add_filters() {
		add_filter( 'post_thumbnail_html', array( $this, 'update_html' ), self::$priority, 5 );
		self::$priority ++;
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
	public function update_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( ! isset( $this->args['data-location'] ) ) {
			return $attr;
		}

		return $this->render_image();
	}
}