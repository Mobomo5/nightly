<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/16/14
 * Time: 9:31 PM
 */
class noticeEngine {
    private $notices;
    public static function getInstance() {
        if (!isset($_SESSION['educaskNotices'])) {
            $_SESSION['educaskNotices'] = new noticeEngine();
        }

        return $_SESSION['educaskNotices'];
    }
    static function setInstance(noticeEngine $object) {
        //verify the variable given is a user object. If it is not, get out of here.
        if (get_class($object) != "noticeEngine") {
            return;
        }
        $_SESSION['educaskNotices'] = $object;
    }
    private function __construct(){
        $this->notices = array();
    }
    public function addNotice(notice $notice) {
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
            if ($this->notices[$i] == null) {
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
    public function removeNotice(notice $toRemove) {
        $positionToRemove = $this->findNotice($toRemove);
        if ($positionToRemove == -1) {
            return;
        }
        $this->notices[$positionToRemove] = null;
        self::setInstance($this);
    }
    public function findNotice(notice $toFind) {
        $numberOfNotices = count($this->notices);
        for ($i = 0; $i < $numberOfNotices; $i++) {
            if ($this->notices[$i] == null) {
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