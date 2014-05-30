<?php

interface module {
    public function __construct();

    public static function getPageType();

    public function noGUI();

    public function getPageContent();

    public function getReturnPage();
}