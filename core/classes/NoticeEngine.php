<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/16/14
 * Time: 9:31 PM
 */
class NoticeEngine {
    private $notices;
    public static function getInstance() {
        if(! isset($_SESSION['educaskNotices'])) {
            $_SESSION['educaskNotices'] = new NoticeEngine();
        }
        return $_SESSION['educaskNotices'];
    }
    private static function setInstance(NoticeEngine $object) {
        $_SESSION['educaskNotices'] = $object;
    }
    private function __construct() {
        $this->notices = array();
    }
    public function addNotice(Notice $notice) {
        $this->notices[] = $notice;
        self::setInstance($this);
    }
    public function getNotices() {
        return $this->notices;
    }
    public function removeNotices() {
        $numberOfNotices = count($this->notices);
        $newArray = array();
        for ($i = 0; $i < $numberOfNotices; $i++) {
            if ($this->notices[$i] === null) {
                continue;
            }
            if ($this->notices[$i]->removeOnceDisplayed()) {
                continue;
            }
            $newArray[] = $this->notices[$i];
        }
        $this->notices = $newArray;
        self::setInstance($this);
    }
    public function removeNotice(Notice $toRemove) {
        $positionToRemove = $this->findNotice($toRemove);
        if ($positionToRemove === -1) {
            return;
        }
        $this->notices[$positionToRemove] = null;
        self::setInstance($this);
    }
    private function findNotice(Notice $toFind) {
        $numberOfNotices = count($this->notices);
        for ($i = 0; $i < $numberOfNotices; $i++) {
            if ($this->notices[$i] === null) {
                continue;
            }
            if ($this->notices[$i]->getMessage() != $toFind->getMessage()) {
                continue;
            }
            if ($this->notices[$i]->getStyle() != $toFind->getStyle()) {
                continue;
            }
            return $i;
        }
        return -1;
    }
}
abstract class noticeType {
    const warning = 'warning';
    const neutral = 'neutral';
    const success = 'success';
}