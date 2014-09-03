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
    public function getNodeFieldRevision($revisionID) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if(! is_numeric($revisionID)) {
            return false;
        }
        $revisionID = $database->escapeString($revisionID);
        $data = $database->getData('*', 'nodeFieldRevision', "revisionID={$revisionID}");
        if($data == null) {
            return false;
        }
        if($data == false) {
            return false;
        }
        if(count($data) > 1) {
            return false;
        }
        $fieldRevisionData = $data[0];
        $date = new DateTime($fieldRevisionData['timePosted']);
        $nodeFieldType = $this->getNodeFieldType($fieldRevisionData['nodeFieldType']);
        if($nodeFieldType == false) {
            return false;
        }
        if($fieldRevisionData['isCurrent'] == 1) {
            return new nodeFieldRevision($fieldRevisionData['revisionID'], $fieldRevisionData['content'], $date, $fieldRevisionData['authorID'], $fieldRevisionData['nodeID'], $nodeFieldType);
        }
        return new nodeFieldRevision($fieldRevisionData['revisionID'], $fieldRevisionData['content'], $date, $fieldRevisionData['authorID'], $fieldRevisionData['nodeID'], $nodeFieldType, false);
    }
    public function addNodeFieldRevision(nodeFieldRevision $toAdd) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        //If the user has super privileges, continue
        if(! $permissionEngine->currentUserCanDo('canReviseAllNodes')) {
            //If the user can revise their own nodes, check if the user owns the node to edit.
            if(! $permissionEngine->currentUserCanDo('canReviseOwnNodes')) {
                return false;
            }
            $nodeID = $database->escapeString($toAdd->getNodeID());
            $nodeData = $database->getData('authorID', 'node', "nodeID={$nodeID}");
            if($nodeData == false) {
                return false;
            }
            if($nodeData == null) {
                return false;
            }
            if(count($nodeData) > 1) {
                return false;
            }
            if(currentUser::getUserSession()->getUserID != $nodeData[0]['authorID']) {
                return false;
            }
        }
        $content = $database->escapeString($toAdd->getContent());
        $timePosted = $database->escapeString($toAdd->getTimePosted()->format('Y-m-d H:i:s'));
        $authorID = $database->escapeString($toAdd->getAuthorID());
        $nodeID = $database->escapeString($toAdd->getNodeID());
        $fieldType = $database->escapeString($toAdd->getFieldType()->getFieldName());
        if($toAdd->isCurrent()) {
            $isCurrent = 1;
        } else {
            $isCurrent = 0;
        }
        $result = $database->insertData('nodeFieldRevision', 'content, timePosted, authorID, nodeID, nodeFieldType, isCurrent', "'{$content}', '{$timePosted}', {$authorID}, {$nodeID}, '{$fieldType}', {$isCurrent}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function deleteNodeFieldRevision(nodeFieldRevision $toDelete) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        //If the user has super privileges, continue
        if(! $permissionEngine->currentUserCanDo('canReviseAllNodes')) {
            //If the user can revise their own nodes, check if the user owns the node to edit.
            if(! $permissionEngine->currentUserCanDo('canReviseOwnNodes')) {
                return false;
            }
            $nodeID = $database->escapeString($toDelete->getNodeID());
            $nodeData = $database->getData('authorID', 'node', "nodeID={$nodeID}");
            if($nodeData == false) {
                return false;
            }
            if($nodeData == null) {
                return false;
            }
            if(count($nodeData) > 1) {
                return false;
            }
            if(currentUser::getUserSession()->getUserID != $nodeData[0]['authorID']) {
                return false;
            }
        }
        $id = $database->escapeString($toDelete->getID());
        $result = $database->removeData('nodeFieldRevision', "revisionID={$id}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function editNodeFieldRevision(nodeFieldRevision $toEdit) {
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        //If the user has super privileges, continue
        if(! $permissionEngine->currentUserCanDo('canReviseAllNodes')) {
            //If the user can revise their own nodes, check if the user owns the node to edit.
            if(! $permissionEngine->currentUserCanDo('canReviseOwnNodes')) {
                return false;
            }
            $nodeID = $database->escapeString($toEdit->getNodeID());
            $nodeData = $database->getData('authorID', 'node', "nodeID={$nodeID}");
            if($nodeData == false) {
                return false;
            }
            if($nodeData == null) {
                return false;
            }
            if(count($nodeData) > 1) {
                return false;
            }
            if(currentUser::getUserSession()->getUserID != $nodeData[0]['authorID']) {
                return false;
            }
        }
        $id = $database->escapeString($toEdit->getID());
        $content = $database->escapeString($toEdit->getContent());
        $timePosted = $database->escapeString($toEdit->getTimePosted()->format('Y-m-d H:i:s'));
        $authorID = $database->escapeString($toEdit->getAuthorID());
        $nodeID = $database->escapeString($toEdit->getNodeID());
        $fieldType = $database->escapeString($toEdit->getFieldType()->getFieldName());
        if($toEdit->isCurrent()) {
            $isCurrent = 1;
        } else {
            $isCurrent = 0;
        }
        $results = $database->updateTable('nodeFieldRevision', "content='{$content}', timePosted='{$timePosted}', authorID={$authorID}, nodeID={$nodeID}, nodeFieldType='{$fieldType}', isCurrent={$isCurrent}", "revisionID={$id}");
        if(! $results) {
            return false;
        }
        return true;
    }
}