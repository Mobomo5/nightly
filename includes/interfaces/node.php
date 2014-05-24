<?php

interface node {
    public function __construct();
    public function getTitle();
    public function setTitle($inTitle);
    public function getContent();
    public function pageAuthorIsVisible();
    public function datePagePublishedIsVisible();
    public function getDatePagePublished();
    public function getPageAuthor();
    public static function getNodeType();
    public function statusesAreVisible();
    public function getStatuses();
    public function noGUI();
    public function getReturnPage();
}