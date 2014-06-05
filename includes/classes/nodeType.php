<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 03/06/14
 * Time: 7:23 PM
 */
require_once(MODULE_ENGINE_OBJECT_FILE);
class nodeType {
    private $id;
    private $humanName;
    private $moduleInCharge;
    private $description;
    private $nodeFields;
    private $addedFields;
    private $deletedFields;
    public function __construct($inID, $inHumanName, $inModuleInCharge, $inDescription, array $inNodeFields) {
        if(! is_numeric($inID)) {
            return;
        }
        $inModuleInCharge = preg_replace('/\s+/', '', strip_tags($inModuleInCharge));
        if(! $this->validateModule($inModuleInCharge)) {
            $inModuleInCharge = 'node';
        }
        $inHumanName = strip_tags($inHumanName);
        $inDescription = strip_tags($inDescription);
        $validatedNodeFields = array();
        foreach ($inNodeFields as $nodeField) {
            if(! is_object($nodeField)) {
                continue;
            }
            $objectsClass = get_class($nodeField);
            if($objectsClass != 'nodeFieldType') {
                continue;
            }
            $validatedNodeFields[] = $nodeField;
        }
        unset($fieldRevisions);
        $this->id = $inID;
        $this->humanName = $inHumanName;
        $this->moduleInCharge = $inModuleInCharge;
        $this->description = $inDescription;
        $this->nodeFields = $validatedNodeFields;
        $this->addedFields = array();
        $this->deletedFields = array();
    }
    public function getID() {
        return $this->id;
    }
    public function getHumanName() {
        return $this->humanName;
    }
    public function setHumanName($inHumanName) {
        $inHumanName = strip_tags($inHumanName);
        $this->humanName = $inHumanName;
    }
    public function getModuleInCharge() {
        return $this->moduleInCharge;
    }
    public function setModuleInCharge($inModuleInCharge) {
        $inModuleInCharge = preg_replace('/\s+/', '', strip_tags($inModuleInCharge));
        if(! $this->validateModule($inModuleInCharge)) {
            return;
        }
        $this->moduleInCharge = $inModuleInCharge;
    }
    public function getDescription() {
        return $this->description;
    }
    public function setDescription($inDescription) {
        $this->description = strip_tags($inDescription);
    }
    public function getNodeFields() {
        return $this->nodeFields;
    }
    public function getAddedFields() {
        return $this->addedFields;
    }
    public function addNodeField(nodeFieldType $inNodeField) {
        //Get out of here if the field is already part of this nodeType.
        foreach($this->nodeFields as $nodeField) {
            if($inNodeField->getFieldName() != $nodeField->getFieldName()) {
                continue;
            }
            return;
        }
        $this->nodeFields[] = $inNodeField;
        $this->addedFields[] = $inNodeField;
    }
    public function getRemovedFields() {
        return $this->deletedFields;
    }
    public function removeNodeField(nodeFieldType $inNodeField) {
        foreach($this->nodeFields as $nodeField) {
            if($inNodeField->getFieldName() != $nodeField->getFieldName()) {
                continue;
            }
            $this->deletedFields[] = $inNodeField;
            unset($nodeField);
        }
    }
    private function validateModule($inModuleName) {
        $test = moduleEngine::getInstance();
        if(! $test->moduleExists($inModuleName)) {
            return false;
        }
        return true;
    }
 }