<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/2/14
 * Time: 9:46 PM
 */
class noticeBlock implements block {
    private $content;

    public function __construct() {
        $this->content = noticeEngine::getInstance()->getNotices();
    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        if (empty($this->content)) {
            return false;
        }
        return $this->content;
    }
}