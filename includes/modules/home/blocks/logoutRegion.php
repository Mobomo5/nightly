<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:57 PM
 */

require_once(BLOCK_INTERFACE_FILE);

class logoutRegion implements block {
    private $title;
    private $content;
    private $href;

    public function __construct() {
        $this->title = 'Log Out';
        $this->content = '';
        $this->href = new link('users/logout');
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        $this->title = strip_tags($inTitle);
    }

    public function getContent() {
        return $this->content;
    }

    public function getHref() {
        return $this->href->getHref();
    }

    public function getType() {
        return 'homepageBlock';
    }
}