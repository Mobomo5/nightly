<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:46 PM
 */
class addStudentBlock implements block {

    private $content;
    private $title;

    public function __construct() {
        $this->content = 'Here is where you add a student';
        $this->title = 'Add a student';
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