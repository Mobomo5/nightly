<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:54 PM
 */
class setPermissionsBlock implements block {
    //@todo: can we find a way to override the region in the block?
    private $content;
    private $title;

    public function __construct() {
        $this->content = 'Here is how you control who can do what and how!';
        $this->title = "Set Permissions";
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