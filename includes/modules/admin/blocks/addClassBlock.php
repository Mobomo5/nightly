<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:36 PM
 */
class addClassBlock implements block {

    private $content;
    private $title;

    public function __construct() {
        $this->content = 'This is where you add a class.';
        $this->title = 'Add a class';
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
    }

    public function getType() {
        return get_class($this);
    }
}