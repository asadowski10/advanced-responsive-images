<?php
class ARI_Image{
	
	private static $locations;
	private static $image_sizes;
	private static $hooks;
	public static $allowed_ext = array('.jpg', '.gif', '.png');
	
	/**
	 * Construct
	 */
	public function __construct() {		
		add_action( 'after_setup_theme', array( __CLASS__, 'load_image_sizes' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'load_locations' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'load_hooks' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'add_image_sizes' ) );
	}
	/*
	 * Load JSON Image Sizes
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function load_image_sizes(){
		$tpl = ARI_Plugin::get_template( 'image-sizes' );
		if ( empty( $tpl ) ) {
			return false;
		}
		
		$file_content = file_get_contents( $tpl );
		$result = json_decode( $file_content );
		if( is_array( $result ) && !empty( $result ) ){
			self::$image_sizes = $result;
		}
	}
	
	/*
	 * Load locations JSON
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function load_locations(  ){
		$tpl = ARI_Plugin::get_template( 'image-locations' );
		if ( empty( $tpl ) ) {
			return false;
		}
		
		$file_content = file_get_contents( $tpl );
		$result = json_decode( $file_content );
		if( is_array( $result ) && !empty( $result ) ){
			self::$locations = $result;
		}
	}
	
	/*
	 * Load hooks JSON
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function load_hooks(  ){
		$tpl = ARI_Plugin::get_template( 'image-hooks' );
		if ( empty( $tpl ) ) {
			return false;
		}
		
		$file_content = file_get_contents( $tpl );
		$result = json_decode( $file_content );
		if( is_array( $result ) && !empty( $result ) ){
			self::$hooks = $result;
		}
	}
	
	/*
	 * Add Image Sizes in WP
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function add_image_sizes() {
		if( !is_array(self::$image_sizes) || empty(self::$image_sizes) ) {
			return false;
		}
		
		foreach ( self::$image_sizes as $key => $value ) {
			foreach( $value as $name => $attributes ){
				if( empty($attributes) ){
					continue;
				}
				
				if( isset($attributes->width) && !empty( $attributes->width ) && isset($attributes->height) && !empty( $attributes->height ) && isset($attributes->crop) ){
					add_image_size($name, $attributes->width, $attributes->height, $attributes->crop);
				}
			}
		}
		
		return true;
	}
	
	/*
	 * Get attributes of a location
	 * 
	 * @value string $location The location name used in JSON
	 * @return array|false $attributes Array of attributes in JSON : srcset, size, class, default_src...
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function get_location( $location = ''){
		if( !is_array(self::$locations) | empty(self::$locations) ){
			return false;
		}
		
		foreach ( self::$locations as $key => $value ) {
			foreach( $value as $name => $attributes ){
				if( $name == $location ){
					return $attributes;
				}
			}
		}
		
		return false;
	}
	
	
	/*
	 * Get attributes of an image size
	 * 
	 * @value string $location The location name used in JSON
	 * @return array|false $attributes Array of attributes in JSON : width, height, crop
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function get_image_size( $location = ''){
		if( !is_array(self::$image_sizes) | empty(self::$image_sizes) ){
			return false;
		}
		
		foreach ( self::$image_sizes as $key => $value ) {
			foreach( $value as $name => $attributes ){
				if( $name == $location ){
					return $attributes;
				}
			}
		}
		return false;
	}
	
	/*
	 * Get attributes of a hook
	 * 
	 * @value string $hook The Hook name used in JSON
	 * @return array|false $attributes Array of attributes in JSON : srcset, size, class, default_src...
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function get_hook( $hook = ''){
		if( !is_array(self::$hooks) | empty(self::$hooks) ){
			return false;
		}
		
		foreach ( self::$hooks as $key => $value ) {
			foreach( $value as $name => $attributes ){
				if( $name == $hook ){
					return $attributes;
				}
			}
		}
		
		return false;
	}
}