<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/16/14
 * Time: 9:30 PM
 */
class Notice {
    private $style;
    private $message;
    private $removeOnceDisplayed;
    public function __construct($style = 'neutral', $message = '', $removeOnceDisplayed = true) {
        if(! is_bool($removeOnceDisplayed)) {
            $removeOnceDisplayed = true;
        }
        $this->style = preg_replace('/\s+/', '', htmlspecialchars($style));
        $this->message = htmlspecialchars($message);
        $this->removeOnceDisplayed = $removeOnceDisplayed;
    }
    public function getMessage() {
        return $this->message;
    }
    public function setMessage($message) {
        $this->message = htmlspecialchars($message);
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