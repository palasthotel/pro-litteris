<?php

namespace Palasthotel\ProLitteris;

/**
 * @property Plugin plugin
 */
abstract class _Component {
	/**
	 * _Component constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
		$this->onCreate();
	}

	/**
	 * overwrite this method in component implementations
	 */
	public function onCreate(){

	}
}
