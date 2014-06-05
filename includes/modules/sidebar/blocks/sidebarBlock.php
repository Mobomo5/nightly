<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:19 PM
 */
class sidebarBlock implements block {

    private $content;

    public function __construct() {
        for ($i = 0; $i < 10; $i++) {
            $this->content[] = array('href' => $i, 'title' => 'Title' . $i);
        }
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
        return get_class($this);
    }
}