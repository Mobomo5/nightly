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
    public function getNode($nodeID) {
        if (!is_numeric($nodeID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $nodeID = $database->escapeString($nodeID);
        $results = $database->getData('title, nodeType', 'node', "nodeID={$nodeID}");
        if ($results == false) {
            return false;
        }
        if ($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $nodeType = $this->getNodeType($results[0]['nodeType']);
        if($nodeType == false) {
            return false;
        }
        $fieldRevisions = array();
        foreach($nodeType->getNodeFields() as $nodeFieldType) {
            $fieldRevision = $this->getNodeFieldRevisionsForNode($nodeID, $nodeType);
            if($fieldRevisions == false) {
                continue;
            }
            $fieldRevisions = array_merge($fieldRevisions, $fieldRevision);
        }
        return new node($nodeID, $results[0]['title'], $nodeType, $fieldRevisions);
    }
    public function deleteNode(node $nodeToDelete) {
        $nodeType = $nodeToDelete->getNodeType()->getID();
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('deleteAllNodesOfType' . $nodeType)) {
            if(! $permissionEngine->currentUserCanDo('deleteOwnNodesOfType' . $nodeType)) {
                return false;
            }
            $currentUser = currentUser::getUserSession();
            if($currentUser->getUserID() != $nodeToDelete->getAuthorID()) {
                return false;
            }
        }
        $fieldRevisions = $nodeToDelete->getFields();
        //Remove all fields first.
        foreach($fieldRevisions as $field) {
            $deleted = $this->deleteFieldRevision($field);
            if($deleted == true) {
                continue;
            }
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $nodeToDelete->getID();
        $id = $database->escapeString($id);
        $success = $database->removeData('node', "nodeID={$id}");
        if(! $success) {
            return false;
        }
        return true;
    }
    public function getNodeType($inNodeTypeID) {
        if (!is_numeric($inNodeTypeID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inNodeTypeID = $database->escapeString($inNodeTypeID);
        $results = $database->getData('nodeTypeID, humanName, module, description', 'nodeType', "nodeTypeID={$inNodeTypeID}");
        if ($results == false) {
            return false;
        }
        if ($results == null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $moduleEngine = moduleEngine::getInstance();
        $moduleData = $moduleEngine->getRawModuleDataFromDatabase($results[0]['module']);
        if ($moduleData == false) {
            return false;
        }
        $moduleName = $moduleData['moduleName'];
        if (!$moduleEngine->moduleExists($moduleName)) {
            $moduleName = 'node';
        }
        $nodeFieldTypes = $this->getNodeFieldTypesForNodeType($results[0]['nodeTypeID']);
        if($nodeFieldTypes == false) {
            return false;
        }
        return new nodeType($results[0]['nodeTypeID'], $results[0]['humanName'], $moduleName, $results[0]['inDescription'], $nodeFieldTypes);
    }
    public function getNodeTypeOfNode($nodeID) {
        if(! is_numeric($nodeID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $nodeID = $database->escapeString($nodeID);
        $results = $database->getData('nodeType', 'node', "nodeID={$nodeID}");
        if ($results == false) {
            return false;
        }
        if ($results == null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        return $this->getNodeType($results[0]['nodeType']);
    }
    public function getNodeFieldTypesForNodeType($inNodeTypeID) {
        if (!is_numeric($inNodeTypeID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inNodeTypeID = $database->escapeString($inNodeTypeID);
        $results = $database->getData('nft.fieldName, nft.dataType, nft.validator, nft.validatorOptions, nft.sanitizer, nft.parameterForData, nft.sanitizerOptions', 'nodeFieldType nft, nodeField nf', "nf.nodeFieldType = nft.fieldName AND nf.nodeType={$inNodeTypeID} ORDER BY nf.weight DESC");
        if ($results == false) {
            return false;
        }
        if ($results == null) {
            return false;
        }
        $toReturn = array();
        foreach ($results as $row) {
            $validatorOptions = unserialize($row['validatorOptions']);
            if($validatorOptions == false) {
                continue;
            }
            if(! is_array($validatorOptions)) {
                continue;
            }
            $sanitizerOptions = unserialize($row['sanitizerOptions']);
            if($sanitizerOptions == false) {
                continue;
            }
            if(! is_array($sanitizerOptions)) {
                continue;
            }
            $toReturn[] = new nodeFieldType($row['fieldName'], $row['dataType'], $row['validator'], $validatorOptions, $row['sanitizer'], $row['parameterForData'], $sanitizerOptions);
        }
        return $toReturn;
    }
    public function getNodeFieldRevisionsForNode($inNodeID, nodeFieldType $nodeFieldType) {
        if(! is_numeric($inNodeID)) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inNodeID = $database->escapeString($inNodeID);
        $nodeFieldName = $nodeFieldType->getFieldName();
        $nodeFieldName = $database->escapeString($nodeFieldName);
        $results = $database->getData('revisionID, content, timePosted, authorID, nodeID, nodeFieldType, isCurrent', 'nodeFieldRevisions', "nodeID={$inNodeID} AND nodeFieldType='{$nodeFieldName}'");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $toReturn = array();
        foreach($results as $row) {
            if($row['isCurrent'] == 1) {
                $isCurrent = true;
            } else {
                $isCurrent = false;
            }
            $toReturn[] = new nodeFieldRevision($row['revisionID'], $row['content'], new DateTime($row['timePosted']), $row['authorID'], $row['nodeID'], $nodeFieldType, $isCurrent);
        }
        return $toReturn;
    }
    public function addNode(node $nodeToAdd) {
        $nodeType = $nodeToAdd->getNodeType()->getID();
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('addNodesOfType' . $nodeType)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $title = $nodeToAdd->getTitle();
        $title = $database->escapeString($title);
        $nodeType = $nodeToAdd->getNodeType();
        $nodeTypeID = $nodeType->getID();
        $nodeTypeID = $database->escapeString($nodeTypeID);
        $success = $database->insertData('node', 'title, nodeType', "'{$title}', {$nodeTypeID}");
        if(! $success) {
            return false;
        }
        return true;
    }
    public function saveNode(node $nodeToSave) {
        $nodeType = $nodeToSave->getNodeType()->getID();
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('saveAllNodesOfType' . $nodeType)) {
            if(! $permissionEngine->currentUserCanDo('saveOwnNodesOfType' . $nodeType)) {
                return false;
            }
            $currentUser = currentUser::getUserSession();
            if($currentUser->getUserID() != $nodeToSave->getAuthorID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $title = $nodeToSave->getTitle();
        $title = $database->escapeString($title);
        $id = $nodeToSave->getID();
        $id = $database->escapeString($id);
        $success = $database->updateTable('node', "title='{$title}'", "nodeID={$id}");
        if(! $success) {
            return false;
        }
        return true;
    }
    public function addFieldRevision(nodeFieldRevision $revisionToAdd) {
        $nodeType = $this->getNodeTypeOfNode($revisionToAdd->getNodeID());
        if($nodeType == false) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('reviseAllNodesOfType' . $nodeType->getID())) {
            if(! $permissionEngine->currentUserCanDo('reviseOwnNodesOfType' . $nodeType->getID())) {
                return false;
            }
            $currentUser = currentUser::getUserSession();
            if($currentUser->getUserID() != $revisionToAdd->getAuthorID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $content = $revisionToAdd->getContent();
        $content = $database->escapeString($content);
        $timePosted = $revisionToAdd->getTimePosted();
        $authorID = $revisionToAdd->getAuthorID();
        $authorID = $database->escapeString($authorID);
        $nodeID = $revisionToAdd->getNodeID();
        $nodeID = $database->escapeString($nodeID);
        $nodeFieldType = $revisionToAdd->getFieldType()->getFieldName();
        $nodeFieldType = $database->escapeString($nodeFieldType);
        $boolIsCurrent = $revisionToAdd->isCurrent();
        if($boolIsCurrent == true) {
            $isCurrent = 1;
        } else {
            $isCurrent = 0;
        }
        $success = $database->insertData('nodeFieldRevision', 'content, timePosted, authorID, nodeID, nodeFieldType, isCurrent', "'{$content}', '{$timePosted}', {$authorID}, {$nodeID}, '{$nodeFieldType}', {$isCurrent}");
        if($success == false) {
            return false;
        }
        return true;
    }
    public function saveFieldRevision(nodeFieldRevision $revisionToSave) {
        $nodeType = $this->getNodeTypeOfNode($revisionToSave->getNodeID());
        if($nodeType == false) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('reviseAllNodesOfType' . $nodeType->getID())) {
            if(! $permissionEngine->currentUserCanDo('reviseOwnNodesOfType' . $nodeType->getID())) {
                return false;
            }
            $currentUser = currentUser::getUserSession();
            if($currentUser->getUserID() != $revisionToSave->getAuthorID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $revisionToSave->getID();
        $id = $database->escapeString($id);
        $content = $revisionToSave->getContent();
        $content = $database->escapeString($content);
        $boolIsCurrent = $revisionToSave->isCurrent();
        if($boolIsCurrent == true) {
            $isCurrent = 1;
        } else {
            $isCurrent = 0;
        }
        $success = $database->updateTable('nodeFieldRevision', "content='{$content}', isCurrent={$isCurrent}", "revisionID={$id}");
        if($success == false) {
            return false;
        }
        return true;
    }
    public function deleteFieldRevision(nodeFieldRevision $revisionToDelete) {
        $nodeType = $this->getNodeTypeOfNode($revisionToDelete->getNodeID());
        if($nodeType == false) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('revertAllRevisionsOfNodeType' . $nodeType->getID())) {
            if(! $permissionEngine->currentUserCanDo('reviseOwnRevisionsOfNodeType' . $nodeType->getID())) {
                return false;
            }
            $currentUser = currentUser::getUserSession();
            if($currentUser->getUserID() != $revisionToDelete->getAuthorID()) {
                return false;
            }
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $revisionToDelete->getID();
        $id = $database->escapeString($id);
        $success = $database->removeData('nodeFieldRevision', "revisionID={$id}");
        if(! $success) {
            return false;
        }
        return true;
    }
    public function addNodeFieldType() {

    }

    //@ToDo: getting, saving, deleting, and adding every kind of node object.
}