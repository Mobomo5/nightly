<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/10/14
 * Time: 11:39 PM
 */
require_once(BLOCK_INTERFACE_FILE);

class homepage implements block {
    private $title;

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        if (empty($inTitle)) {
            return false;
        }
        $this->title = $inTitle;
        return true;
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

    public function getType() {
        return 'homepageBlock';
    }
}