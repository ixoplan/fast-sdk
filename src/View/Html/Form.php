<?php

namespace Ixolit\CDE\View\Html;


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

/**
 * Form Renderer
 *
 * Builds HTML element structures for forms
 *
 * @package Ixolit\CDE\View\Html
 */
class Form {

	/** @var $instance */
	private static $instance = null;

	private function __construct() {
	}

	private function __clone() {
	}

	/**
	 * @return $this
	 */
	public static function get() {

		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return string
	 */
	public function getClassHasErrors() {
		// TODO: writable?
		return 'has-errors';
	}

	protected function getElementForm(FormObject $form) {
		return (new Element(Element::NAME_FORM))
			->setAttribute(Element::ATTRIBUTE_NAME_ACTION, $form->getAction())
			->setAttribute(Element::ATTRIBUTE_NAME_METHOD, $form->getMethod());
	}

	protected function getElementInput(FormObject $form, FormField $field, $type, $prefix = '') {
		// TODO: extract generic code
		return (new Element(Element::NAME_INPUT))
			->setId('form_' . $form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, $type)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $field->getValue());
	}

	protected function getElementHidden(FormObject $form, FormField $field, $prefix = '') {
		return $this->getElementInput($form, $field, Element::ATTRIBUTE_VALUE_TYPE_HIDDEN, $prefix);
	}

	protected function getElementText(FormObject $form, FormField $field, $prefix = '') {
		return $this->getElementInput($form, $field, Element::ATTRIBUTE_VALUE_TYPE_TEXT, $prefix);
	}

	protected function getElementEmail(FormObject $form, FormField $field, $prefix = '') {
		return $this->getElementInput($form, $field, Element::ATTRIBUTE_VALUE_TYPE_EMAIL, $prefix);
	}

	protected function getElementPassword(FormObject $form, FormField $field, $prefix = '') {
		return $this->getElementInput($form, $field, Element::ATTRIBUTE_VALUE_TYPE_PASSWORD, $prefix);
	}

	protected function getElementCheckbox(FormObject $form, FormField $field, $prefix = '') {
		// TODO: extract generic code
		return (new Element(Element::NAME_INPUT))
			->setId('form_' . $form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_CHECKBOX)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, 1)
			->booleanAttribute(Element::ATTRIBUTE_NAME_CHECKED, $field->getValue());
	}

	protected function getElementDropdown(FormObject $form, FormField $field, $prefix = '') {
		// TODO: extract generic code
		$select = (new Element(Element::NAME_SELECT))
			->setId('form_' . $form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName());

		if ($field instanceof DropDownField) {
			/** @var DropDownField $field */
			foreach ($field->getValues() as $value => $label) {
				$select->addContent((new Element(Element::NAME_OPTION))
					->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $value)
					->booleanAttribute(Element::ATTRIBUTE_NAME_SELECTED, $field->getValue() == $value)
					->addContent($label)
				);
			}
		}

		return $select;
	}

	protected function getElementRadioGroup(FormObject $form, FormField $field, $prefix = '') {
		$group = new Element(Element::NAME_DIV);

		if ($field instanceof RadioField) {
			/** @var RadioField $field */
			$index = 0;
			foreach ($field->getValues() as $value => $label) {
				$id = 'form_' . $form->getKey() . '_' . $prefix . $field->getName() . '_' . $index++;
				$group
					->addContent((new Element(Element::NAME_INPUT))
						->setId($id)
						->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_RADIO)
						->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
						->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $value)
						->booleanAttribute(Element::ATTRIBUTE_NAME_CHECKED, $field->getValue() == $value)
					)
					->addContent((new Element(Element::NAME_LABEL))
						->setAttribute(Element::ATTRIBUTE_NAME_FOR, $id)
						->addContent($label)
					);
			}
		}

		return $group;
	}

	protected function getElementDefault(FormObject $form, FormField $field, $prefix = '') {
		return $this->getElementHidden($form, $field, $prefix);
	}

	/**
	 * Returns the given form's start tag
	 *
	 * @param FormObject $form
	 *
	 * @return string
	 */
	public function getFormStart(FormObject $form) {
		return $this->getElementForm($form)->getStart();
	}

	/**
	 * Returns the given form's end tag
	 *
	 * @param FormObject $form
	 *
	 * @return string
	 */
	public function getFormEnd(FormObject $form) {
		return $this->getElementForm($form)->getEnd();
	}

	/**
	 * Returns obligatory fields for a form
	 *
	 * @param FormObject $form
	 *
	 * @return Content
	 */
	public function getHeader(FormObject $form) {
		return new Content([
			$this->getElement($form, FormObject::FORM_FIELD_FORM),
			$this->getElement($form, FormObject::FORM_FIELD_CSRF_TOKEN),
		]);
	}

	/**
	 * @param FormObject $form
	 * @param FormField|string $field
	 * @param string $prefix
	 * @param array $attributes
	 *
	 * @return Element
	 */
	public function getElement(FormObject $form, $field, $prefix = '', $attributes = []) {

		if (!($field instanceof FormField)) {
			$field = $form->getFirstFieldByName($field);
		}

		// TODO: has errors
		switch ($field->getType()) {

			case HiddenField::TYPE_HIDDEN:
				$element = $this->getElementHidden($form, $field, $prefix);
				break;

			case TextField::TYPE_TEXT:
				$element = $this->getElementText($form, $field, $prefix);
				break;

			case EmailField::TYPE_EMAIL:
				$element = $this->getElementEmail($form, $field, $prefix);
				break;

			case PasswordField::TYPE_PASSWORD:
				$element = $this->getElementPassword($form, $field, $prefix);
				break;

			case CheckboxField::TYPE_CHECKBOX:
				$element = $this->getElementCheckbox($form, $field, $prefix);
				break;

			case CountrySelector::TYPE_COUNTRY:
			case DropDownField::TYPE_DROP_DOWN:
				$element = $this->getElementDropdown($form, $field, $prefix);
				break;

			case RadioField::TYPE_RADIO:
				$element = $this->getElementRadioGroup($form, $field, $prefix);
				break;

			default:
				$element = $this->getElementDefault($form, $field, $prefix);
		}

		$element->setAttributes($attributes, true);

		if (\count($field->getErrors())) {
			$element->addClass($this->getClassHasErrors());
		}

		return $element;
	}
}