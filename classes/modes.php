<?php
namespace ARI;

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
	 * @return \ARI\Mode
	 */
	public static function get_render( $mode ) {
		switch ( $mode ) {
			case 'srcset':
				return '';
				break;
			case 'lazysize':
				return '';
				break;
			case 'lazysize':
				return '';
				break;
		}
	}

}