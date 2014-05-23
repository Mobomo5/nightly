<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:46 PM
 */
require_once(NODE_INTERFACE_FILE);

class default1 implements node {

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public function getTitle() {
        return 'default dummy';
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        // TODO: Implement getContent() method.
    }

    public function pageAuthorIsVisible() {
        // TODO: Implement pageAuthorIsVisible() method.
    }

    public function datePagePublishedIsVisible() {
        // TODO: Implement datePagePublishedIsVisible() method.
    }

    public function getDatePagePublished() {
        // TODO: Implement getDatePagePublished() method.
    }

    public function getPageAuthor() {
        // TODO: Implement getPageAuthor() method.
    }

    public static function getNodeType() {
        // TODO: Implement getNodeType() method.
    }

    public function statusesAreVisible() {
        // TODO: Implement statusesAreVisible() method.
    }

    public function getStatuses() {
        // TODO: Implement getStatuses() method.
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }
}