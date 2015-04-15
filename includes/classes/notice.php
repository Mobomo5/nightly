<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/16/14
 * Time: 9:30 PM
 */
class notice {
    private $style;
    private $message;
    private $removeOnceDisplayed;
    public function __construct($style = 'neutral', $message = '', $removeOnceDisplayed = true) {
        $this->style = preg_replace('/\s+/', '', strip_tags($style));
        $this->message = strip_tags($message);
        $this->removeOnceDisplayed = $removeOnceDisplayed;
    }
    public function getMessage() {
        return $this->message;
    }
    public function setMessage($message) {
        $this->message = strip_tags($message);
    }
    public function getStyle() {
        return $this->style;
    }
    public function setStyle($style = 'neutral') {
        $this->style = preg_replace('/\s+/', '', strip_tags($style));
    }
    public function removeOnceDisplayed() {
        return $this->removeOnceDisplayed;
    }
    public function __toString() {
        $noticeHTML = '<div class="' . $this->style . 'Notice notice">';
        $noticeHTML .= "<p>{$this->message}</p>";
        $noticeHTML .= '</div>';
        return $noticeHTML;
    }
}