<?php

namespace Ixolit\CDE\Interfaces;


/**
 * Interface ControllerLogicInterface
 *
 * @package Ixolit\CDE\Interfaces
 */
interface ControllerLogicInterface {

    /**
     * @param RequestAPI $requestApi
     *
     * @return $this
     */
    public function setRequestApi(RequestAPI $requestApi);

    /**
     * @param ResponseAPI $responseApi
     *
     * @return $this
     */
    public function setResponseApi(ResponseAPI $responseApi);

    /**
     * @param FilesystemAPI $filesystemApi
     *
     * @return $this
     */
    public function setFileSystemApi(FilesystemAPI $filesystemApi);

    /**
     * @return void
     */
    public function execute();

}