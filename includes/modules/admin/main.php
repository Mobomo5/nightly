<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 4:40 PM
 */

require_once(MODULE_INTERFACE_FILE);

class admin implements module {

    private $noGUI = false;
    private $forceFourOhFour = false;
    private $content;
    private $title;

    public function __construct() {

        // check permissions
        $perm = permissionEngine::getInstance()->getPermission('userCanAccessAdminPages');
        if (!$perm->canDo()) {
            $this->forceFourOhFour = true;
            return false;
        }

        // check post. If there is anything in there, do it.
//        // if the doPost returns a false, throw a 404
//        if (!empty($_POST)) {
//            if (!$this->doPOST()) {
//                $this->forceFourOhFour = true;
//                return false;
//            }
//        }

        $this->title = 'Admin';

    }

    public static function getPageType() {
        return 'admin';
    }

    public function getPageContent() {
        return $this->content;
    }

    public function getTitle() {
        return $this->title;
    }

    public function noGUI() {
        return $this->noGUI;
    }

    public function getReturnPage() {
        return '';
    }

    public function forceFourOhFour() {
        return $this->forceFourOhFour;
    }

    private function doPOST() {
    }
}