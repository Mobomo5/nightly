<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/2/14
 * Time: 9:25 PM
 */
class menuBlock implements block {

    private $content;

    public function __construct($inBlockID) {
        $this->content = menuEngine::getInstance()->getMenu(1);
    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
    }

    public function getType() {
        return get_class(self);
    }
}