<?php

interface module {
    public function __construct();
    public static function getPageType();
    public function getPageContent();
    public function noGUI();
    public function getReturnPage();
}