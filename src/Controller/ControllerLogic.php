<?php

namespace Ixolit\CDE\Controller;

use Ixolit\CDE\Auth\AuthenticationRequiredException;
use Ixolit\CDE\Exceptions\ControllerSkipViewException;
use Ixolit\CDE\Exceptions\InformationNotAvailableInContextException;
use Ixolit\CDE\Interfaces\ControllerLogicInterface;
use Ixolit\CDE\Interfaces\FilesystemAPI;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;
use Ixolit\CDE\PSR7\Response;
use Ixolit\CDE\WorkingObjects\ViewModel;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ControllerLogic
 *
 * @package Ixolit\CDE\Controller
 */
class ControllerLogic implements ControllerLogicInterface {
	/**
	 * @var RequestAPI|null
	 */
	private $requestApi;

	/**
	 * @var ResponseAPI|null
	 */
	private $responseApi;

	/**
	 * @var FilesystemAPI|null
	 */
	private $fsApi;

    /**
     * ControllerLogic constructor.
     *
     * @param RequestAPI|null    $requestApi
     * @param ResponseAPI|null   $responseApi
     * @param FilesystemAPI|null $fsApi
     */
	public function __construct(
	    RequestAPI $requestApi = null,
        ResponseAPI $responseApi = null,
        FilesystemAPI $fsApi = null
    ) {
		$this->requestApi  = $requestApi;
		$this->responseApi = $responseApi;
		$this->fsApi = $fsApi;
	}

    /**
     * @param RequestAPI $requestApi
     *
     * @return $this
     */
    public function setRequestApi(RequestAPI $requestApi) {
        $this->requestApi = $requestApi;

        return $this;
    }

    /**
     * @param ResponseAPI $responseApi
     *
     * @return $this
     */
    public function setResponseApi(ResponseAPI $responseApi) {
        $this->responseApi = $responseApi;

        return $this;
    }

    /**
     * @param FilesystemAPI $filesystemApi
     *
     * @return $this
     */
    public function setFileSystemApi(FilesystemAPI $filesystemApi) {
        $this->fsApi = $filesystemApi;

        return $this;
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
	public function execute() {
	    if (empty($this->requestApi) || empty($this->responseApi) || empty($this->fsApi)) {
	        throw new \Exception('Required APIs are missing.');
        }

		global $view;

		try {
			$path = defined('VHOSTS_DIR') ? VHOSTS_DIR : '/vhosts/';
			$path .= urlencode($this->requestApi->getEffectiveVhost());
			$path .= '/layouts/';
			$path .= $this->requestApi->getLayout()->getName();
			$path .= '/pages';
			$path .= rtrim($this->requestApi->getPagePath(), '/');
			$path .= '/controller.php';

			$viewData = [];
			if ($this->fsApi->exists($path)) {
				try {
					$controllerData = include($path);
				} catch (ControllerSkipViewException $e) {
					exit;
				}
				if ($controllerData instanceof ResponseInterface) {
					$this->responseApi->sendPSR7($controllerData);
					exit;
				}
				if (!empty($controllerData) && is_array($controllerData)) {
                    foreach ($controllerData as $key => $value) {
                        $viewData[$key] = $value;
                    }
                }
			}
			$view = new ViewModel($viewData);
		} catch (AuthenticationRequiredException $e) {
			if ($loginPage = getMeta('loginPage')) {
				$currentUri = $this->requestApi->getPSR7()->getUri();
				$newUri     = $currentUri
					->withPath('/' . $this->requestApi->getLanguage() . $loginPage)
					->withQuery('backurl=' . \urlencode($currentUri->getPath()));
				$this->responseApi->sendPSR7(new Response(302, [
					'Location' => [(string)$newUri]
				], '', '1.1'));
			} else {
				throw $e;
			}
		} catch (InformationNotAvailableInContextException $e) {
			// We are not in a page, no controller logic.
			return;
		} catch (\Exception $e) {
			if (\function_exists('previewInfo') && previewInfo()) {
				include(__DIR__ . '/errorpage.php');
				exit;
			} else {
				throw $e;
			}
		}
	}

}