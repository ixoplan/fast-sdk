<?php

namespace Ixolit\CDE\Interfaces;

use Ixolit\CDE\Form\Form;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface FormProcessorInterface {
	/**
	 * Store the form, and modify the response to include any data that is needed.
	 *
	 * @param Form              $form
	 * @param ResponseInterface $response
	 *
	 * @return ResponseInterface
	 *
	 * @deprecated use FormProcessorInterface::storeForm instead
	 */
	public function store(Form $form, ResponseInterface $response);

	/**
	 * Store the form request data.
	 *
	 * @param Form $form
	 *
	 * @return $this
	 */
	public function storeForm(Form $form);

	/**
	 * Restores the form from storage and returns if the restore process was successful.
	 *
	 * @param Form                   $form
	 * @param ServerRequestInterface $request
	 *
	 * @return bool
	 */
	public function restore(Form $form, ServerRequestInterface $request);

    /**
     * Read all stored form data.
     *
     * @param string $formName
     *
     * @return array
     */
	public function readStoredFormData($formName);

	/**
	 * Cleanup the form from storage.
	 *
	 * @param Form              $form
	 * @param ResponseInterface $response
	 *
	 * @return ResponseInterface
	 *
	 * @deprecated use FormProcessorInterface::cleanupForm instead
	 */
	public function cleanup(Form $form, ResponseInterface $response);

	/**
	 * Cleanup the form from storage.
	 *
	 * @param Form $form
	 *
	 * @return $this
	 */
	public function cleanupForm(Form $form);

}
