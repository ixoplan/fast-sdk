<?php

namespace Ixolit\CDE\Form;


use Ixolit\CDE\CDETemporaryDataStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CollectedCookieFormProcessor
 *
 * @package Ixolit\CDE\Form
 */
class CollectionCookieFormProcessor extends CookieFormProcessor {

    const TIMEOUT_SESSION_COOKIE = 0;

    /** @var string */
    private $formCollectionName;

    /**
     * @var CDETemporaryDataStorage
     */
    private $dataStorage;

    /**
     * @param $formCollectionName
     */
    public function __construct($formCollectionName, CDETemporaryDataStorage $dataStorage = null) {
        $this->formCollectionName = $formCollectionName . '-forms';
        $this->dataStorage = $dataStorage;
    }

    /**
     * @return string
     */
    protected function getFormCollectionName() {
        return $this->formCollectionName;
    }

    /**
     * {@inheritdoc}
     */
    public function store(Form $form, ResponseInterface $response) {
        $dataset = $this->getFormDataToStore($form);

        $this->addFormDataToCollection($form->getKey(), $dataset);

        return $response;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function storeForm(Form $form) {
        $dataset = $this->getFormDataToStore($form);

        return $this->addFormDataToCollection($form->getKey(), $dataset);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(Form $form, ResponseInterface $response) {
        $this->removeFormDataFromCollection($form->getKey());

        return $response;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function cleanupForm(Form $form) {
        return $this->removeFormDataFromCollection($form->getKey());
    }

    /**
     * {@inheritdoc}
     */
    public function restore(Form $form, ServerRequestInterface $request) {
        $data = $this->restoreFormDataFromCollection($form->getKey());

        return $this->setRestoredFormData($form, $data);
    }

    /**
     * @param string $formName
     *
     * @return array
     */
    public function readStoredFormData($formName) {
        $storedData = $this->getDataStorage()->read($this->getFormCollectionName());

        if (isset($storedData[$formName])) {
            return $storedData[$formName];
        }

        return [];
    }

    /**
     * @return $this
     */
    public function cleanFormCollection() {
        $this->getDataStorage()->delete($this->getFormCollectionName());

        return $this;
    }

    /**
     * @param string $formName
     * @param array  $data
     *
     * @return $this
     */
    protected function addFormDataToCollection($formName, array $data = []) {
        $storedData = $this->getDataStorage()->read($this->getFormCollectionName());

        if (!\is_array($storedData)) {
            $storedData = [];
        }

        $storedData[$formName] = $data;

        $this->getDataStorage()->write($this->getFormCollectionName(), $storedData);

        return $this;
    }

    /**
     * @param string $formName
     *
     * @return $this
     */
    protected function removeFormDataFromCollection($formName) {
        $formCollection = $this->getDataStorage()->read($this->getFormCollectionName());

        unset($formCollection[$formName]);

        $this->getDataStorage()->write($this->getFormCollectionName(), $formCollection);

        return $this;
    }

    /**
     * @param string $formName
     *
     * @return array
     */
    protected function restoreFormDataFromCollection($formName) {
        $storedData = $this->getDataStorage()->read($this->getFormCollectionName());

        $formData = [];
        if (isset($storedData[$formName])) {
            $formData = $storedData[$formName];

            unset($storedData[$formName]);
        }

        $this->getDataStorage()->write($this->getFormCollectionName(), $storedData);

        return $formData;
    }

    /**
     * @return CDETemporaryDataStorage
     */
    protected function getDataStorage() {

        if (!$this->dataStorage) {
            $this->dataStorage = CDETemporaryDataStorage::getInstance(self::TIMEOUT_SESSION_COOKIE);
        }

        return $this->dataStorage;

    }

}