<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:44 PM
 */
require_once(BLOCK_INTERFACE_FILE);

class loginRegion implements block {
    private $title;
    private $href;

    public function __construct() {
        $this->title = 'Login';
        $this->href = new link('test');
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        $this->title = $inTitle;
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

    public function getHref() {
        return $this->href;
    }
}