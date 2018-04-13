<?php

namespace Ixolit\CDE\Api;


use Ixolit\CDE\Exceptions\CDEFeatureNotSupportedException;
use Ixolit\CDE\Exceptions\ResourceNotFoundException;
use Ixolit\CDE\Interfaces\ResourceAPI;

/**
 * Class CDEResourceAPI
 *
 * @package Ixolit\CDE\Api
 */
class CDEResourceAPI implements ResourceAPI {

    /** @inheritdoc */
    function getStaticUrl($path) {

        if (!\function_exists('getStaticLink')) {
            throw new CDEFeatureNotSupportedException('getStaticLink');
        }

        $url = \getStaticLink($path);
        if ($url === null) {
            throw new ResourceNotFoundException($path);
        }

        return $url;
    }

}