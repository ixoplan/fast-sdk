<?php

namespace Ixolit\CDE\Context;

/**
 * Interface PageCustomInterface
 *
 * @package Ixolit\CDE\Context
 */
interface PageCustomInterface {

    /**
     * @param Page $page
     *
     * @return $this
     */
    public function setPage(Page $page);

    /**
     * @return Page
     */
    public function getPage();

    /**
     * @return $this
     */
    public function doPrepare();

}