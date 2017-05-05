<?php

namespace Ixolit\CDE\Controller;


use Ixolit\CDE\Form\Form;
use Ixolit\CDE\Interfaces\FormProcessorInterface;
use Ixolit\CDE\Interfaces\RequestAPI;
use Ixolit\CDE\Interfaces\ResponseAPI;
use Psr\Http\Message\UriInterface;

/**
 * Class CDEController
 *
 * @package Ixolit\CDE\Controller
 */
class CDEController {

    /** @var RequestAPI */
    private $requestApi;

    /** @var ResponseAPI */
    private $responseApi;

    /** @var FormProcessorInterface */
    private $formProcessor;

    /** @var string */
    private $language;

    /**
     * @param RequestAPI             $requestApi
     * @param ResponseAPI            $responseApi
     * @param FormProcessorInterface $formProcessor
     */
    public function __construct(RequestAPI $requestApi,
                                ResponseAPI $responseApi,
                                FormProcessorInterface $formProcessor
    ) {
        $this->requestApi = $requestApi;
        $this->responseApi = $responseApi;
        $this->formProcessor = $formProcessor;
    }

    /**
     * @return RequestAPI
     */
    protected function getRequestApi() {
        return $this->requestApi;
    }

    /**
     * @return ResponseAPI
     */
    protected function getResponseApi() {
        return $this->responseApi;
    }

    /**
     * @return FormProcessorInterface
     */
    protected function getFormProcessor() {
        return $this->formProcessor;
    }

    /**
     * @return string
     */
    protected function getLanguage() {
        if (!isset($this->language)) {
            $this->language = $this->getRequestApi()->getLanguage();
        }

        return $this->language;
    }

    /**
     * @param Form $form
     *
     * @return bool
     */
    protected function handleFormPost(Form $form) {
        if (!$form->isFormPost($this->getRequestApi()->getRequestParameters())) {
            $this->onFormRender($form);

            return false;
        }

        if ($form->hasValidationErrors($this->getRequestApi()->getPSR7())) {
            $this->onFormError($form);
            //exit
        }

        return true;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    protected function onFormRender(Form $form) {
        $this->getFormProcessor()->restore($form, $this->getRequestApi()->getPSR7());

        return $this;
    }

    /**
     * @param Form $form
     *
     * @return void
     */
    protected function onFormError(Form $form) {
        $this->getFormProcessor()->storeForm($form);

        $this->getResponseApi()->redirectToPage($this->createFormErrorRedirectPath($form), $this->getLanguage());
        //exit
    }

    /**
     * @param Form $form
     *
     * @return string
     */
    protected function createFormErrorRedirectPath(Form $form) {
        $redirectPath = empty($form->getErrorRedirectPath())
            ? $this->getRequestApi()->getPagePath() : $form->getErrorRedirectPath();

        $redirectPath .= $this->getParametersString($form->getErrorRedirectParameters());

        return $redirectPath;
    }

    /**
     * @param string $name
     *
     * @return string|null
     */
    protected function getRequestParameter($name) {
        return $this->getRequestApi()->getRequestParameter($name);
    }

    /**
     * @param string $pagePath
     * @param array  $parameters
     *
     * @return UriInterface
     */
    protected function getRedirectUri($pagePath, array $parameters = []) {
        return $this->getRequestApi()->getPSR7()->getUri()
            ->withPath($pagePath)
            ->withQuery($this->getParametersString($parameters));
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    protected function getParametersString($parameters = []) {
        $parameterStringArray = [];
        foreach ($parameters as $name => $value) {
            if (\is_array($value)) {
                foreach ($value as $valuePart) {
                    $parameterStringArray[] = \urlencode($name) . '[]=' . \urlencode($valuePart);
                }
            } else {
                $parameterStringArray[] = \urlencode($name) . '=' . \urlencode($value);
            }
        }

        return \implode('&', $parameterStringArray);
    }

}