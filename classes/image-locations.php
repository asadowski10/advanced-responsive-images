<?php
namespace ARI;

/**
 * The purpose of the Image_Locations class is to load images locations :
 *
 * Class Image_Locations
 * @package ARI
 */
class Image_Locations {
	/**
	 * Use the trait
	 */
	use Singleton;

	private $image_locations;

	protected function init() {
		// Get data from JSON
		$this->load_locations();
	}

	/*
	 * Load JSON Image Locations
	 *
	 * @author Alexandre Sadowski
	 */
	public function load_locations() {
		if ( ! is_file( ARI_JSON_DIR . 'image-locations.json' ) ) {
			return false;
		}
		$file_content = file_get_contents( ARI_JSON_DIR . 'image-locations.json' );
		$result       = json_decode( $file_content );
		if ( is_array( $result ) && ! empty( $result ) ) {
			$this->image_locations = $result;
		}
	}

	/*
	 * Get attributes of a location
	 *
	 * @value string $location The location name used in JSON
	 * @return array|false $attributes Array of attributes in JSON : srcset, size, class, default_src...
	 *
	 * @author Alexandre Sadowski
	 */
	public function get_location( $location = '' ) {
		if ( ! is_array( $this->image_locations ) | empty( $this->image_locations ) ) {
			return false;
		}
		foreach ( $this->image_locations as $key => $value ) {
			foreach ( $value as $name => $attributes ) {
				if ( $name == $location ) {
					return $attributes;
				}
			}
		}

		return false;
	}

}