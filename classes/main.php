<?php
class ARI_Main{
	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp', array(__CLASS__, 'register_assets' ) );
		add_action( 'wp_head', array( __CLASS__, 'enqueue_assets' ) );
		
		// Hook WP function for add new attribute
		add_filter('wp_get_attachment_image_attributes', array(__CLASS__, 'get_attributes'),10,2 );
		add_filter('intermediate_image_sizes_advanced',array(__CLASS__, 'ari_dynimg_image_sizes_advanced' ) );
	}
	
	public static function register_assets(){
		if( defined( 'SCRIPT_DEBUG' ) && constant( 'SCRIPT_DEBUG' ) === true ) {
			$js_file = "picturefill.js";
		} else {
			$js_file = "picturefill.min.js";
		}
		
		wp_register_script( 'picturefill', ARI_URL.'assets/js/'.$js_file, array( ), '2.2.0', true );
	}
	
	public static function enqueue_assets(){
		wp_enqueue_script( 'picturefill' );
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
	public static function get_attributes( $args = array(),WP_Post $attachment ){
		if( !isset($args['data-location']) ){
			return $args;
		}
		
		$location_array = ARI_Image::get_location( $args['data-location'] );
		if( empty( $location_array ) ){
			$args['data-location'] = 'No location found';
		} else {
			$srcset_attrs = array();
			$args['sizes'] = '100vw';
			foreach( $location_array as $location ){
				if( !isset( $location->size ) || empty( $location->size ) ){
					continue;
				}

				$img = wp_get_attachment_image_src( $attachment->ID, (array) ARI_Image::get_image_size( $location->size ) );
				if( empty($img) ){
					continue;
				}

				if( isset( $location->class ) && !empty( $location->class ) ){
					$args['class'] = $args['class']. ' '.$location->class;
				}

				$srcset_attrs[] = $img[0].' '.$location->srcset;
			}
		
			if( !empty($srcset_attrs) ){
				$args['srcset'] = implode(', ', $srcset_attrs);
			}
		}
		
		return $args;
	}
	
	public static function ari_dynimg_image_sizes_advanced($sizes) {
		if( !class_exists( 'WP_Thumb' ) ) {
			return $sizes;
		}

		global $dynimg_image_sizes;

		// save the sizes to a global, because the next function needs them to lie to WP about what sizes were generated
		$dynimg_image_sizes = $sizes;

		// Get all editor sizes
		$default_sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __('Thumbnail'),
			'medium'    => __('Medium'),
			'large'     => __('Large'),
		) );

		foreach( $default_sizes as $size => $name ) {
			if( !isset( $sizes[ $size ] ) ) {
				continue;
			}
			$default_sizes[ $size ] = $sizes[ $size ];
		}

		// tell WordPress to generate only default sizes
		return $default_sizes;
	}
}