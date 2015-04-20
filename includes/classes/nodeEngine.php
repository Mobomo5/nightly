<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 19/04/14
 * Time: 11:05 AM
 */
require_once(DATABASE_OBJECT_FILE);
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
    private $foundNodeFieldTypes;
    private $foundNodeFieldRevisions;
    private $foundNodeTypes;
    private $foundNodeFields;
    private $foundNodes;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nodeEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundNodeFieldTypes = array();
        $this->foundNodeFieldRevisions = array();
        $this->foundNodeTypes = array();
        $this->foundNodeFields = array();
        $this->foundNodes = array();
    }
    public function getNodeFieldType($fieldName) {
        if(isset($this->foundNodeFieldTypes[$fieldName])) {
            return $this->foundNodeFieldTypes[$fieldName];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $fieldName = $database->escapeString(preg_replace('/\s+/', '', strip_tags($fieldName)));
        $results = $database->getData('*', 'nodeFieldType', "fieldName='{$fieldName}'");
        if($results === false) {
            return false;
        }
        if($results === null) {
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
        $toReturn = new nodeFieldType($fieldTypeData['fieldName'], $fieldTypeData['dataType'], $fieldTypeData['validator'], $validatorArray, $fieldTypeData['sanitizer'], $fieldTypeData['parameterForData'], $sanitizerArray);
        $this->foundNodeFieldTypes[$toReturn->getFieldName()] = $toReturn;
        return $toReturn;
    }
    public function addNodeFieldType(nodeFieldType $toAdd) {
        if(! $toAdd->validatorIsValid()) {
            return false;
        }
        if(! $toAdd->sanitizerIsValid()) {
            return false;
        }
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
        if(! $toEdit->validatorIsValid()) {
            return false;
        }
        if(! $toEdit->sanitizerIsValid()) {
            return false;
        }
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
        if($results === false) {
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
        if($results === false) {
            return false;
        }
        return true;
    }
    public function getNodeFieldRevision($revisionID) {
        if(! is_numeric($revisionID)) {
            return false;
        }
        if(isset($this->foundNodeFieldRevisions[$revisionID])) {
            return $this->foundNodeFieldRevisions[$revisionID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $revisionID = $database->escapeString($revisionID);
        $data = $database->getData('*', 'nodeFieldRevision', "revisionID={$revisionID}");
        if($data === null) {
            return false;
        }
        if($data === false) {
            return false;
        }
        if(count($data) > 1) {
            return false;
        }
        $fieldRevisionData = $data[0];
        $date = new DateTime($fieldRevisionData['timePosted']);
        $nodeFieldType = $this->getNodeFieldType($fieldRevisionData['nodeFieldType']);
        if($nodeFieldType === false) {
            return false;
        }
        if($fieldRevisionData['isCurrent'] === 1) {
            $toReturn = new nodeFieldRevision($fieldRevisionData['revisionID'], $fieldRevisionData['content'], $date, $fieldRevisionData['authorID'], $fieldRevisionData['nodeID'], $nodeFieldType);
            $this->foundNodeFieldRevisions[$toReturn->getID()] = $toReturn;
            return $toReturn;
        }
        $toReturn = new nodeFieldRevision($fieldRevisionData['revisionID'], $fieldRevisionData['content'], $date, $fieldRevisionData['authorID'], $fieldRevisionData['nodeID'], $nodeFieldType, false);
        $this->foundNodeFieldRevisions[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function getNodeFieldRevisionsForNode($nodeID) {
        if(! is_numeric($nodeID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $nodeID = $database->escapeString($nodeID);
        $rawData = $database->getData('*', 'nodeFieldRevision', "nodeID={$nodeID}");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        $toReturn = array();
        foreach($rawData as $rawDataForRevision) {
            if(isset($this->foundNodeFieldRevisions[$rawDataForRevision['revisionID']])) {
                $toReturn[] = $this->foundNodeFieldRevisions[$rawDataForRevision['revisionID']];
                continue;
            }
            $date = new DateTime($rawDataForRevision['timePosted']);
            $nodeFieldType = $this->getNodeFieldType($rawDataForRevision['nodeFieldType']);
            if($nodeFieldType === false) {
                continue;
            }
            if($rawDataForRevision['isCurrent'] === 1) {
                $nodeField = new nodeFieldRevision($rawDataForRevision['revisionID'], $rawDataForRevision['content'], $date, $rawDataForRevision['authorID'], $rawDataForRevision['nodeID'], $nodeFieldType);
                $this->foundNodeFieldRevisions[$nodeField->getID()] = $nodeField;
                $toReturn[] = $nodeField;
                continue;
            }
            $nodeField = new nodeFieldRevision($rawDataForRevision['revisionID'], $rawDataForRevision['content'], $date, $rawDataForRevision['authorID'], $rawDataForRevision['nodeID'], $nodeFieldType, false);
            $this->foundNodeFieldRevisions[$nodeField->getID()] = $nodeField;
            $toReturn[] = $nodeField;
        }
        return $toReturn;
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
            if($nodeData === false) {
                return false;
            }
            if($nodeData === null) {
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
        if($result === false) {
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
            if($nodeData === false) {
                return false;
            }
            if($nodeData === null) {
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
        if($result === false) {
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
            if($nodeData === false) {
                return false;
            }
            if($nodeData === null) {
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
    public function getNodeType($nodeTypeID) {
        if(! is_numeric($nodeTypeID)) {
            return false;
        }
        if(isset($this->foundNodeTypes[$nodeTypeID])) {
            return $this->foundNodeTypes[$nodeTypeID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $nodeTypeID = $database->escapeString($nodeTypeID);
        $results = $database->getData('nt.nodeTypeID, nt.humanName, m.moduleName, nt.description', 'nodeType nt, module m', "nt.module=m.moduleID AND nt.nodeTypeID={$nodeTypeID}");
        if($results === null) {
            return false;
        }
        if($results === false) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $nodeFieldTypesRawData = $database->getData('nft.fieldName', 'nodeFieldType nft, nodeField nf', "nft.fieldName=nf.nodeFieldType AND nf.nodeType={$nodeTypeID} ORDER BY nf.weight, nf.id, nft.fieldName");
        if(! is_array($nodeFieldTypesRawData)) {
            //Not saving because an error happened.
            return new nodeType($results[0]['nodeTypeID'], $results[0]['humanName'], $results[0]['moduleName'], $results[0]['description'], array());
        }
        $nodeFieldTypes = array();
        foreach($nodeFieldTypesRawData as $nodeFieldTypeRawData) {
            $nodeFieldType = $this->getNodeFieldType($nodeFieldTypeRawData['fieldName']);
            if($nodeFieldType === false) {
                continue;
            }
            $nodeFieldTypes[] = $nodeFieldType;
        }
        $toReturn = new nodeType($results[0]['nodeTypeID'], $results[0]['humanName'], $results[0]['moduleName'], $results[0]['description'], $nodeFieldTypes);
        $this->foundNodeTypes[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function editNodeType(nodeType $toEdit) {
        if(! $toEdit->moduleIsValid()) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canEditNodeTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toEdit->getID());
        $humanName = $database->escapeString(strip_tags($toEdit->getHumanName()));
        $moduleName = $database->escapeString($toEdit->getModuleInCharge());
        $description = $database->escapeString($toEdit->getDescription());
        $moduleIDData = $database->getData('moduleID', 'module', "moduleName={$moduleName}");
        if(! is_array($moduleIDData)) {
            return false;
        }
        $moduleID = $moduleIDData[0]['moduleID'];
        $result = $database->updateTable('nodeType', "humanName='{$humanName}', module={$moduleID}, description='{$description}'", "nodeTypeID={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function addNodeType(nodeType $toAdd) {
        if(! $toAdd->moduleIsValid()) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canAddNodeTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $humanName = $database->escapeString(strip_tags($toAdd->getHumanName()));
        $moduleName = $database->escapeString($toAdd->getModuleInCharge());
        $description = $database->escapeString($toAdd->getDescription());
        $moduleIDData = $database->getData('moduleID', 'module', "moduleName={$moduleName}");
        if(! is_array($moduleIDData)) {
            return false;
        }
        $moduleID = $moduleIDData[0]['moduleID'];
        $result = $database->insertData('nodeType', 'humanName, module, description', "'{$humanName}', {$moduleID}, '{$description}'");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function deleteNodeType(nodeType $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canDeleteNodeTypes')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toDelete->getID());
        //Check if any data in the db depends on this nodeType
        $dataExists = $database->getData('id', 'nodeField', "nodeType={$id}");
        if($dataExists != null) {
            return false;
        }
        $dataExists = $database->getData('nodeID', 'node', "nodeType={$id}");
        if($dataExists != null) {
            return false;
        }
        $result = $database->removeData('nodeType', "nodeTypeID={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function getNodeField($nodeFieldID) {
        if(! is_numeric($nodeFieldID)) {
            return false;
        }
        if(isset($this->foundNodeFields[$nodeFieldID])) {
            return $this->foundNodeFields[$nodeFieldID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $nodeFieldID = $database->escapeString($nodeFieldID);
        $rawData = $database->getData('*', 'nodeField', "id={$nodeFieldID}");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $nodeFieldType = $this->getNodeFieldType($rawData[0]['nodeFieldType']);
        if($nodeFieldType === false) {
            return false;
        }
        $nodeType = $this->getNodeType($rawData[0]['nodeType']);
        if($nodeType === false) {
            return false;
        }
        $toReturn = new nodeField($rawData[0]['id'], $nodeFieldType, $nodeType, $rawData[0]['weight']);
        $this->foundNodeFields[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function addNodeField(nodeField $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canAddNodeFields')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $nodeFieldType = $toAdd->getNodeFieldType();
        $nodeFieldTypeID = $nodeFieldType->getFieldName();
        $nodeFieldTypeID = $database->escapeString(preg_replace('/\s+/', '', strip_tags($nodeFieldTypeID)));
        $nodeType = $toAdd->getNodeType();
        $nodeTypeID = $nodeType->getID();
        if(! is_numeric($nodeTypeID)) {
            return false;
        }
        $nodeTypeID = $database->escapeString($nodeTypeID);
        $weight = $toAdd->getWeight();
        if(! is_numeric($weight)) {
            return false;
        }
        $weight = $database->escapeString($weight);
        $result = $database->insertData('nodeField', 'nodeFieldType, nodeType, weight', "'{$nodeFieldTypeID}', {$nodeTypeID}, {$weight}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function editNodeField(nodeField $toEdit) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canEditNodeFields')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toEdit->getID();
        if(! is_numeric($id)) {
            return false;
        }
        $id = $database->escapeString($id);
        $weight = $toEdit->getWeight();
        if(! is_numeric($weight)) {
            return false;
        }
        $weight = $database->escapeString($weight);
        $result = $database->updateTable('nodeField', "weight={$weight}", "id={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function deleteNodeField(nodeField $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canDeleteNodeFields')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toDelete->getID();
        if(! is_numeric($id)) {
            return false;
        }
        $id = $database->escapeString($id);
        $result = $database->removeData('nodeField', "id={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function getNode($id) {
        if(! is_numeric($id)) {
            return false;
        }
        if(isset($this->foundNodes[$id])) {
            return $this->foundNodes[$id];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($id);
        $rawData = $database->getData('*', 'node', "id={$id}");
        if($rawData === false) {
            return false;
        }
        if($rawData === null) {
            return false;
        }
        if(count($rawData) > 1) {
            return false;
        }
        $nodeType = $this->getNodeType($rawData[0]['nodeType']);
        if($nodeType === false) {
            return false;
        }
        $fieldRevisions = $this->getNodeFieldRevisionsForNode($id);
        if($fieldRevisions === false) {
            return false;
        }
        $toReturn = new node($rawData[0]['nodeID'], $rawData[0]['title'], $rawData[0]['author'], $nodeType, $fieldRevisions);
        $this->foundNodes[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function addNode(node $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canAddNodesOfType' . preg_replace('/\s+/', '', $toAdd->getNodeType()->getHumanName()))) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $title = $database->escapeString(strip_tags($toAdd->getTitle()));
        $author = $database->escapeString($toAdd->getAuthorID());
        $nodeTypeID = $database->escapeString($toAdd->getNodeType()->getID());
        $result = $database->insertData('node', 'title, nodeType, authorID', "'{$title}', {$author}, {$nodeTypeID}");
        if($result === false) {
            return false;
        }
        $lastInsertID = $database->getLastInsertID();
        $fieldRevisions = $toAdd->getFields();
        $completed = array();
        foreach($fieldRevisions as $fieldRevision) {
            $fieldRevision->setNodeID($lastInsertID);
            $added = $this->addNodeFieldRevision($fieldRevision);
            if($added === false) {
                $this->removeBulkFieldRevisions($completed);
                return false;
            }
            $completed[] = $fieldRevision;
        }
    }
    public function editNode(node $toEdit) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canEditAllNodesOfType' . preg_replace('/\s+/', '', $toEdit->getNodeType()->getHumanName()))) {
            if(! $permissionEngine->currentUserCanDo('canEditOwnNodesOfType' . preg_replace('/\s+/', '', $toEdit->getNodeType()->getHumanName()))) {
                return false;
            }
            if($toEdit->getAuthorID() != currentUser::getUserSession()->getUserID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toEdit->getID();
        if(! is_numeric($id)) {
            return false;
        }
        $id = $database->escapeString($id);
        $title = $database->escapeString(strip_tags($toEdit->getTitle()));
        $result = $database->updateTable('node', "title='{$title}'", "nodeID={$id}");
        if($result === false) {
            return false;
        }
        return true;
    }
    public function deleteNode(node $toRemove) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('canDeleteAllNodesOfType' . preg_replace('/\s+/', '', $toRemove->getNodeType()->getHumanName()))) {
            if(! $permissionEngine->currentUserCanDo('canDeleteOwnNodesOfType' . preg_replace('/\s+/', '', $toRemove->getNodeType()->getHumanName()))) {
                return false;
            }
            if($toRemove->getAuthorID() != currentUser::getUserSession()->getUserID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $toRemove->getID();
        if(! is_numeric($id)) {
            return false;
        }
        $removedRevisions = $database->removeData('nodeFieldRevisions', "nodeID={$id}");
        if($removedRevisions === false) {
            return false;
        }
        $modifiedFiles = $database->updateTable('file', 'nodeID=0', "nodeID={$id}");
        if($modifiedFiles === false) {
            return false;
        }
        $removedAssignmentMarked = $database->removeData('assignmentMark', "assignmentID={$id}");
        if($removedAssignmentMarked === false) {
            return false;
        }
        $removedGroupMembers = $database->removeData('groupMember', "nodeID={$id}");
        if($removedGroupMembers === false) {
            return false;
        }
        $removedMessages = $database->removeData('message m, messageRecipient mr', "mr.messageID = m.messageID AND m.nodeID={$id}");
        if($removedMessages === false) {
            return false;
        }
        $statusesToRemove = $database->getData('statusID', 'status', "nodeID={$id}");
        if($statusesToRemove === false) {
            return false;
        }
        foreach($statusesToRemove as $status) {
            //@ToDo: delete statuses on the node individually. Status System has to be rewritten first.
        }
        $nodeRemoved = $database->removeData('node', "nodeID={$id}");
        if($nodeRemoved === false) {
            return false;
        }
        return true;
    }
    private function removeBulkFieldRevisions(array $fieldRevisionsToRemove) {
        foreach($fieldRevisionsToRemove as $fieldRevision) {
            $this->deleteNodeFieldRevision($fieldRevision);
        }
    }
 }