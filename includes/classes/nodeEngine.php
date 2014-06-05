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
        $fieldRevisions = $this->getNodeFieldRevisionsForNode($nodeID, $nodeType);
        if($fieldRevisions == false) {
            return false;
        }
        return new node($nodeID, $results[0]['title'], $nodeType, $fieldRevisions);
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
            $sanitizerOptions = unserialize($row['sanitizerOptions']);
            if($sanitizerOptions == false) {
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
}