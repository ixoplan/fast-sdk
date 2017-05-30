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
	public function getClassHasErrors() {
		// TODO: writable?
		return 'has-errors';
	}

	/**
	 * @return ElementContent
	 */
	protected function getElementForm() {
		return (new ElementContent(Element::NAME_FORM))
			->setAttribute(Element::ATTRIBUTE_NAME_ACTION, $this->form->getAction())
			->setAttribute(Element::ATTRIBUTE_NAME_METHOD, $this->form->getMethod());
	}

	protected function getElementInput(FormField $field, $type, $prefix = '') {
		// TODO: extract generic code
		return (new ElementEmpty(Element::NAME_INPUT))
			->setId('form_' . $this->form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, $type)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $field->getValue());
	}

	protected function getElementHidden(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_HIDDEN, $prefix);
	}

	protected function getElementText(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_TEXT, $prefix);
	}

	protected function getElementEmail(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_EMAIL, $prefix);
	}

	protected function getElementPassword(FormField $field, $prefix = '') {
		return $this->getElementInput($field, Element::ATTRIBUTE_VALUE_TYPE_PASSWORD, $prefix);
	}

	protected function getElementCheckbox(FormField $field, $prefix = '') {
		// TODO: extract generic code
		return (new ElementEmpty(Element::NAME_INPUT))
			->setId('form_' . $this->form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_CHECKBOX)
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_VALUE, 1)
			->booleanAttribute(Element::ATTRIBUTE_NAME_CHECKED, $field->getValue());
	}

	protected function getElementDropdown(FormField $field, $prefix = '') {
		// TODO: extract generic code
		$select = (new ElementContent(Element::NAME_SELECT))
			->setId('form_' . $this->form->getKey() . '_' . $prefix . $field->getName())
			->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName());

		if ($field instanceof DropDownField) {
			/** @var DropDownField $field */
			foreach ($field->getValues() as $value => $label) {
				$select->addContent((new ElementContent(Element::NAME_OPTION))
					->setAttribute(Element::ATTRIBUTE_NAME_VALUE, $value)
					->booleanAttribute(Element::ATTRIBUTE_NAME_SELECTED, $field->getValue() == $value)
					->addContent($label)
				);
			}
		}

		return $select;
	}

	protected function getElementRadioGroup(FormField $field, $prefix = '') {
		$group = new ElementContent(Element::NAME_DIV);

		if ($field instanceof RadioField) {
			/** @var RadioField $field */
			$index = 0;
			foreach ($field->getValues() as $value => $label) {
				$id = 'form_' . $this->form->getKey() . '_' . $prefix . $field->getName() . '_' . $index++;
				$group
					->addContent((new ElementEmpty(Element::NAME_INPUT))
						->setId($id)
						->setAttribute(Element::ATTRIBUTE_NAME_TYPE, Element::ATTRIBUTE_VALUE_TYPE_RADIO)
						->setAttribute(Element::ATTRIBUTE_NAME_NAME, $prefix . $field->getName())
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

		if (!($field instanceof FormField)) {
			$field = $this->form->getFirstFieldByName($field);
		}

		// TODO: has errors
		switch ($field->getType()) {

			case HiddenField::TYPE_HIDDEN:
				$element = $this->getElementHidden($field, $prefix);
				break;

			case TextField::TYPE_TEXT:
				$element = $this->getElementText($field, $prefix);
				break;

			case EmailField::TYPE_EMAIL:
				$element = $this->getElementEmail($field, $prefix);
				break;

			case PasswordField::TYPE_PASSWORD:
				$element = $this->getElementPassword($field, $prefix);
				break;

			case CheckboxField::TYPE_CHECKBOX:
				$element = $this->getElementCheckbox($field, $prefix);
				break;

			case CountrySelector::TYPE_COUNTRY:
			case DropDownField::TYPE_DROP_DOWN:
				$element = $this->getElementDropdown($field, $prefix);
				break;

			case RadioField::TYPE_RADIO:
				$element = $this->getElementRadioGroup($field, $prefix);
				break;

			default:
				$element = $this->getElementDefault($field, $prefix);
		}

		$element->setAttributes($attributes, true);

		if (\count($field->getErrors())) {
			$element->addClass($this->getClassHasErrors());
		}

		return $element;
	}
}