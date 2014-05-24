<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/13/14
 * Time: 11:21 PM
 */

require_once(BLOCK_INTERFACE_FILE);

class fourOhFourMain implements block {
    private $content;
    private $title;

    public function __construct() {
        $this->title = "OOPS!";
        $this->content = 'We dun goofed.';
    }

    public function getTitle() {
        return $this->title;
    }

    public function getContent() {
        return $this->content;
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

    public function setTitle($inTitle) {
        $this->title = $inTitle;
    }
}