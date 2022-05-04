<?php

namespace Ixolit\CDE;

use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\CookieSetFailedException;
use Ixolit\CDE\Exceptions\HeaderSetFailedException;
use Ixolit\CDE\Exceptions\InvalidStatusCodeException;
use Ixolit\CDE\Interfaces\ResponseAPI;
use Psr\Http\Message\ResponseInterface;

class CDEResponseAPI implements ResponseAPI {

    /**
     * When setting a cookie on $domain, the cookie will be deleted on all domains that are mapped for the specific $domain
     * In that example, domainA and domainB. This can be used, to ensure only one cookie with the same name exists, knowing all possibly used (sub-)domains eg. .domain, .abc.domain, more.abc.domain
     * @var array
     *      $domain => [ domainA, domainB, ..... ]
     *
     */
    private $cookieDeletionDomainMap = array();

    /**
     * @var ?string
     */
    private $defaultCookieDomain = null;

    public function __construct()
    {
        //backwards compatibility handling
        $this->setDefaultCookieDomain($this->getLegacyDefaultCookieDomain());

        //for storing cookies on the current domain per default, set the defaultCookieDomain to null.
        //e.g. within the doStart of the custom PageContext call Page::get()->getResponseApi()->setDefaultCookieDomain(null);
        //this also removes the legacy cookie(s), when storing a new cookie value
    }

    public function getLegacyDefaultCookieDomain() {
        return '.' . str_replace('www.', '', getVhost());
    }

    public function setDefaultCookieDomain($domain) {
        $this->defaultCookieDomain = $domain;

        if ($domain === null && !array_key_exists($domain, $this->cookieDeletionDomainMap)) {
            $this->cookieDeletionDomainMap[null] = [$this->getLegacyDefaultCookieDomain()];
            unset($this->cookieDeletionDomainMap[$this->getLegacyDefaultCookieDomain()]);
        }

        if ($domain === $this->getLegacyDefaultCookieDomain() && !array_key_exists($domain, $this->cookieDeletionDomainMap)) {
            $this->cookieDeletionDomainMap[$domain] = [null];
            unset($this->cookieDeletionDomainMap[null]);
        }

        return $this;
    }

    public function getDefaultCookieDomain() {
        return $this->defaultCookieDomain;
    }

    /**
     * When setting a cookie for $domain, and a key with the $domain exists, the cookie will also be deleted on all domains that are mapped for the specific $domain
     * In that example, domainA and domainB. This can be used, to ensure only one cookie with the same name exists, knowing all possibly used (sub-)domains eg. .domain, .abc.domain, more.abc.domain
     * @var array
     *      $domain => [ domainA, domainB, ..... ]
     *
     * @param array $map
     * @return $this
     */
    public function setCookieDeletionDomainMap(array $domainMap) {
        $this->cookieDeletionDomainMap = $domainMap;
        return $this;
    }

	/**
	 * {@inheritdoc}
	 */
	public function redirectTo($location, $permanent = false) {
		if (!\function_exists('redirectTo')) {
			throw new CDEFeatureNotSupportedException('redirectTo');
		}
		redirectTo($location, $permanent);
	}

	/**
	 * {@inheritdoc}
	 */
	public function redirectToPage($page, $lang = null, $permanent = false, $abortRendering = true) {
		if (!\function_exists('redirectToPage')) {
			throw new CDEFeatureNotSupportedException('redirectToPage');
		}
		redirectToPage($page, $lang, $permanent);
		if ($abortRendering) {
			exit;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContentType($contentType) {
		if (!\function_exists('setContentType')) {
			throw new CDEFeatureNotSupportedException('setContentType');
		}
		\setContentType($contentType);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStatusCode($statusCode) {
		if (!\function_exists('setStatusCode')) {
			throw new CDEFeatureNotSupportedException('setStatusCode');
		}
		if (!\setStatusCode($statusCode)) {
			throw new InvalidStatusCodeException($statusCode);
		}
	}

	private function setCookieInternal($name, $value, $maxAge = 0, $path = null, $domain = null, $secure = false, $httponly = false) {
	    if ($domain === null){
	        $domain = $this->getDefaultCookieDomain();
	    }
        $result = \setCookieAdvanced($name, $value, $maxAge, $path, $domain, $secure, $httponly);
        if (!empty($this->cookieDeletionDomainMap[$domain])) {
            foreach($this->cookieDeletionDomainMap[$domain] as $_domain) {
                if ($_domain !== $domain) {
                    //delete cookie
                    \setCookieAdvanced($name, null, -1, $path, $_domain, $secure, $httponly);
                }
            }
        }
        return $result;
	}

	/**
	 * Sets a HTTP cookie in the response.
	 *
	 * @param string $name
	 * @param string $value
	 * @param int    $maxAge in seconds. 0 means session cookie.
	 * @param string $path
	 * @param string $domain When null the $defaultCookieDomain will be used as domain.
     *                       Set the $defaultCookieDomain to null, to explicitly set the cookie on the currently used domain
	 * @param bool   $secure
	 * @param bool   $httponly
	 *
	 * @throws CookieSetFailedException
	 */
	public function setCookie($name, $value, $maxAge = 0, $path = null, $domain = null, $secure = false, $httponly = false) {
		if (!\function_exists('setCookie')) {
			throw new CDEFeatureNotSupportedException('setCookie');
		}
		if (!$this->setCookieInternal($name, $value, $maxAge, $path, $domain, $secure, $httponly)) {
			throw new CookieSetFailedException($name, $value, $maxAge, $path, $domain, $secure, $httponly);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function setHeader($name, $value) {
		if (!\function_exists('setHeader')) {
			throw new CDEFeatureNotSupportedException('setHeader');
		}
		if (!\setHeader($name, $value)) {
			throw new HeaderSetFailedException($name, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function sendPSR7(ResponseInterface $response) {
		$this->setStatusCode($response->getStatusCode());
		$headers = $response->getHeaders();

		foreach ($headers as $header => $content) {
			switch (strtolower($header)) {
				case 'content-type':
					$this->setContentType(\implode(',', $content));
					break;
				case 'location':
					$this->redirectTo(\implode(',', $content), ($response->getStatusCode() == 301 ? true : false));
					break;
				case 'set-cookie':
					foreach ($content as $cookie) {
						$cookieData = [];
						$parts      = explode(';', $cookie);
						$maxAge     = 0;
						foreach ($parts as $part) {
							$partComponents = explode('=', $part);
							$key            = urldecode(trim($partComponents[0]));
							if (isset($partComponents[1])) {
								$value = urldecode(trim($partComponents[1]));
							} else {
								$value = true;
							}
							switch (strtolower($key)) {
								case 'expires':
									$maxAge = strtotime($value) - time();
									break;
								case 'domain':
								case 'path':
								case 'secure':
								case 'httponly':
									//ignore
									break;
								default:
									$cookieData[$key] = $value;
									break;
							}
						}
						foreach ($cookieData as $key => $value) {
							$this->setCookie($key, $value, $maxAge);
						}
					}
					break;
				default:
					throw new CDEFeatureNotSupportedException('Sending header type ' . $header .
						' is not supported');
			}
		}

		echo $response->getBody();
		exit;
	}
}
