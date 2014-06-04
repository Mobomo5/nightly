<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 03/06/14
 * Time: 7:18 PM
 */
require_once(NODE_TYPE_OBJECT_FILE);
require_once(NODE_FIELD_REVISION_OBJECT_FILE);
class node {
    private $id;
    private $title;
    private $nodeType;
    private $fieldRevisions;
    public function __construct($id, $title, nodeType $nodeType, array $fieldRevisions = array()) {
        if(! is_numeric($id)) {
            return;
        }
        $title = strip_tags($title);
        $validatedFieldRevisions = array();
        foreach ($fieldRevisions as $fieldRevision) {
            if(! is_object($fieldRevision)) {
                continue;
            }
            $objectsClass = get_class($fieldRevision);
            if($objectsClass != 'nodeFieldRevision') {
                continue;
            }
            $validatedFieldRevisions[] = $fieldRevision;
        }
        unset($fieldRevisions);
        $this->id = $id;
        $this->title = $title;
        $this->nodeType = $nodeType;
        $this->fieldRevisions = $validatedFieldRevisions;
    }
    public function getID() {
        return $this->id;
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        $inTitle = strip_tags($inTitle);
        $this->title = $inTitle;
    }
    public function getNodeType() {
        return $this->nodeType;
    }
    public function getFields() {
        return $this->fieldRevisions;
    }
    public function setField(nodeFieldRevision $field) {
        if($field->getNodeID() != $this->id) {
            return;
        }
        foreach($this->fieldRevisions as $fieldRevision) {
            if($field->getFieldType() != $fieldRevision->getFieldType()) {
                continue;
            }
            if($field->getID() != $fieldRevision->getID()) {
                continue;
            }
            $fieldRevision->setIsCurrent(false);
            $nodeEngine = nodeEngine::getInstance();
            $nodeEngine->saveFieldRevision($fieldRevision);
            $nodeEngine->addFieldRevision($field);
            $fieldRevision = $field;
            break;
        }
    }
    public function getContent() {
        $toReturn = '';
        foreach($this->fieldRevisions as $fieldRevision) {
            $toReturn .= $fieldRevision->getContent();
        }
        return $toReturn;
    }
}