<?php

namespace ARI\Modes;

use ARI\Image_Locations;
use ARI\Image_Sizes;

/**
 * Abstract Class Mode
 * @package ARI\Modes
 */
abstract class Mode {

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
		if ( 'thumbnail' === $size_or_img_name ) {
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
		$path_img = urlencode( $path_img );
		if ( ! empty( $image_size ) && isset( $image_size['width'] ) ) {
			return get_full_url( $_SERVER,
					true ) . 'functions/vendor/timthumb.php?src=' . $path_img . '&h=' . $image_size['height'] . '&w=' . $image_size['width'] . '&zc=' . (int) $image_size['crop'];
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

	/**
	 *
	 * @param @var $img_size Image_Sizes
	 * @param $size string
	 * @param $mode
	 *
	 * @return string|null
	 * @author Alexandre Sadowski
	 */
	protected function get_url_from_size( $img_size, $size, $mode ): ?string {
		$image_size = (array) $img_size->get_image_size( $size );
		if ( empty( $image_size ) ) {
			trigger_error( 'Missing a image size declaration on BEA Images - ' . $size . ' for this location : ' . $mode->args['data-location'],
				E_USER_WARNING );
		}
		/**
		 * @var $img_size Image_Sizes
		 */
		$imgsize = function_exists( 'wpthumb' ) ? (array) $img_size->get_image_size( $size ) : $size;
		$img_url = wp_get_attachment_image_url( $mode->attachment_id, $imgsize );
		if ( empty( $img_url ) ) {
			return null;
		}

		// Verif SSL
		return ( function_exists( 'is_ssl' ) && is_ssl() ) ? str_replace( 'http://', 'https://', $img_url ) : $img_url;
	}

	/**
	 * Generate alt text
	 * Rules :
	 *  - If "none" is set return an empty alt
	 *  - If alt is set return it
	 *  - If empty alt, generate it from WP
	 *
	 * @return string
	 * @author Alexandre Sadowski
	 */
	protected function get_alt_text( $mode ) {
		if ( empty( $mode->args['alt'] ) ) {
			$alt = trim( strip_tags( get_post_meta( $mode->attachment_id, '_wp_attachment_image_alt', true ) ) );
		} elseif ( 'none' === $mode->args['alt'] ) {
			$alt = '';
		} else {
			$alt = $mode->args['alt'];
		}

		return apply_filters( 'ari_responsive_image_alt', $alt, $mode->attachment_id, $mode->args );
	}

	/**
	 * Get caption of image
	 *
	 * @return mixed|void
	 * @author Alexandre Sadowski
	 */
	protected function get_caption( $mode ) {
		$legend = ! empty( $mode->args['caption'] ) ? $mode->args['caption'] : wp_get_attachment_caption( $mode->attachment_id );

		return apply_filters( 'ari_responsive_image_caption', $legend, $mode->attachment_id, $mode->args );
	}

	/**
	 * Get default img path when empty HTML from WP
	 *
	 * @return string|\WP_Error
	 * @author Alexandre Sadowski
	 */
	protected function get_default_img_path( $mode ) {
		if ( ! isset( $mode->args['data-location'] ) ) {
			return new \WP_Error( 'ari-error', 'No data-location found in arguments' );
		}

		/**
		 * @var $locations Image_Locations
		 */
		$locations      = Image_Locations::get_instance();
		$location_array = $locations->get_location( $mode->args['data-location'] );
		if ( empty( $location_array ) ) {
			return new \WP_Error( 'ari-error', "Location ' . $mode->args['data-location'] . ' not found in image-locations file" );
		}

		$location_array = array_shift( $location_array );
		if ( ! isset( $location_array->default_img ) || empty( $location_array->default_img ) ) {
			return new \WP_Error( 'ari-error', "No default_img ( ' . $location_array->default_img . ' ) attribute in json for location : ' . $mode->args['data-location'] . '" );
		}

		$default_path     = apply_filters( 'ari_responsive_image_default_img_path', '/dist/images/', $mode->args );
		$img_default_name = apply_filters( 'ari_responsive_image_default_img_name', $location_array->default_img, $mode->args );
		$img_path         = $default_path . $img_default_name;

		if ( ! is_readable( get_stylesheet_directory() . $img_path ) ) {
			return new \WP_Error( 'ari-error', "Default img (' . $img_default_name . ') not exists or not readable" );
		}

		return $img_path;
	}

	/**
	 * @param $location_array
	 * @param $html
	 *
	 * @return array|\WP_Error
	 * @author Alexandre Sadowski
	 */
	protected function check_tpl( $location_array, $mode, $tpl_name ) {
		if ( ! is_array( $location_array ) ) {
			return new \WP_Error( 'ari-error', "Location ' . $mode->args['data-location'] . ' not found in image-locations file" );
		}

		$location_array = reset( $location_array );
		if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
			return new \WP_Error( 'ari-error', "No srcsets found or not V2 JSON for location (' . $mode->args['data-location'] . ')" );
		}

		$main_tpl = ARI_JSON_DIR . 'tpl/' . $tpl_name;

		//Check if default tpl is overloaded
		if ( isset( $mode->args['data-tpl'] ) && ! empty( $mode->args['data-tpl'] ) ) {
			$main_tpl_name = $mode->args['data-tpl'];
			$main_tpl      = ARI_JSON_DIR . 'tpl/' . $mode->args['data-tpl'] . '.tpl';
		} elseif ( ( isset( $mode->args['data-mode'] ) && 'picture' === $mode->args['data-mode'] ) && ( isset( $mode->args['data-caption'] ) && ( '1' === $mode->args['data-caption'] || true === $mode->args['data-caption'] ) ) && ! empty( $this->get_caption( $mode ) ) ) {
			$main_tpl_name = 'default-picture-nolazyload-caption';
			$main_tpl      = ARI_JSON_DIR . 'tpl/default-picture-nolazyload-caption.tpl';
		} elseif ( ( isset( $mode->args['data-caption'] ) && ( '1' === $mode->args['data-caption'] || true === $mode->args['data-caption'] ) ) && ! empty( $this->get_caption( $mode ) ) ) {
			$main_tpl_name = 'default-picture-caption';
			$main_tpl      = ARI_JSON_DIR . 'tpl/default-picture-caption.tpl';
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

		//Check if default tpl is overloaded
		$location_tpl = ARI_JSON_DIR . 'tpl/' . $mode->args['data-location'] . '.tpl';

		if ( ! is_readable( $location_tpl ) ) {
			return new \WP_Error( 'ari-error', "Location tpl not exists or not readable (' . $mode->args['data-location'] . ')" );
		}

		$handle           = fopen( $location_tpl, 'r' );
		$location_content = fread( $handle, filesize( $location_tpl ) );
		fclose( $handle );
		if ( empty( $location_content ) ) {
			return new \WP_Error( 'ari-error', "Empty location tpl : (' . $mode->args['data-location'] . ')" );
		}

		return array( 'location_content' => $location_content, 'main_content' => $main_content );
	}

}