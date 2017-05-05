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
		$dataset = [];

		foreach ($form->getFields() as $field) {
			$dataset[$field->getName()] = [];
			if (!$field->isMasked()) {
				$dataset[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_VALUE] = $field->getValue();
			}
			$dataset[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_ERRORS] = $field->getErrors();
		}

		$dataset[self::COOKIE_FORM_PARAMETER_ERRORS] = $form->getErrors();

		return $dataset;
	}

	/**
	 * @param Form   $form
     * @param array  $data
	 *
	 * @return bool
	 */
	protected function setRestoredFormData(Form $form, $data) {
        if (\is_array($data)) {
            foreach ($form->getFields() as $field) {
                if (\array_key_exists($field->getName(), $data)) {
                    if (isset($data[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_VALUE])) {
                        $field->setValue($data[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_VALUE]);
                    }

                    $field->setErrors($data[$field->getName()][self::COOKIE_FORM_PARAMETER_FIELD_ERRORS]);
                }
            }

            if (isset($data[self::COOKIE_FORM_PARAMETER_ERRORS])) {
                $form->setErrors($data[self::COOKIE_FORM_PARAMETER_ERRORS]);
            }

            return true;
        }

        return false;
	}

}
