<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 5:19 PM
 */

require_once(NODE_INTERFACE_FILE);


class home implements node {

    private $title;
    private $content;
    private $pageAuthorIsVisible;
    private $pageAuthor;
    private $datePagePublishedIsVisible;
    private $datePagePublished;
    private $nodeType;
    private $noGui;
    private $returnPage;
    private $statusesAreVisible;
    private $statuses;

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public function getTitle() {
        return "home!";
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

    public function setTitle($inTitle) {
        $this->title = $inTitle;
    }
}