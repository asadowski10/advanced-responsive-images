<?php
namespace ARI;

use ARI\Modes\Lazysize;

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
				return '';
				break;
			case 'lazysize':
				/**
				 * @var $lazy Lazysize
				 */
				$lazy = Lazysize::get_instance();
				$lazy->set_args( $args );

				return $lazy;
				break;
			case 'picture':
				return '';
				break;
			case 'picture_lazyload':
				return ''; // return render();
				break;
			default:
				throw new \Exception( sprintf( '%s post type does not match model post type %s', get_post_type( $object ), $this->post_type ) );
		}
	}

}