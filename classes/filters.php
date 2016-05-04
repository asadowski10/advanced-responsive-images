<?php
namespace ARI;

/**
 * The purpose of the filters class is to hook WordPress to implement our code like :
 *  - HTML format
 *  - Default IMG
 *
 * Class Filters
 * @package ARI
 */
class Filters {
	/**
	 * Use the trait
	 */
	use Singleton;

	protected function init() {
		// Hook WP function for add new attribute
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'get_attributes' ), 10, 2 );
		add_filter( 'post_thumbnail_html', array( $this, 'bea_default_img' ), 10, 5 );
	}

	/*
	 * Add filter on "wp_get_attachment_image_attributes" to add srcset attributes
	 *
	 * @value array $args attributes for the image markup.
	 * @value object $attachment WP_Post of attachment
	 * @return array $attributes attributes for the image markup.
	 *
	 * @author Alexandre Sadowski
	 */
	public function get_attributes( $args = array(), \WP_Post $attachment ) {
		if ( ! isset( $args['data-location'] ) ) {
			return $args;
		}
		$location_array = self::get_location( $args['data-location'] );
		if ( empty( $location_array ) ) {
			$args['data-location'] = 'No location found';
		} else {
			$location_array = reset( $location_array );
			$srcset_attrs   = array();
			if ( ! isset( $location_array->srcsets ) || empty( $location_array->srcsets ) ) {
				$args['data-location'] = 'No srcsets found or not V2 JSON';
			} else {
				// add lazyload on all medias
				if ( defined( 'BEA_LAZYSIZE' ) && true === BEA_LAZYSIZE ) {
					$args['class'] = $args['class'] . ' lazyload';
				}
				foreach ( $location_array->srcsets as $location ) {
					if ( ! isset( $location->size ) || empty( $location->size ) ) {
						continue;
					}
					$img = wp_get_attachment_image_src( $attachment->ID, (array) self::get_image_size( $location->size ) );
					if ( empty( $img ) ) {
						continue;
					}
					if ( isset( $location->class ) && ! empty( $location->class ) ) {
						$args['class'] = $args['class'] . ' ' . $location->class;
					}
					$srcset_attrs[] = $img[0] . ' ' . $location->srcset;
				}
			}
			//Get img_base size for base SRC
			if ( isset( $location_array->img_base ) && ! empty( $location_array->img_base ) ) {
				$img = wp_get_attachment_image_src( $attachment->ID, (array) self::get_image_size( $location_array->img_base ) );
				if ( is_array( $img ) && ! empty( $img ) ) {
					$args['src'] = reset( $img );
				}
			}
			if ( ! empty( $srcset_attrs ) && defined( 'BEA_LAZYSIZE' ) ) {
				if ( false === BEA_LAZYSIZE ) {
					$args['srcset'] = implode( ', ', $srcset_attrs );
				} else {
					$args['data-srcset'] = implode( ', ', $srcset_attrs );
					$args['src']         = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
				}
			}
		}

		return $args;
	}

	/**
	 * Add default image on post_thumbnail empty
	 *
	 * @author Alexandre Sadowski
	 */
	public function bea_default_img( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( ! empty( $html ) ) {
			return $html;
		}
		if ( ! isset( $attr['data-location'] ) ) {
			return $html;
		}
		$location_array = self::get_location( $attr['data-location'] );
		if ( empty( $location_array ) ) {
			return $html;
		}
		$location_array = array_shift( $location_array );
		if ( ! isset( $location_array->default_img ) || empty( $location_array->default_img ) ) {
			return $html;
		}
		$default_path = apply_filters( 'bea_responsive_image_default_img_path', '/assets/img/default/', $attr );
		$img_path     = $default_path . $location_array->default_img;
		if ( ! is_file( get_stylesheet_directory() . $img_path ) ) {
			return $html;
		}
		$classes   = array( 'attachment-thumbnail', 'wp-post-image' );
		$classes[] = isset( $attr['class'] ) ? $attr['class'] : '';
		// add lazyload on all medias
		if ( defined( 'BEA_LAZYSIZE' ) && true === BEA_LAZYSIZE ) {
			$classes[] = 'lazyload';

			return '<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-srcset="' . get_stylesheet_directory_uri() . $img_path . '" class="' . implode( ' ', $classes ) . '">';
		}

		return '<img src="' . get_stylesheet_directory_uri() . $img_path . '" class="' . implode( ' ', $classes ) . '">';
	}

	public function render( $location ) {
		$render = BeaMode::getRender( $this->mode );
		$html   = $render->renderImage( $location );
	}
}