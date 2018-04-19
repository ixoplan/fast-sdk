<?php

namespace Ixolit\CDE\View\Html\Build;


use Ixolit\CDE\Context\Page;
use Ixolit\CDE\Form\CheckboxField;
use Ixolit\CDE\Form\CountrySelector;
use Ixolit\CDE\Form\DropDownField;
use Ixolit\CDE\Form\EmailField;
use Ixolit\CDE\Form\Form as FormObject;
use Ixolit\CDE\Form\FormField;
use Ixolit\CDE\Form\HiddenField;
use Ixolit\CDE\Form\PasswordField;
use Ixolit\CDE\Form\RadioField;
use Ixolit\CDE\Form\TextField;
use Ixolit\CDE\View\Html\Content;
use Ixolit\CDE\View\Html\Element;
use Ixolit\CDE\View\Html\ElementContent;
use Ixolit\CDE\View\Html\ElementEmpty;

/**
 * Form builder
 *
 * Builds HTML element structures for forms
 *
 * @package Ixolit\CDE\View\Html
 */
class Form {

	/** @var FormObject */
	private $form;

	/**
	 * @param FormObject $form
	 */
	public function __construct(FormObject $form) {
		$this->form = $form;
	}

	/**
	 * @return string
	 */
	protected function getFormKey() {
		return $this->form->getKey();
	}

    /**
     * @param string|FormField $field
     * @param string           $prefix
     *
     * @return string
     */
	protected function getFieldName($field, $prefix = '') {
	    return (!empty($prefix) ? $prefix . '_' : '') . $this->getField($field, $prefix)->getName();
    }

    /**
     * @param FormField|string $field
     * @param string           $prefix
     *
     * @return FormField
     */
	protected function getField($field, $prefix = '') {
	    if ($field instanceof  FormField) {
	        return $field;
        }

	    if (!empty($prefix)) {
	        $fieldSet = $this->form->getFieldSetByName($prefix);

	        return $fieldSet->getFieldByName($field);
        }

		return ($field instanceof FormField) ? $field : $this->form->getFirstFieldByName($field);
	}

    /**
     * @param FormField|string $field
     * @param string           $prefix
     *
     * @return bool
     */
	public function fieldHasErrors($field, $prefix = '') {
	    return (\count($this->getField($field, $prefix)->getErrors()) > 0);
    }

	/**
	 * @param FormField|string $field
	 * @param string $prefix
	 *
	 * @return string
	 */
	protected function getFieldId($field, $prefix = '') {
		return 'form_' . $this->getFormKey() . '_' . $this->getFieldName($field, $prefix);
	}

	/**
	 * @return ElementContent
	 */
	protected function getElementForm() {
		return (new ElementContent(Element::NAME_FORM))
			->setAttribute(Element::ATTRIBUTE_NAME_ACTION, $this->form->getAction())
			->setAttribute(Element::ATTRIBUTE_NAME_METHOD, $this->form->getMethod());
	}

    /**
     * @param FormField $field
     * @param           $type
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementInput(FormField $field, $type, $prefix = '') {
		// TODO: extract generic code
		return (new ElementEmpty(Element::NAME_INPUT))
			->setId($this->getFieldId($field, $prefix))
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, $type)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $this->getFieldName($field, $prefix))
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $field->getValue());
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementHidden(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_HIDDEN, $prefix);
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementText(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_TEXT, $prefix);
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementEmail(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_EMAIL, $prefix);
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementPassword(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_PASSWORD, $prefix);
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementCheckbox(FormField $field, $prefix = '') {
		// TODO: extract generic code
		return (new ElementEmpty(Element::NAME_INPUT))
			->setId($this->getFieldId($field, $prefix))
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_CHECKBOX)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $this->getFieldName($field, $prefix))
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, 1)
			->booleanAttribute(Element::ATTRIBUTE_NAME_CHECKED, $field->getValue());
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementDropdown(FormField $field, $prefix = '') {
		// TODO: extract generic code
		$select = (new ElementContent(Element::NAME_SELECT))
			->setId($this->getFieldId($field, $prefix))
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $this->getFieldName($field, $prefix));

		if ($field instanceof DropDownField) {
			/** @var DropDownField $field */
			foreach ($field->getValues() as $value => $label) {
				// TODO: translate label ?
				$select->addContent((new ElementContent(Element::NAME_OPTION))
					->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $value)
					->booleanAttribute(Element::ATTRIBUTE_NAME_SELECTED, $field->getValue() == $value)
					->addContent($label)
				);
			}
		}

		return $select;
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return ElementContent
     */
	protected function getElementRadioGroup(FormField $field, $prefix = '') {
		$group = new ElementContent(Element::NAME_DIV);

		if ($field instanceof RadioField) {
			/** @var RadioField $field */
			$fieldId = $this->getFieldId($field, $prefix);
			$index = 0;
			foreach ($field->getValues() as $value => $label) {
				// TODO: translate label ?
				$id = $fieldId . '_' . $index++;
				$group
					->addContent((new ElementEmpty(Element::NAME_INPUT))
						->setId($id)
						->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_RADIO)
						->setAttribute(Element::ATTRIBUTE_NAME_NAME, $this->getFieldName($field, $prefix))
						->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $value)
						->booleanAttribute(Element::ATTRIBUTE_NAME_CHECKED, $field->getValue() == $value)
					)
					->addContent((new ElementContent(Element::NAME_LABEL))
						->setAttribute(Element::ATTRIBUTE_NAME_FOR, $id)
						->addContent($label)
					);
			}
		}

		return $group;
	}

    /**
     * @param FormField $field
     * @param string    $prefix
     *
     * @return Element
     */
	protected function getElementDefault(FormField $field, $prefix = '') {
		return $this->getElementHidden($field, $prefix);
	}

	/**
	 * Returns the form's start tag
	 *
	 * @return string
	 */
	public function getFormStart() {
		return $this->getElementForm()->getStart();
	}

	/**
	 * Returns the form's end tag
	 *
	 * @return string
	 */
	public function getFormEnd() {
		return $this->getElementForm()->getEnd();
	}

	/**
	 * Returns obligatory fields for a form
	 *
	 * @return Content
	 */
	public function getHeader() {
		return new Content([
			$this->getElement(FormObject::FORM_FIELD_FORM),
			$this->getElement(FormObject::FORM_FIELD_CSRF_TOKEN),
		]);
	}

	/**
	 * @param FormField|string $field
	 * @param string $prefix
	 * @param array $attributes
	 *
	 * @return Element
	 */
	public function getElement($field, $prefix = '', $attributes = []) {

		$formField = $this->getField($field, $prefix);

		switch ($formField->getType()) {

			case HiddenField::TYPE_HIDDEN:
				$element = $this->getElementHidden($formField, $prefix);
				break;

			case TextField::TYPE_TEXT:
				$element = $this->getElementText($formField, $prefix);
				break;

			case EmailField::TYPE_EMAIL:
				$element = $this->getElementEmail($formField, $prefix);
				break;

			case PasswordField::TYPE_PASSWORD:
				$element = $this->getElementPassword($formField, $prefix);
				break;

			case CheckboxField::TYPE_CHECKBOX:
				$element = $this->getElementCheckbox($formField, $prefix);
				break;

			case CountrySelector::TYPE_COUNTRY:
			case DropDownField::TYPE_DROP_DOWN:
				$element = $this->getElementDropdown($formField, $prefix);
				break;

			case RadioField::TYPE_RADIO:
				$element = $this->getElementRadioGroup($formField, $prefix);
				break;

			default:
				$element = $this->getElementDefault($formField, $prefix);
		}

		$element->setAttributes($attributes, true);

		return $element;
	}

	/**
	 * @param FormField|string $field
	 * @param string $prefix
	 * @param array $attributes
	 * @param string|null $text
	 *
	 * @return Element
	 */
	public function getLabel($field, $prefix = '', $attributes = [], $text = null) {

		$formField = $this->getField($field, $prefix);
		$label = $formField->getLabel();
		$text =
			isset($text)
			? $text
			: (
				$label
				? Page::translation($label)
				: Page::translations('label', [
					$this->getFormKey(),
					$this->getFieldName($field, $prefix)
				])
			);

		$element = (new ElementContent(Element::NAME_LABEL))
			->addContent($text)
			->setAttribute(Element::ATTRIBUTE_NAME_FOR, $this->getFieldId($field, $prefix))
			->setAttributes($attributes, true);

		return $element;
	}

	/**
	 * @param FormField|string $field
	 * @param string $prefix
	 * @param array $attributes
	 *
	 * @return Content
	 */
	public function getErrors($field, $prefix = '', $attributes = []) {

		$formField = $this->getField($field, $prefix);

		$content = new Content();
		foreach ($formField->getErrors() as $key => $value) {
			$content->add((new ElementContent(
				Element::NAME_DIV,
				$attributes,
				Page::translations('error', [
					$this->getFormKey(),
					$this->getFieldName($field, $prefix),
					is_numeric($key) ? $value : $key
				])
			)));
		}

		return $content;
	}
}