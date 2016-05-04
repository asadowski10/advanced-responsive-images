<?php
namespace ARI;

/**
 * The purpose of the Image_Sizes class is to load images sizes :
 *
 * Class Image_Sizes
 * @package ARI
 */
class Image_Sizes {
	/**
	 * Use the trait
	 */
	use Singleton;

	private $image_sizes;

	protected function init() {
		// Get data from JSON
		$this->load_image_sizes();

		// Set image size to WP
		$this->add_image_sizes();
	}

	/*
	 * Load JSON Image Sizes
	 *
	 * @author Alexandre Sadowski
	 */
	public function load_image_sizes() {
		if ( ! is_file( ARI_JSON_DIR . 'image-sizes.json' ) ) {
			return false;
		}
		$file_content = file_get_contents( ARI_JSON_DIR . 'image-sizes.json' );
		$result       = json_decode( $file_content );
		if ( is_array( $result ) && ! empty( $result ) ) {
			$this->image_sizes = $result;
		}
	}

	/*
	 * Add Image Sizes in WP
	 *
	 * @author Alexandre Sadowski
	 */
	public function add_image_sizes() {
		if ( ! is_array( $this->image_sizes ) || empty( $this->image_sizes ) ) {
			return false;
		}
		foreach ( $this->image_sizes as $key => $value ) {
			foreach ( $value as $name => $attributes ) {
				if ( empty( $attributes ) ) {
					continue;
				}
				if ( isset( $attributes->width ) && ! empty( $attributes->width ) && isset( $attributes->height ) && ! empty( $attributes->height ) && isset( $attributes->crop ) ) {
					add_image_size( $name, $attributes->width, $attributes->height, $attributes->crop );
				}
			}
		}

		return true;
	}

	/*
	 * Get attributes of an image size
	 *
	 * @value string $location The location name used in JSON
	 * @return array|false $attributes Array of attributes in JSON : width, height, crop
	 *
	 * @author Alexandre Sadowski
	 */
	public function get_image_size( $location = '' ) {
		if ( ! is_array( $this->image_sizes ) | empty( $this->image_sizes ) ) {
			return false;
		}
		foreach ( $this->image_sizes as $key => $value ) {
			foreach ( $value as $name => $attributes ) {
				if ( $name == $location ) {
					return $attributes;
				}
			}
		}

		return false;
	}

	/*
	 * Get all image sizes
	 *
	 * @return array : JSON image sizes
	 *
	 * @author Nicolas Juen
	 */
	public function get_image_sizes() {
		if ( ! is_array( $this->image_sizes ) || empty( $this->image_sizes ) ) {
			return array();
		}

		return $this->image_sizes;
	}
}