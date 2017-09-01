<?php

namespace ARI\Modes;

use ARI\Mode_Interface;
use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class SRCSET
 * @package ARI\Modes
 */
class Srcset_Front extends Mode implements Mode_Interface {

	/**
	 * @var []
	 */
	private $args = array();

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
			echo 'No location found in source file';

			return;
		}

		$location_array = reset( $location_array );
		$srcset_attrs   = array();
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			echo 'No srcsets found or not V2 JSON';

			return;
		} else {
			$img_size = Image_Sizes::get_instance();

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

				if ( isset( $location->class ) && ! empty( $location->class ) ) {
					$this->args['class'] = $this->args['class'] . ' ' . $location->class;
				}
				$srcset_attrs[] = htmlspecialchars_decode( $img_url ) . ' ' . $location->srcset;
			}
		}

		if ( ! empty( $srcset_attrs ) ) {
			$this->args['srcset'] = implode( ', ', $srcset_attrs );
		}

		$src = $this->front_default_img( $location_array, $this->size_or_img_name );
		if ( ! empty( $src ) ) {
			$this->args['src'] = htmlspecialchars_decode( $src );
		}

		// Write HTML
		$html = rtrim( "<img" );
		foreach ( $this->args as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';

		echo $html;
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
}