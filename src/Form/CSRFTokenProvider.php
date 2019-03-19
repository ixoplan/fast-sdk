<?php

namespace Ixolit\CDE\Form;

interface CSRFTokenProvider {
	/**
	 * @return string
	 */
	public function getCSRFToken();

    /**
     * @return string
     */
	public function getStoredCSRFToken();
}
