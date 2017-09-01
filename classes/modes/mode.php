<?php

namespace ARI\Modes;

use ARI\Image_Sizes;

/**
 * Abstract Class Mode
 * @package ARI\Modes
 */
abstract class Mode {
	/**
	 * Use the trait
	 */
	use \ARI\Singleton;

	/**
	 * @var int
	 */
	protected static $priority = 12;

	abstract protected function init();

	/**
	 * Emulate get_attachment_image_src for HTML context
	 *
	 * @param string $size_or_img_name
	 * @param string $image_size
	 *
	 * @return string
	 */
	protected function get_attachment_image_src( $size_or_img_name = 'thumbnail', $image_size = '' ) {
		$is_img = $this->is_size_or_img( $size_or_img_name );
		if ( true === $is_img ) {
			return $this->get_file( BEA_IMG_SAMPLE_DIR . $size_or_img_name, $image_size );
		}

		$img_url = $this->get_random_sample_img_url( $size_or_img_name );

		return $this->get_timthumb_url( $img_url, $image_size );
	}

	/*
	 * Get random sample img url
	 * @author Alexandre Sadowski
	 */
	protected function get_random_sample_img_url( $img_prefix = 'thumbnail' ) {
		if ( strrpos( $img_prefix, '-' ) !== false ) {
			$matches = glob( BEA_IMG_SAMPLE_DIR . $img_prefix . '{*.gif,*.jpg,*.png,*.jpeg}', GLOB_BRACE );
		} else {
			$matches = glob( BEA_IMG_SAMPLE_DIR . '{*.gif,*.jpg,*.png,*.jpeg}', GLOB_BRACE );
		}
		if ( empty( $matches ) ) {
			return false;
		}

		$rand_img = array_rand( $matches, 1 );

		$img_path = $matches[ $rand_img ];

		return str_replace( BEA_IMG_SAMPLE_DIR, BEA_IMG_SAMPLE_URL, $img_path );
	}

	/*
	 * Check if is a img name or a size
	 *
	 * @return bool true|false
	 * @author Alexandre Sadowski
	 */
	protected function is_size_or_img( $size_or_img_name = 'thumbnail' ) {
		if ( 'thumbnail' == $size_or_img_name ) {
			return false;
		}

		foreach ( Image_Sizes::$allowed_ext as $ext ) {
			if ( is_file( BEA_IMG_SAMPLE_DIR . $size_or_img_name . $ext ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build Timthumb URL
	 *
	 * @param string $path_img
	 * @param $image_size
	 *
	 * @return string
	 */
	protected function get_timthumb_url( $path_img, $image_size = null ) {
		if ( ! empty( $image_size ) && isset( $image_size['width'] ) ) {
			return get_full_url( $_SERVER, true ) . 'functions/vendor/timthumb.php?src=' . $path_img . '&h=' . $image_size['height'] . '&w=' . $image_size['width'] . '&zc=' . (int) $image_size['crop'];
		} else {
			return get_full_url( $_SERVER, true ) . 'functions/vendor/timthumb.php?src=' . $path_img;
		}
	}

	/**
	 * @param string $path
	 * @param string $image_size
	 *
	 * @return string
	 * @author Alexandre Sadowski
	 */
	protected function get_file( $path = '', $image_size = '' ) {
		$img_size = Image_Sizes::get_instance();
		foreach ( $img_size::$allowed_ext as $ext ) {
			if ( is_file( $path . $ext ) ) {
				return $this->get_timthumb_url( $path . $ext, $image_size );
			}
		}
	}

	/**
	 * @param $location_array
	 * @param $size_or_img_name
	 *
	 * @return string
	 * @author Alexandre Sadowski
	 */
	protected function front_default_img( $location_array, $size_or_img_name ) {
		//Get img_base size for base SRC
		if ( isset( $location_array->img_base ) && ! empty( isset( $location_array->img_base ) ) ) {
			$img_size = Image_Sizes::get_instance();
			$_size    = $img_size->get_image_size( $location_array->img_base );
			if ( ! empty( $_size ) ) {
				$image_size = (array) $_size;
			}
		}

		$is_img = $this->is_size_or_img( $size_or_img_name );
		if ( true === $is_img ) {
			$src = $this->get_file( BEA_IMG_SAMPLE_DIR . $size_or_img_name, $image_size );

			return $src;
		} else {
			$img_url = $this->get_random_sample_img_url( $size_or_img_name );
			$src     = $this->get_timthumb_url( $img_url, $image_size );

			return $src;
		}
	}
}