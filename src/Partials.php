<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\PartialNotFoundException;
use Ixolit\CDE\WorkingObjects\ViewModel;

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

	    static $cache;

	    $language = getCurrentLanguage();
	    $cacheKey = $name . $language;

		$tryFiles = [];
		if (!empty($cache[$cacheKey])) {
		    $tryFiles = [$cache[$cacheKey]];
		}

		if (empty($tryFiles)) {
            if (function_exists('getCurrentLayout') && \getCurrentLayout()) {
                $tryFiles[] = '/vhosts/' . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.' . $language . '.php';
                $tryFiles[] = '/vhosts/' . getEffectiveVhost() . '/layouts/' . getCurrentLayout() . '/partials/' . $name . '.php';
            }
            $tryFiles[] = '/vhosts/' . getEffectiveVhost() . '/partials/' . $name . '.' . $language . '.php';
            $tryFiles[] = '/vhosts/' . getEffectiveVhost() . '/partials/' . $name . '.php';
        }

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
