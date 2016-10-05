<?php
namespace ARI;

use ARI\Modes\Lazysize;
use ARI\Modes\Srcset;

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
	 * @return \ARI\Modes
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
				return false;
			case 'picture_lazyload':
				return false;
			default:
				return false;
		}
	}

}