<?php

namespace Ixolit\CDE\Interfaces;

use Ixolit\CDE\Exceptions\GeoLookupFailedException;
use Ixolit\CDE\WorkingObjects\GeoLookupResponse;

interface GeoLookupAPI {
	/**
	 * @param string|null $ip
	 *
	 * @return GeoLookupResponse
	 *
	 * @throws GeoLookupFailedException
	 */
	public function lookup($ip = null);
}
