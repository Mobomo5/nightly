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
    }
}