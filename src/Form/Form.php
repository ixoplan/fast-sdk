<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\Validator\CSRFTokenValidator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This class was ported from the Piccolo form library with permission.
 */
abstract class Form {

	const FORM_METHOD_GET = 'GET';
	const FORM_METHOD_POST = 'POST';

	const FORM_FIELD_CSRF_TOKEN = 'csrf-token';
	const FORM_FIELD_FORM = '_form';

	/**
	 * @var CSRFTokenProvider
	 */
	private $csrfTokenProvider;
	/**
	 * @var string
	 */
	private $action;
	/**
	 * @var string
	 */
	private $method;

	/** @var string */
	private $errorRedirectPath;

	/** @var array */
	private $errorRedirectParameters;

	/**
	 * @var FormField[]
	 */
	private $fields = [];

	/**
	 * Generic, form errors.
	 *
	 * @var string[]
	 */
	private $errors = [];

	/**
	 * @param string            $action
	 * @param string            $method
	 * @param CSRFTokenProvider $csrfTokenProvider
	 * @param string            $errorRedirectPath
	 * @param array             $errorRedirectParameters
	 */
	public function __construct($action = '',
								$method = self::FORM_METHOD_POST,
								CSRFTokenProvider $csrfTokenProvider,
								$errorRedirectPath = '',
								array $errorRedirectParameters = []
	) {
		$this->csrfTokenProvider = $csrfTokenProvider;
		$this->action = $action;
		$this->method = $method;
		$this->errorRedirectPath = $errorRedirectPath;
		$this->errorRedirectParameters = $errorRedirectParameters;

		if ($this->method == self::FORM_METHOD_POST) {
			$csrfField = new HiddenField(self::FORM_FIELD_CSRF_TOKEN);
			$csrfField->addValidator(new CSRFTokenValidator($csrfTokenProvider->getCSRFToken()));
			$csrfField->setValue($csrfTokenProvider->getCSRFToken());
			//Don't transfer the value back to the form.
			$csrfField->setMasked(true);
			$this->addField($csrfField);
		}
		$formField = new HiddenField(self::FORM_FIELD_FORM);
		$formField->setValue($this->getKey());
		$formField->setMasked(true);
		$this->addField($formField);
	}

	/**
	 * Return a unique key for this form.
	 *
	 * @return string
	 */
	abstract public function getKey();

	/**
	 * Return the form-specific errors. Does not return the field errors.
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @param string[] $errors
	 */
	public function setErrors($errors) {
		$this->errors = $errors;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * @param string $errorRedirectPath
	 *
	 * @return $this
	 */
	public function setErrorRedirectPath($errorRedirectPath) {
		$this->errorRedirectPath = $errorRedirectPath;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getErrorRedirectPath() {
		return $this->errorRedirectPath;
	}

	/**
	 * @param array $errorRedirectParameters
	 *
	 * @return $this
	 */
	public function setErrorRedirectParameters(array $errorRedirectParameters) {
		$this->errorRedirectParameters = $errorRedirectParameters;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getErrorRedirectParameters() {
		return $this->errorRedirectParameters;
	}

	/**
	 * @return FormField[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param FormField $field
	 *
	 * @return $this
	 */
	protected function addField(FormField $field) {
		$this->fields[$field->getName()] = $field;

		return $this;
	}

	/**
	 * Validate the form and return a list of error codes.
	 *
	 * @return array
	 *
	 * @deprecated
	 */
	public function validate() {
		$errors = [];
		foreach ($this->fields as $field) {
			$errors[$field->getName()] = $field->validate();
		}
		return $errors;
	}

	/**
	 * @param string $name
	 *
	 * @return FormField[]
	 */
	public function getFieldsByName($name) {
		$result = [];
		foreach ($this->fields as $field) {
			if ($field->getName() == $name) {
				$result[] = $field;
			}
		}
		return $result;
	}

	/**
	 * @param string $name
	 *
	 * @return FormField
	 * @throws \Exception
	 */
	public function getFirstFieldByName($name) {
		foreach ($this->fields as $field) {
			if ($field->getName() == $name) {
				return $field;
			}
		}
		throw new \Exception('Form field not found: ' . $name);
	}

	public function getValueByName($name) {
		$fields = $this->getFieldsByName($name);
		$value = null;
		foreach ($fields as $field) {
			if (!\is_null($value = $field->getValue())) {
				break;
			}
		}
		return $value;
	}

	public function setFromRequest(ServerRequestInterface $request) {
		$errors = [];
		foreach ($this->fields as $field) {
			$field->setErrors([]);
			$fieldErrors = $field->setFromRequest($request);
			if ($fieldErrors) {
				$errors[$field->getName()] = $fieldErrors;
			}
		}
		return $errors;
	}

	/**
	 * @param array $requestParameters
	 *
	 * @return bool
	 */
	public function isFormPost(array $requestParameters) {;
		return (
			empty($requestParameters[Form::FORM_FIELD_FORM])
			|| $this->getKey() != $requestParameters[Form::FORM_FIELD_FORM]
		);
	}

	/**
	 * @param ServerRequestInterface $request
	 *
	 * @return bool
	 */
	public function hasValidationErrors(ServerRequestInterface $request) {
		$errors = $this->setFromRequest($request);

		return \count($errors);
	}

}
