<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\Validator\CSRFTokenValidator;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class Form {

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

	/** @var FormFieldSet[] */
	private $fieldSets = [];

	/**
	 * Generic, form errors.
	 *
	 * @var string[]
	 */
	private $errors = [];

    /**
     * @var array
     */
	private $validationErrors = [];

    /**
     * @var FormCustomInterface
     */
	private $customForm;

    /**
     * @param string $action
     * @param string $method
     * @param CSRFTokenProvider $csrfTokenProvider
     * @param string $errorRedirectPath
     * @param array $errorRedirectParameters
     * @param FormCustomInterface|null $customForm
     */
	public function __construct($action = '',
								$method = self::FORM_METHOD_POST,
								CSRFTokenProvider $csrfTokenProvider,
								$errorRedirectPath = '',
								array $errorRedirectParameters = [],
                                FormCustomInterface $customForm = null
	) {
		$this->csrfTokenProvider = $csrfTokenProvider;
		$this->action = $action;
		$this->method = $method;
		$this->errorRedirectPath = $errorRedirectPath;
		$this->errorRedirectParameters = $errorRedirectParameters;

        if ($customForm) {
            $this->setCustomForm($customForm);
        }

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
     * @param FormCustomInterface $customForm
     *
     * @return $this
     */
	public function setCustomForm(FormCustomInterface $customForm) {
	    $this->customForm = $customForm;

	    return $this;
    }

    /**
     * @return FormCustomInterface
     */
    public function getCustomForm() {
	    return $this->customForm;
    }

	/**
	 * Return a unique key for this form.
	 *
	 * @return string
	 */
	public function getKey() {
	    if ($this->getCustomForm()) {
	        return $this->getCustomForm()->getKey();
        }

        //fallback
	    return 'form';
    }

	/**
	 * Return the form-specific errors. Does not return the field errors.
	 */
	public function getErrors() {
		return $this->errors;
	}

    /**
     * @param string[] $errors
     *
     * @return $this
     */
    // TODO: write a helper addError?
	public function setErrors($errors) {
		$this->errors = $errors;

		return $this;
	}

    /**
     * @return array
     */
	public function getValidationErrors() {
	    return $this->validationErrors;
    }

    /**
     * @param array $validationErrors
     *
     * @return $this
     */
    public function setValidationErrors($validationErrors) {
	    $this->validationErrors = $validationErrors;

	    return $this;
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
     * @param FormField[] $fields
     *
     * @return mixed
     */
	public function setFields($fields) {
	    $this->fields = $fields;

	    return $fields;
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
	public function addField(FormField $field) {
		$this->fields[$field->getName()] = $field;

		return $this;
	}

    /**
     * @param FormFieldSet[] $fieldSets
     *
     * @return $this
     */
	public function setFieldSets($fieldSets) {
	    $this->fieldSets = $fieldSets;

	    return $this;
    }

    /**
     * @return FormFieldSet[]
     */
	public function getFieldSets() {
	    return $this->fieldSets;
    }

    /**
     * @param FormFieldSet $fieldSet
     *
     * @return $this
     */
    public function addFieldSet(FormFieldSet $fieldSet) {
	    $this->fieldSets[$fieldSet->getKey()] = $fieldSet;

	    return $this;
    }

    /**
     * @param string $fieldSetName
     *
     * @return FormFieldSet|null
     */
    public function getFieldSetByName($fieldSetName) {
        $fieldSets = $this->getFieldSets();
        if (isset($fieldSets[$fieldSetName]) && $fieldSets[$fieldSetName] instanceof FormFieldSet) {
            return $fieldSets[$fieldSetName];
        }

        return null;
    }

    /**
     * Validate the form and set a list of error codes.
     *
     * @return $this
     */
	public function validate() {
		$errors = [];

		//validate all fields
		foreach ($this->getFields() as $field) {
		    $field = $field->validate();

		    if (!empty($field->getErrors())) {
                $errors[$field->getName()] = $field->getErrors();
            }
		}

		//validate all field sets
		foreach ($this->getFieldSets() as $fieldSet) {
		    $fieldSet = $fieldSet->validate();

		    $errors = \array_merge($errors, $fieldSet->getPrefixedErrors());
        }

		return $this->setValidationErrors($errors);
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

    /**
     * @param string $name
     *
     * @return null|string
     */
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

    /**
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
	public function setFromRequest(ServerRequestInterface $request) {
	    //set field values
		foreach ($this->fields as $field) {
            $field->setFromRequest($request);
		}

		//set field sets values
		foreach ($this->fieldSets as $fieldSet) {
            $fieldSet->setFromRequest($request);
        }

		return $this;
	}

	/**
	 * @param array $requestParameters
	 *
	 * @return bool
	 */
	public function isFormPost(array $requestParameters) {
        return !(
			empty($requestParameters[Form::FORM_FIELD_FORM])
			|| $this->getKey() != $requestParameters[Form::FORM_FIELD_FORM]
		);
	}

}
