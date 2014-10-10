<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/28/14
 * Time: 5:13 PM
 */
require_once(BLOCK_INTERFACE_FILE);
class page implements block {
    private $title;
    private $content;
    public function __construct() {
        $this->title = 'Node';
        $this->content = '';
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        $this->title = strip_tags(trim($inTitle));
    }
    public function getContent() {
        return $this->content;
    }
    public function setContent($inContent) {
        $this->content = $inContent;
    }
    public function getType() {
        return get_class(self);
    }
}