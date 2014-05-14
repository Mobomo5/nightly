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
        $this->style = $style;
        $this->message = $message;
        $this->removeOnceDisplayed = $removeOnceDisplayed;
    }
    public function getMessage() {
        return $this->message;
    }
    public function setMessage($message) {
        $this->message = $message;
    }
    public function getStyle() {
        return $this->style;
    }
    public function setStyle($style = 'neutral') {
        $this->style = $style;
    }
    public function removeOnceDisplayed() {
        return $this->removeOnceDisplayed;
    }
    public function __toString() {
        $noticeHTML = '<div class=" . $this->style . ">';
            $noticeHTML .= "<p>{$this->message}</p>";
        $noticeHTML .= '</div>';
        return $noticeHTML;
    }
}