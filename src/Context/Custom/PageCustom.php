<?php

namespace Ixolit\CDE\Context\Custom;

use Ixolit\CDE\Context\Page;
use Ixolit\CDE\Context\PageCustomInterface;

/**
 * Class PageCustom
 *
 * @package Ixolit\CDE\Context\Custom
 */
final class PageCustom implements PageCustomInterface {

    /**
     * @var Page
     */
    private $page;

    /**
     * @param Page $page
     *
     * @return $this
     */
    public function setPage(Page $page) {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Page
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * @return $this
     */
    public function doPrepare() {
        return $this;
    }
}