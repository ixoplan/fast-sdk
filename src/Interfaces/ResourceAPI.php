<?php

namespace Ixolit\CDE\Interfaces;

use Ixolit\CDE\Exceptions\ResourceNotFoundException;

/**
 * This API gives access to the resource functionality of the CDE (files, static content)
 */
interface ResourceAPI {

	/**
	 * Returns the full URL for the static content specified by path
	 *
	 * @param string $path
	 *
	 * @return string
	 *
	 * @throws ResourceNotFoundException
	 */
	function getStaticUrl($path);
}