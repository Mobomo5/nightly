<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
require_once(NODE_INTERFACE_FILE);

class userModule implements node {
    private $params;
    private $noGui = false;
    private $module;
    private $subModule;

    public function __construct() {
        $this->params = nodeEngine::getInstance()->getParameters(true);
        $this->module = $this->params[0];
        if (!empty($_POST['login'])) {
            $this->doLogin();
            return;
        } elseif (!empty($_POST['logout'])) {
            $this->doLogout();
            return;
        } elseif (!empty($_POST[''])) {

        }

    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        // TODO: Implement getContent() method.
    }

    public function pageAuthorIsVisible() {
        // TODO: Implement pageAuthorIsVisible() method.
    }

    public function datePagePublishedIsVisible() {
        // TODO: Implement datePagePublishedIsVisible() method.
    }

    public function getDatePagePublished() {
        // TODO: Implement getDatePagePublished() method.
    }

    public function getPageAuthor() {
        // TODO: Implement getPageAuthor() method.
    }

    public static function getNodeType() {
        // TODO: Implement getNodeType() method.
    }

    public function statusesAreVisible() {
        // TODO: Implement statusesAreVisible() method.
    }

    public function getStatuses() {
        // TODO: Implement getStatuses() method.
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    private function doLogOut() {
        return currentUser::getUserSession()->logOut();
    }

    private function doLogIn() {
        if (!currentUser::getUserSession()->logIn($_POST['username'], $_POST['password'])) {
            noticeEngine::getInstance()->addNotice(new notice('error', 'I couldn\'t log you in.'));
        }
        return;
    }
}