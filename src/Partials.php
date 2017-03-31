<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\PartialNotFoundException;

/**
 * Helper class for accessing partials
 */
class Partials {

	/**
	 * Try to load a partial from the layout, or if it doesn't exist, from the vhost.
	 *
	 * @param string $name
	 * @param array  $data
	 *
	 * @throws PartialNotFoundException
	 */
	public static function load($name, $data = []) {
		$tryFiles = [];
		if (function_exists('getCurrentLayout') && \getCurrentLayout()) {
			$tryFiles[] =  '/vhosts/' . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.php';
		}
		$tryFiles[] = '/vhosts/' . getEffectiveVhost() . '/partials/' . $name . '.php';

		foreach ($tryFiles as $tryFile) {
			if (\file_exists($tryFile)) {
				\extract($data);
				include($tryFile);
				return;
			}
		}

		throw new PartialNotFoundException($name);
	}
}
