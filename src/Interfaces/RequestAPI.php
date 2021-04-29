<?php

namespace Ixolit\CDE\Interfaces;

use Ixolit\CDE\Exceptions\CookieNotSetException;
use Ixolit\CDE\Exceptions\InformationNotAvailableInContextException;
use Ixolit\CDE\WorkingObjects\Cookie;
use Ixolit\CDE\WorkingObjects\INETAddress;
use Ixolit\CDE\WorkingObjects\Layout;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The request API in the CDE gives access to the current HTTP request. Some functionality may not be available
 * depending on the current context.
 */
interface RequestAPI {
	/**
	 * Returns the scheme for the current request, e.g. http or https
	 *
	 * @return string
	 */
	public function getScheme();

	/**
	 * Returns the resolved, normalized vhost name for the current request for inclusion in URLs
	 *
	 * @return string
	 *
	 * @throws InformationNotAvailableInContextException
	 */
	public function getVhost();

	/**
	 * Returns the resolved, normalized vhost name for the current request for inclusion in path names.
	 *
	 * @return string
	 *
	 * @throws InformationNotAvailableInContextException
	 */
	public function getEffectiveVhost();

	/**
	 * Returns the fully qualified domain name for the current request, e.g. docs.ixoplan.com
	 *
	 * @return string
	 *
	 * @throws InformationNotAvailableInContextException
	 */
	public function getFQDN();

	/**
	 * Returns the value of the cookie with the given name. If the request contains multiple cookies with the same
	 * name, the value of the first (request header order) cookie with the given name will be returned.
	 *
	 * @param string $name
	 *
	 * @return Cookie
	 *
	 * @throws CookieNotSetException
	 */
	public function getCookie($name);

	/**
	 * Returns all cookies contained in the current request.
	 *
	 * @return Cookie[]
	 */
	public function getCookies();

	/**
	 * Get language for the current request. Defaults to the default language on an error page.
	 *
	 * @return string
	 */
	public function getLanguage();

	/**
	 * Returns the current layout.
	 *
	 * @return Layout
	 *
	 * @throws InformationNotAvailableInContextException if no layout information is available in the current context.
	 */
	public function getLayout();

	/**
	 * Returns the link to the current page, optionally for a different language.
	 *
	 * @param string|null $lang
	 *
	 * @return string
	 *
	 * @throws InformationNotAvailableInContextException if the page link is not available in the current context.
	 */
	public function getPageLink($lang = null);

	/**
	 * Returns the path of the current page. E.g. this function would return "/resources" for the url
	 *
	 * @return string
	 *
	 * @throws InformationNotAvailableInContextException if the page link is not available in the current context.
	 */
	public function getPagePath();

	/**
	 * Returns the client address that initiated the request.
	 *
	 * @return INETAddress
	 */
	public function getRemoteAddress();

	/**
	 * Returns a dictionary containing all request parameter/value pairs. Substitutes the $_REQUEST superglobal.
	 *
	 * @return array
	 */
	public function getRequestParameters();

	/**
	 * Returns a single request parameter. Substitutes the $_REQUEST superglobal.
	 *
	 * @param string $parameter
	 *
	 * @return string|null
	 */
	public function getRequestParameter($parameter);

	/**
	 * Returns a dictionary containing all request headers.
	 *
	 * @return array
	 */
	public function getHeaders();

	/**
	 * Returns a single header.
	 *
	 * @param string $header
	 *
	 * @return string|null
	 */
	public function getHeader($header);

	/**
	 * Returns a PSR-7 ServerRequestInterface object
	 *
	 * @return ServerRequestInterface
	 */
	public function getPSR7();
}
