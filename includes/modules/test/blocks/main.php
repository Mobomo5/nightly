<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 11:11 PM
 */
require_once(BLOCK_INTERFACE_FILE);

class main implements block {
    private $title;
    private $content;

    public function __construct() {
        $this->content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum';
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {

        $this->title = $inTitle;
    }

    public function getContent() {
        return $this->content;
    }
}