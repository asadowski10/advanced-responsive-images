<?php
if ( ! function_exists( 'bea_get_attachment_image' ) ) {
	function bea_get_attachment_image( $attachment_id, $size = 'thumbnail', $icon = false, $attr = [] ) {
		if ( ! isset( $attr['data-location'] ) ) {
			$attr['data-location'] = 'No location filled in';

			return wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
		}

		/**
		 * @var $locations \ARI\Image_Locations
		 */
		$locations      = \ARI\Image_Locations::get_instance();
		$location_array = $locations->get_location( $attr['data-location'] );
		if ( empty( $location_array ) ) {
			$args['data-location'] = 'No location found in source file';

			return wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
		}

		/**
		 * @var $mode ARI\Modes
		 */
		$mode = ARI\Modes::get_instance();
		try {
			$_mode_instance = $mode->get_mode( $attr );
			if ( false === $_mode_instance ) {
				return wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
			}
			$html = wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
			$_mode_instance->set_attachment_id( $attachment_id );

			return $_mode_instance->render_image( $html );
		} catch ( \Exception $e ) {
			$args['data-location'] = $e->getMessage();
		}

		return wp_get_attachment_image( $attachment_id, $size, $icon, $attr );
	}
}