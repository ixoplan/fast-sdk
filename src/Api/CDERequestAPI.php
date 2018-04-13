<?php

namespace Ixolit\CDE\Api;

use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\CookieNotSetException;
use Ixolit\CDE\Exceptions\InformationNotAvailableInContextException;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\PSR7\ServerRequest;
use Ixolit\CDE\PSR7\Uri;
use Ixolit\CDE\WorkingObjects\Cookie;
use Ixolit\CDE\WorkingObjects\INETAddress;
use Ixolit\CDE\WorkingObjects\Layout;

/**
 * Class CDERequestAPI
 *
 * @package Ixolit\CDE\Api
 */
class CDERequestAPI implements RequestAPI {

    /**
     * {@inheritdoc}
     */
    public function getScheme() {
        if (!\function_exists('getScheme')) {
            throw new CDEFeatureNotSupportedException('getScheme');
        }
        return getScheme();
    }

    /**
     * {@inheritdoc}
     */
    public function getVhost() {
        if (!\function_exists('getVhost')) {
            throw new CDEFeatureNotSupportedException('getVhost');
        }
        $vhost = getVhost();
        if ($vhost === null) {
            throw new InformationNotAvailableInContextException('vhost');
        }
        return $vhost;
    }

    /**
     * {@inheritdoc}
     */
    public function getEffectiveVhost() {
        if (!\function_exists('getEffectiveVhost')) {
            throw new CDEFeatureNotSupportedException('getEffectiveVhost');
        }
        $vhost = getEffectiveVhost();
        if ($vhost === null) {
            throw new InformationNotAvailableInContextException('effective vhost');
        }
        return $vhost;
    }

    public function getFQDN() {
        if (!\function_exists('getFQDN')) {
            throw new CDEFeatureNotSupportedException('getFQDN');
        }
        $fqdn = getFQDN();
        if ($fqdn === null) {
            throw new InformationNotAvailableInContextException('FQDN');
        }
        return $fqdn;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($name) {
        if (!\function_exists('getCookie')) {
            throw new CDEFeatureNotSupportedException('getCookie');
        }
        $value = getCookie($name);
        if ($value === null) {
            throw new CookieNotSetException($name);
        }

        return new Cookie(
            $name,
            $value
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCookies() {
        if (!\function_exists('getCookies')) {
            throw new CDEFeatureNotSupportedException('getCookies');
        }
        $cookies = getCookies();
        $result = [];
        foreach ($cookies as $name => $values) {
            foreach ($values as $value) {
                $result[] = new Cookie($name, $value);
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage() {
        if (!\function_exists('getCurrentLanguage')) {
            throw new CDEFeatureNotSupportedException('getCurrentLanguage');
        }
        return \getCurrentLanguage();
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout() {
        if (!\function_exists('getCurrentLayout')) {
            throw new CDEFeatureNotSupportedException('getCurrentLayout');
        }
        $layoutName = \getCurrentLayout();
        if ($layoutName === null) {
            throw new InformationNotAvailableInContextException('layout');
        }

        return new Layout(
            $this->getVhost(),
            $this->getEffectiveVhost(),
            $layoutName
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageLink($lang = null) {
        if (!\function_exists('getCurrentPageLink')) {
            throw new CDEFeatureNotSupportedException('getCurrentPageLink');
        }

        $link = getCurrentPageLink($lang);

        if ($link === null) {
            throw new InformationNotAvailableInContextException('current page link');
        }

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function getPagePath() {
        if (!\function_exists('getCurrentPagePath')) {
            throw new CDEFeatureNotSupportedException('getCurrentPagePath');
        }

        $path = getCurrentPagePath();

        if ($path === null) {
            throw new InformationNotAvailableInContextException('current page path');
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoteAddress() {
        if (!\function_exists('getRemoteAddress')) {
            throw new CDEFeatureNotSupportedException('getRemoteAddress');
        }

        $address = getRemoteAddress();

        if ($address === null) {
            throw new InformationNotAvailableInContextException('remote address');
        }
        return INETAddress::getFromString($address);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParameters() {
        if (!\function_exists('getRequestParameterList')) {
            throw new CDEFeatureNotSupportedException('getRequestParameterList');
        }

        $data = getRequestParameterList(null);
        if ($data === null) {
            throw new InformationNotAvailableInContextException('request parameter');
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestParameter($parameter) {
        $parameters = $this->getRequestParameters();
        if (isset($parameters[$parameter])) {
            return $parameters[$parameter];
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders() {
        if (!\function_exists('getHeaderList')) {
            throw new CDEFeatureNotSupportedException('getHeaderList');
        }

        $data = getHeaderList(null);
        if ($data === null) {
            throw new InformationNotAvailableInContextException('header');
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header) {
        $headers = $this->getHeaders();
        if (isset($headers[$header])) {
            return $headers[$header];
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPSR7() {
        $http_build_query = function($array) {
            $elements = [];
            foreach ($array as $key => $value) {
                $elements[] = urlencode($key) . '=' . urlencode($value);
            }
            return implode('&', $elements);
        };
        $cookies = [];
        foreach ($this->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }
        // TODO: pass headers and body
        // TODO: don't pass headers only! (ServerRequest::getParsedBody needs a body when 'Content-Type' == 'application/x-www-form-urlencoded')
        // TODO: parse port (or whole URI) from getPageLink ?
        $request = new ServerRequest(
            'GET',
            new Uri(
                $this->getScheme(),
                $this->getFQDN(),
                ($this->getScheme()=='https'?443:80),
                preg_replace('/^http(|s):\/\/[a-zA-Z0-9_\-\.:]+/', '', $this->getPageLink()),
                $http_build_query($this->getRequestParameters())
            ),
            [],
            '',
            '1.1',
            $cookies,
            [
                'REMOTE_ADDR' => $this->getRemoteAddress()->__toString()
            ]
        );
        return $request;
    }

}