<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\Validator\FormValidator;
use Ixolit\CDE\Validator\RequiredValidator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This class was ported from the Piccolo form library with permission.
 */
abstract class FormField {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var FormValidator[]
	 */
	private $validators = [];

	/**
	 * @var string
	 */
	private $label = '';

	/**
	 * @var bool
	 */
	private $required = false;

	/**
	 * @var bool
	 */
	private $autofocus = false;

	/**
	 * @var string
	 */
	private $placeholder = '';

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @var string
	 */
	private $value = '';

	protected $masked = false;

	/**
	 * @param boolean $masked
	 */
	public function setMasked($masked) {
		$this->masked = $masked;
	}

	/**
	 * @return boolean
	 */
	public function isMasked() {
		return $this->masked;
	}

	/**
	 * Return the HTML form element type.
	 *
	 * @return string
	 */
	abstract public function getType();

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return FormValidator[]
	 */
	public function getValidators() {
		return $this->validators;
	}

	/**
	 * @param FormValidator[] $validators
	 *
	 * @return $this
	 */
	public function setValidators(array $validators) {
		$this->validators = $validators;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 *
	 * @return $this
	 */
	public function setLabel($label) {
		$this->label = $label;

		return $this;
	}

	/**
	 * Validates all fields and returns a list of errors.
	 *
	 * @return array
	 */
	public function validate() {
		$this->errors = [];
		foreach ($this->validators as $validator) {
			if (!$validator->isValid($this->getValue())) {
				$this->errors[] = $validator->getKey();
			}
		}
		return $this->errors;
	}

	/**
	 * Set the value from a HTTP request.
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return string[] a list of errors
	 */
	public function setFromRequest(ServerRequestInterface $request) {
		$value = null;
		$parsedBody = $request->getParsedBody();
		if (\array_key_exists($this->getName(), $parsedBody)) {
			$value = $parsedBody[$this->getName()];
		}
		$this->setValue($value);
		$this->validate();

		return $this->errors;
	}

	/**
	 * Add a validator to this field.
	 *
	 * @param FormValidator $validator
	 */
	public function addValidator(FormValidator $validator) {
		$this->validators[$validator->getKey()] = $validator;
	}

	/**
	 * Remove a validator.
	 *
	 * @param FormValidator $validator
	 */
	public function removeValidator(FormValidator $validator) {
		if (isset($this->validators[$validator->getKey()])) {
			unset($this->validators[$validator->getKey()]);
		}
	}

	/**
	 * Set this form field as required.
	 *
	 * @param boolean $required
	 *
	 * @return $this
	 */
	public function setRequired($required) {
		$validator = new RequiredValidator();
		if ($required) {
			$this->addValidator($validator);
		} else if (!$required) {
			$this->removeValidator($validator);
		}

		$this->required = $required;

		return $this;
	}

	/**
	 * Enable this field to be in focus by default.
	 *
	 * @param boolean $autofocus
	 *
	 * @return $this
	 */
	public function setAutofocus($autofocus) {
		$this->autofocus = $autofocus;

		return $this;
	}

	/**
	 * Set the text that is displayed until the field is filled.
	 *
	 * @param string $placeholder
	 *
	 * @return $this
	 */
	public function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isRequired() {
		return $this->required;
	}

	/**
	 * @return boolean
	 */
	public function isAutofocus() {
		return $this->autofocus;
	}

	/**
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setValue($value) {
		$this->value = $value;

		return $this;
	}

	/**
	 * @param array $errors
	 *
	 * @return $this
	 */
	public function setErrors($errors) {
		$this->errors = $errors;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return string[]
	 */
	public function getErrors() {
		return $this->errors;
	}
}