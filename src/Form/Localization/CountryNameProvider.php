<?php

namespace Ixolit\CDE\Form\Localization;

interface CountryNameProvider {
	/**
	 * @param string $code
	 *
	 * @return string
	 */
	public function getCountryName($code);
}