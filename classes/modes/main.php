<?php
namespace ARI\Modes;

/**
 * Abstract Class Main
 * @package ARI\Modes
 */
abstract class Main {
	/**
	 * Use the trait
	 */
	use \ARI\Singleton;

	protected function init() {	}

	abstract public function get_locations();

	abstract public function get_location();

}