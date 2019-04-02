<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\CDECookieCache;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;

/**
 * Class CookieCSRFTokenProvider
 * @package Ixolit\CDE\Form
 */
class CookieCSRFTokenProvider implements CSRFTokenProvider {

	const COOKIE_NAME_CSRF_TOKEN = 'csrf-token';

	/**
	 * @var RequestAPI
	 */
	private $requestAPI;

	/**
	 * @var ResponseAPI
	 */
	private $responseAPI;

    /**
     * @var string
     */
	private $storedToken;

    /**
     * @var string
     */
	private $nextToken;

	public function __construct(
		RequestAPI $requestAPI,
		ResponseAPI $responseAPI
	) {
		$this->requestAPI = $requestAPI;
		$this->responseAPI = $responseAPI;

		$this->storedToken = $this->readCookieToken();
		$this->nextToken = (($this->requestAPI->getHeader('X-Requested-With') == 'XMLHttpRequest') && $this->storedToken) ? $this->storedToken : $this->generateToken();

		CDECookieCache::getInstance()->write(self::COOKIE_NAME_CSRF_TOKEN, $this->getCSRFToken(), CDECookieCache::COOKIE_TIMEOUT_SESSION, null, null, false, true);
	}

	private function readCookieToken() {
	    $token = CDECookieCache::getInstance()->read(self::COOKIE_NAME_CSRF_TOKEN);

        if (!strlen($token)) {
            //will be invalid, because we regenerate it
            $token = $this->generateToken();
        }

        return $token;
	}

	private function generateToken() {
	    //we assume 32 bit here on purpose
	    return \md5(\mt_rand(0, defined('PHP_INT_MAX') ? PHP_INT_MAX : 2147483647));
	}

    /**
     * Returns the next csrf token
     * @return string
     */
	public function getCSRFToken() {
	    return $this->nextToken;
	}

    /**
     * Returns the previously generated token which got stored as cookie value
     * @return string
     */
	public function getStoredCSRFToken(){
        return $this->storedToken;
    }

}