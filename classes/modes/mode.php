<?php
namespace ARI\Modes;

/**
 * Abstract Class Mode
 * @package ARI\Modes
 */
abstract class Mode {
	/**
	 * Use the trait
	 */
	use \ARI\Singleton;

	/**
	 * @var int
	 */
	protected static $priority = 12;

	protected function init() {	}


}