<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 19/04/14
 * Time: 11:05 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(NODE_ENGINE_OBJECT_FILE);
require_once(NODE_OBJECT_FILE);
require_once(NODE_TYPE_OBJECT_FILE);
require_once(NODE_FIELD_REVISION_OBJECT_FILE);
require_once(NODE_FIELD_TYPE_OBJECT_FILE);
require_once(NODE_FIELD_OBJECT_FILE);
require_once(MODULE_ENGINE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
class nodeEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nodeEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function getNodeFieldType($fieldName) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $fieldName = $database->escapeString(preg_replace('/\s+/', '', strip_tags($fieldName)));
        $results = $database->getData('*', 'nodeFieldType', "fieldName='{$fieldName}'");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $fieldTypeData = $results[0];
        $validatorArray = unserialize($fieldTypeData['validatorOptions']);
        if(! is_array($validatorArray)) {
            return false;
        }
        $sanitizerArray = unserialize($fieldTypeData['sanitizerOptions']);
        if(! is_array($sanitizerArray)) {
            return false;
        }
        return new nodeFieldType($fieldTypeData['fieldName'], $fieldTypeData['dataType'], $fieldTypeData['validator'], $validatorArray, $fieldTypeData['sanitizer'], $fieldTypeData['parameterForData'], $sanitizerArray);
    }
    public function addNodeFieldType(nodeFieldType $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canAddNodeFieldTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $fieldName = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toAdd->getFieldName())));
        $dataType = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toAdd->getDataType())));
        $validator = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toAdd->getValidator())));
        $sanitizer = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toAdd->getSanitizer())));
        $validatorOptions = $toAdd->getValidatorOptions();
        if(! is_array($validatorOptions)) {
            return false;
        }
        $validatorOptions = $database->escapeString(serialize($validatorOptions));
        $sanitizerOptions = $toAdd->getSanitizerOptions();
        if(! is_array($sanitizerOptions)) {
            return false;
        }
        $sanitizerOptions = $database->escapeString(serialize($sanitizerOptions));
        $sanitizerParameterForData = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toAdd->getSanitizerParameterForData())));
        $results = $database->insertData('nodeFieldType', 'fieldName, dataType, validator, validatorOptions, sanitizer, parameterForData, sanitizerOptions', "'{$fieldName}', '{$dataType}', '{$validator}', '{$validatorOptions}', '{$sanitizer}', '{$sanitizerParameterForData}', {$sanitizerOptions}'");
        if(! $results) {
            return false;
        }
        return true;
    }
    public function editNodeFieldType(nodeFieldType $toEdit) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canEditNodeFieldTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $fieldName = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toEdit->getFieldName())));
        $dataType = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toEdit->getDataType())));
        $validator = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toEdit->getValidator())));
        $sanitizer = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toEdit->getSanitizer())));
        $validatorOptions = $toEdit->getValidatorOptions();
        if(! is_array($validatorOptions)) {
            return false;
        }
        $validatorOptions = $database->escapeString(serialize($validatorOptions));
        $sanitizerOptions = $toEdit->getSanitizerOptions();
        if(! is_array($sanitizerOptions)) {
            return false;
        }
        $sanitizerOptions = $database->escapeString(serialize($sanitizerOptions));
        $sanitizerParameterForData = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toEdit->getSanitizerParameterForData())));
        $results = $database->updateTable('nodeFieldType', "validator='{$validator}', validatorOptions='{$validatorOptions}', sanitizer='{$sanitizer}', parameterForData='{$sanitizerParameterForData}', sanitizerOptions='{$sanitizerOptions}'", "fieldName='{$fieldName}' AND dataType='{$dataType}'");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function deleteNodeFieldType(nodeFieldType $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canDeleteNodeFieldTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $fieldName = $database->escapeString(preg_replace('/\s+/', '', strip_tags($toDelete->getFieldName())));
        $results = $database->getData('revisionID', 'nodeFieldRevision', "nodeFieldType='{$fieldName}'");
        if($results != null) {
            return false;
        }
        $results = $database->getData('id', 'nodeField', "nodeFieldType='{$fieldName}'");
        if($results != null) {
            return false;
        }
        $results = $database->removeData('nodeFieldType', "fieldName='{$fieldName}'");
        if($results == false) {
            return false;
        }
        return true;
    }
}