<?php

namespace ARI;

use ARI\Modes\Lazysize;
use ARI\Modes\Picture;
use ARI\Modes\Srcset;
use ARI\Modes\Picture_Lazyload;
use ARI\Modes\Native;

/**
 * The purpose of the modes class is to return the final render
 *
 * Class Modes
 * @package ARI
 */
class Modes {
	/**
	 * Use the trait
	 */
	use Singleton;

	protected function init() {

	}

	/**
	 * @return \ARI\Modes\Mode
	 */
	public function get_mode( $args ) {
		$mode = ARI_MODE;
		if ( isset( $args['data-mode'] ) && ! empty( $args['data-mode'] ) ) {
			$mode = $args['data-mode'];
		}

		switch ( $mode ) {
			case 'srcset':
				/**
				 * @var $srcset Srcset
				 */
				$srcset = Srcset::get_instance();
				$srcset->set_args( $args );

				return $srcset;
			case 'lazysize':
				/**
				 * @var $lazy Lazysize
				 */
				$lazy = Lazysize::get_instance();
				$lazy->set_args( $args );

				return $lazy;
			case 'picture':
				/**
				 * @var $picture Picture
				 */
				$picture = Picture::get_instance();
				$picture->set_args( $args );

				return $picture;
			case 'picture_lazyload':
				/**
				 * @var $picture_lazyload Picture_Lazyload
				 */
				$picture_lazyload = Picture_Lazyload::get_instance();
				$picture_lazyload->set_args( $args );

				return $picture_lazyload;
			case 'native':
				/**
				 * @var $native_lazyload Native
				 */
				$native_lazyload = native::get_instance();
				$native_lazyload->set_args( $args );

				return $native_lazyload;
			default:
				return false;
		}
	}

}