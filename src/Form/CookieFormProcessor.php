<?php

namespace Ixolit\CDE\Form;

use Ixolit\CDE\CDECookieCache;
use Ixolit\CDE\Interfaces\FormProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This class was ported from the Piccolo form library with permission.
 */
class CookieFormProcessor implements FormProcessorInterface {

	const COOKIE_NAME_POSTFIX_FORM = '-form';
	const COOKIE_FORM_PARAMETER_ERRORS = '_errors';
	const COOKIE_FORM_PARAMETER_FIELDS = '_fields';
	const COOKIE_FORM_PARAMETER_FIELD_SETS = '_fieldSets';
	const COOKIE_FORM_PARAMETER_FIELD_SET_ERRORS = 'errors';
	const COOKIE_FORM_PARAMETER_FIELD_SET_FIELDS = 'fields';
	const COOKIE_FORM_PARAMETER_FIELD_ERRORS = 'errors';
	const COOKIE_FORM_PARAMETER_FIELD_VALUE = 'value';

	/**
	 * {@inheritdoc}
	 */
	public function store(Form $form, ResponseInterface $response) {
		$dataset = $this->getFormDataToStore($form);

		CDECookieCache::getInstance()->write(
			$form->getKey() . self::COOKIE_NAME_POSTFIX_FORM,
			\base64_encode(\json_encode($dataset))
		);

		return $response;
	}

	/**
	 * @param Form $form
	 *
	 * @return $this
	 */
	public function storeForm(Form $form) {
		$dataset = $this->getFormDataToStore($form);

		CDECookieCache::getInstance()->write(
			$form->getKey() . self::COOKIE_NAME_POSTFIX_FORM,
			\base64_encode(\json_encode($dataset))
		);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function cleanup(Form $form, ResponseInterface $response) {
		CDECookieCache::getInstance()->delete($form->getKey() . self::COOKIE_NAME_POSTFIX_FORM);

		return $response;
	}

	/**
	 * @param Form $form
	 *
	 * @return $this
	 */
	public function cleanupForm(Form $form) {
		CDECookieCache::getInstance()->delete($form->getKey() . self::COOKIE_NAME_POSTFIX_FORM);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function restore(Form $form, ServerRequestInterface $request) {
		$data = CDECookieCache::getInstance()->consume($form->getKey() . self::COOKIE_NAME_POSTFIX_FORM);

        if (!empty($data)) {
            try {
                $data = \json_decode(\base64_decode($data), true);

                return $this->setRestoredFormData($form, $data);
            } catch (\Exception $e) {
            }
        }

        return false;
	}

	/**
	 * @param Form $form
	 *
	 * @return array
	 */
	protected function getFormDataToStore(Form $form) {
		$dataset = [
            self::COOKIE_FORM_PARAMETER_ERRORS     => [],
            self::COOKIE_FORM_PARAMETER_FIELDS     => [],
            self::COOKIE_FORM_PARAMETER_FIELD_SETS => [],
        ];

		foreach ($form->getFields() as $field) {
            $dataset[self::COOKIE_FORM_PARAMETER_FIELDS][$field->getName()] = $this->getFieldArrayToStore($field);
		}

		foreach ($form->getFieldSets() as $fieldSet) {
            $dataset[self::COOKIE_FORM_PARAMETER_FIELD_SETS][$fieldSet->getKey()] = $this->getFieldSetArrayToStore(
                $fieldSet
            );
        }

		$dataset[self::COOKIE_FORM_PARAMETER_ERRORS] = $form->getErrors();

		return $dataset;
	}

    /**
     * @param FormField $field
     *
     * @return array
     */
	protected function getFieldArrayToStore(FormField $field) {
	    $fieldArray = [
	        self::COOKIE_FORM_PARAMETER_FIELD_ERRORS => $field->getErrors()
        ];

        if (!$field->isMasked()) {
            $fieldArray[self::COOKIE_FORM_PARAMETER_FIELD_VALUE] = $field->getValue();
        }

        return $fieldArray;
    }

    /**
     * @param FormFieldSet $fieldSet
     *
     * @return array
     */
    protected function getFieldSetArrayToStore(FormFieldSet $fieldSet) {
	    $fieldSetArray = [
            self::COOKIE_FORM_PARAMETER_FIELD_SET_ERRORS => $fieldSet->getErrors(),
        ];

	    $fields = [];
	    foreach ($fieldSet->getFields() as $field) {
	        $fields[$field->getName()] = $this->getFieldArrayToStore($field);
        }

        $fieldSetArray[self::COOKIE_FORM_PARAMETER_FIELD_SET_FIELDS] = $fields;

	    return $fieldSetArray;
    }

	/**
	 * @param Form  $form
     * @param array $data
	 *
	 * @return bool
	 */
	protected function setRestoredFormData(Form $form, $data) {
        if (\is_array($data)) {
            if (
                !empty($data[self::COOKIE_FORM_PARAMETER_FIELDS])
                && \is_array($data[self::COOKIE_FORM_PARAMETER_FIELDS])
            ) {
                $form->setFields(
                    $this->setRestoredFieldData($form->getFields(), $data[self::COOKIE_FORM_PARAMETER_FIELDS])
                );
            }

            if (
                !empty($data[self::COOKIE_FORM_PARAMETER_FIELD_SETS])
                && \is_array($data[self::COOKIE_FORM_PARAMETER_FIELD_SETS])
            ) {
                $form->setFieldSets(
                    $this->setRestoredFieldSetData($form->getFieldSets(), $data[self::COOKIE_FORM_PARAMETER_FIELD_SETS])
                );
            }

            if (isset($data[self::COOKIE_FORM_PARAMETER_ERRORS])) {
                $form->setErrors($data[self::COOKIE_FORM_PARAMETER_ERRORS]);
            }

            return true;
        }

        return false;
	}

    /**
     * @param FormField[] $formFields
     * @param array       $fields
     *
     * @return FormField[]
     */
	protected function setRestoredFieldData($formFields, array $fields) {
        foreach ($formFields as $field) {
            if (\array_key_exists($field->getName(), $fields)) {
                if (isset($fields[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_VALUE])) {
                    $field->setValue($fields[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_VALUE]);
                }

                $field->setErrors($fields[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_ERRORS]);
            }
        }

        return $formFields;
    }

    /**
     * @param FormFieldSet[] $formFieldSets
     * @param array          $fieldSets
     *
     * @return FormFieldSet[]
     */
    protected function setRestoredFieldSetData($formFieldSets, array $fieldSets) {
        foreach ($formFieldSets as $fieldSet) {
            if (\array_key_exists($fieldSet->getKey(), $fieldSets)) {
                if (
                    !empty($fieldSets[$fieldSet->getKey()][self::COOKIE_FORM_PARAMETER_FIELD_SET_FIELDS])
                    && \is_array($fieldSets[$fieldSet->getKey()][self::COOKIE_FORM_PARAMETER_FIELD_SET_FIELDS])
                ) {
                    $fieldSet->setFields(
                        $this->setRestoredFieldData(
                            $fieldSet->getFields(),
                            $fieldSets[$fieldSet->getKey()][self::COOKIE_FORM_PARAMETER_FIELD_SET_FIELDS]
                        )
                    );
                }

                $fieldSet->setErrors($fieldSets[$fieldSet->getKey()][self::COOKIE_FORM_PARAMETER_FIELD_SET_ERRORS]);
            }
        }

        return $formFieldSets;
    }

}
