<?php

namespace Ixolit\CDE\Controller;


use Ixolit\CDE\CDE;
use Ixolit\CDE\Exceptions\ControllerSkipViewException;
use Ixolit\CDE\Form\CookieCSRFTokenProvider;
use Ixolit\CDE\Form\CookieFormProcessor;
use Ixolit\CDE\Form\CSRFTokenProvider;
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

    /** @var FormProcessorInterface */
    private $formProcessor;

    /** @var RequestAPI */
    private $requestApi;

    /** @var ResponseAPI */
    private $responseApi;

    /** @var CSRFTokenProvider */
    private $csrfTokenProvider;

    /** @var string */
    private $language;

    /**
     * CDEController constructor.
     *
     * @param FormProcessorInterface|null $formProcessor
     * @param RequestAPI|null             $requestApi
     * @param ResponseAPI|null            $responseApi
     * @param CSRFTokenProvider|null      $csrfTokenProvider
     */
    public function __construct(FormProcessorInterface $formProcessor = null,
                                RequestAPI $requestApi = null,
                                ResponseAPI $responseApi = null,
                                CSRFTokenProvider $csrfTokenProvider = null
    ) {
        $this->formProcessor = $formProcessor;
        $this->requestApi = $requestApi;
        $this->responseApi = $responseApi;
        $this->csrfTokenProvider = $csrfTokenProvider;
    }

    /**
     * @return FormProcessorInterface
     */
    protected function getFormProcessor() {
        if (!isset($this->formProcessor)) {
            //default form processor
            $this->formProcessor = new CookieFormProcessor();
        }
        return $this->formProcessor;
    }

    /**
     * @return RequestAPI
     */
    protected function getRequestApi() {
        if (!isset($this->requestApi)) {
            //default request api
            $this->requestApi = CDE::getRequestAPI();
        }

        return $this->requestApi;
    }

    /**
     * @return ResponseAPI
     */
    protected function getResponseApi() {
        if (!isset($this->responseApi)) {
            //default response api
            $this->responseApi = CDE::getResponseAPI();
        }

        return $this->responseApi;
    }

    /**
     * @return CSRFTokenProvider
     */
    protected function getCSRFTokenProvider() {
        if (!isset($this->csrfTokenProvider)) {
            //default csrf token provider
            $this->csrfTokenProvider = new CookieCSRFTokenProvider($this->getRequestApi(), $this->getResponseApi());
        }

        return $this->csrfTokenProvider;
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

        $validatedForm = $form
            ->setFromRequest($this->getRequestApi()->getPSR7())
            ->validate();

        if (!empty($validatedForm->getValidationErrors())) {
            $this->onFormError($validatedForm);
            //exit
        }

        return true;
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    protected function onFormRender(Form $form) {
        $this->getFormProcessor()->restore($form, $this->getRequestApi()->getPSR7());

        return $form;
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
     * @param Form   $form
     * @param string $pagePath
     */
    protected function cleanFormAndRedirectTo(Form $form, $pagePath) {
        $this->getFormProcessor()->cleanupForm($form);

        $this->redirectToPath($pagePath);
        //exit
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
     * @param string $pagePath
     * @param array  $parameters
     *
     * @return void
     */
    protected function redirectToPath($pagePath, array $parameters = []) {
        $redirectUri = $this->getRedirectUri($pagePath, $parameters);

        $this->redirectTo($redirectUri);
    }

    /**
     * @param UriInterface|string $redirectUri
     *
     * @return void
     *
     * @throws ControllerSkipViewException
     */
    protected function redirectTo($redirectUri) {
        $this->getResponseApi()->redirectTo($redirectUri);

        throw new ControllerSkipViewException();
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