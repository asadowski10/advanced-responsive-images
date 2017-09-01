<?php

namespace ARI;

use ARI\Modes\Lazysize;
use ARI\Modes\Lazysize_Front;
use ARI\Modes\Srcset;
use ARI\Modes\Srcset_Front;
use ARI\Modes\Picture_Lazyload;
use ARI\Modes\Picture_Lazyload_Front;

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

		if ( defined( 'ARI_CONTEXT' ) && 'front' === ARI_CONTEXT ) {
			$mode = $mode . '_front';
		}

		switch ( $mode ) {
			case 'srcset':
				/**
				 * @var $srcset Srcset
				 */
				$srcset = Srcset::get_instance();
				$srcset->set_args( $args );

				return $srcset;
			case 'srcset_front':
				/**
				 * @var $srcset Srcset
				 */
				$srcset = Srcset_Front::get_instance();
				$srcset->set_args( $args );

				return $srcset;
			case 'lazysize':
				/**
				 * @var $lazy Lazysize
				 */
				$lazy = Lazysize::get_instance();
				$lazy->set_args( $args );

				return $lazy;
			case 'lazysize_front':
				/**
				 * @var $lazy Lazysize
				 */
				$lazy_front = Lazysize_Front::get_instance();
				$lazy_front->set_args( $args );

				return $lazy_front;
			case 'picture':
				return false;
			case 'picture_lazyload':
				/**
				 * @var $picture_lazyload Picture_Lazyload
				 */
				$picture_lazyload = Picture_Lazyload::get_instance();
				$picture_lazyload->set_args( $args );

				return $picture_lazyload;
			case 'picture_lazyload_front':
				/**
				 * @var $picture_lazyload Picture_Lazyload_Front
				 */
				$picture_lazyload_front = Picture_Lazyload_Front::get_instance();
				$picture_lazyload_front->set_args( $args );

				return $picture_lazyload_front;
			default:
				return false;
		}
	}

}