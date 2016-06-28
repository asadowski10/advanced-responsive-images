<?php
namespace ARI;

/**
 *
 * Interface Mode
 * @package ARI
 */
interface Mode_Interface {
	public function render_image();

	public function add_filters();

	public function set_args( $args );

	public function set_attachment_id( $id );
}