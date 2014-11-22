<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:46 PM
 */
require_once(BLOCK_INTERFACE_FILE);

class default1 implements block {

    public function __construct($inBlockID) {
        // TODO: Implement __construct() method.
    }

    public function getTitle() {
        return 'testPage';
    }

    public function getHref() {
        return new link('test');
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        // TODO: Implement getContent() method.
    }

    public function getType() {
        return 'homepageBlock';
    }
}