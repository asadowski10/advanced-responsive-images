<?php
class ARI_Plugin{
	/**
	 * Construct
	 */
	public function __construct() {
		
	}
	
	/**
	 * Get template file depending on theme
	 * 
	 * @param (string) $tpl : the template name
	 * @return (string) the file path | false
	 * 
	 * @author Alexandre Sadowski
	 */
	public static function get_template( $tpl = '' ) {
		if ( empty( $tpl ) ) {
			return false;
		}

		if ( defined( 'STYLESHEETPATH' ) && is_file( STYLESHEETPATH . '/assets/conf-img/' . $tpl . '.json' ) ) {// Use custom template from child theme
			return ( STYLESHEETPATH . '/assets/conf-img/' . $tpl . '.json' );
		} elseif ( defined( 'TEMPLATEPATH' ) && is_file( TEMPLATEPATH . '/assets/conf-img/' . $tpl . '.json' ) ) {// Use custom template from parent theme
			return (TEMPLATEPATH . '/assets/conf-img/' . $tpl . '.json' );
		} elseif ( defined( 'ARI_DIR' ) && is_file( ARI_DIR . '/assets/conf-img/' . $tpl . '.json' ) ) {// Use builtin template
			return ( ARI_DIR . '/assets/conf-img/' . $tpl . '.json' );
		}

		return false;
	}
}



if( has_post_thumbnail() ){
	the_post_thumbnail( 'thumbnail', array( 'data-location' => 'news' ) );
}