<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 03/06/14
 * Time: 9:21 PM
 */
require_once(VALIDATOR_OBJECT_FILE);
require_once(GENERAL_ENGINE_OBJECT_FILE);
class nodeFieldType {
    private $fieldName;
    private $dataType;
    private $validator;
    private $validatorOptions;
    private $sanitizer;
    private $sanitizerParameterForData;
    private $sanitizerOptions;
    public function __construct($inFieldName, $inDataType, $inValidator, array $inValidatorOptions, $inSanitizer, $inSanitizerParameterForData, array $inSanitizerOptions) {
        $inValidator = preg_replace('/\s+/', '', strip_tags($inValidator));
        $inSanitizer = preg_replace('/\s+/', '', strip_tags($inSanitizer));
        if(! $this->validateValidator($inValidator)) {
            return;
        }
        if(! $this->validateSanitizer($inSanitizer)) {
            return;
        }
        $this->fieldName = preg_replace('/\s+/', '', strip_tags($inFieldName));
        $this->dataType = preg_replace('/\s+/', '', strip_tags($inDataType));
        $this->validator = $inValidator;
        $this->validatorOptions = $inValidatorOptions;
        $this->sanitizer = $inSanitizer;
        $this->sanitizerParameterForData = preg_replace('/\s+/', '', strip_tags($inSanitizerParameterForData));
        $this->sanitizerOptions = $inSanitizerOptions;
    }
    public function getFieldName() {
        return $this->fieldName;
    }
    public function getDataType() {
        return $this->dataType;
    }
    public function getValidator() {
        return $this->validator;
    }
    public function setValidator($inValidator) {
        $inValidator = preg_replace('/\s+/', '', strip_tags($inValidator));
        if(! $this->validateValidator($inValidator)) {
            return;
        }
        $this->validator = $inValidator;
    }
    public function getSanitizer() {
        return $this->sanitizer;
    }
    public function setSanitizer($inSanitizer) {
        $inSanitizer = preg_replace('/\s+/', '', strip_tags($inSanitizer));
        if(! $this->validateSanitizer($inSanitizer)) {
            return;
        }
        $this->sanitizer = $inSanitizer;
    }
    public function getValidatorOptions() {
        return $this->validatorOptions;
    }
    public function setValidatorOptions(array $inOptions) {
        $this->validatorOptions = $inOptions;
    }
    public function getSanitizerOptions() {
        return $this->sanitizerOptions;
    }
    public function getSanitizerParameterForData() {
        return $this->sanitizerParameterForData;
    }
    public function setSanitizerParameterForData($inParameterName) {
        $this->sanitizerParameterForData = preg_replace('/\s+/', '', strip_tags($inParameterName));
    }
    public function setSanitizerOptions(array $inOptions) {
        $this->sanitizerOptions = $inOptions;
    }
    public function validateData($inData) {
        $validator = new validator($this->validator);
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inData, $this->validatorOptions)) {
            return false;
        }
        return true;
    }
    public function sanitizeData($inData) {
        $sanitizer = new general($this->sanitizer);
        if(! $sanitizer->functionsExists()) {
            return false;
        }
        $this->sanitizerOptions[$this->sanitizerParameterForData] = $inData;
        $toReturn = $sanitizer->run($this->sanitizerOptions);
        unset($this->sanitizerOptions[$this->sanitizerParameterForData]);
        return $toReturn;
    }
    private function validateValidator($inValidator) {
        $test = new validator($inValidator);
        if(! $test->validatorExists()) {
            return false;
        }
        return true;
    }
    private function validateSanitizer($inSanitizer) {
        $test = new general($inSanitizer);
        if(! $test->functionsExists()) {
            return false;
        }
        return true;
    }
}