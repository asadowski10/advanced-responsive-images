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

	/**
	 * @var int
	 */
	protected static $priority = 12;

	protected function init() {	}


}