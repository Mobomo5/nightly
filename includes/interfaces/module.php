<?php

interface module {
    public function __construct();
    public static function getPageType();
    public function getPageContent();
    public function getTitle();
    public function noGUI();
    public function getReturnPage();
    public function forceFourOhFour();
}