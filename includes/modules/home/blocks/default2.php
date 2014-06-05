<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:57 PM
 */

require_once(BLOCK_INTERFACE_FILE);

class default2 implements block {

    public function __construct() {
        // TODO: Implement __construct() method.
    }

    public function getTitle() {
        return 'title';
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