<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:50 PM
 */
require_once(BLOCK_INTERFACE_FILE);

class login implements block {
    private $content;
    private $button;
    private $noGUI;

    public function __construct() {
        $this->noGUI = false;
        if (!currentUser::getUserSession()->isLoggedIn()) {
            $this->content = $this->getLogIn();
            $this->button = '<a id="login" class="inlineLogIn" href="#login-modal">Log in</a>';
        } else {
            $this->content = $this->getLogOut();
            $this->button = '<a href="' . EDUCASK_WEB_ROOT . '/users/logout">Log out</a>';
        }

    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
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
        return $this->noGUI;
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    public function getButton() {
        return $this->button;
    }

    private function getLogIn() {
        $content = '<div id="login-form-background">
            <h2>Please log in</h2>

            <form method="post" action="users">
                Username: <input tabindex="1" id="login-username" type="text" name="username"/><br/>
                Password: <input tabindex="2" type="password" name="password"/><br/>
                <input type="hidden" name="login" value="1">
                <input tabindex="3" type="submit"/><a tabindex="4" id="login-form-cancel" href="javascript:$.fancybox.close()">Cancel</a>
            </form>
        </div>';
        return $content;
    }

    private function getLogOut() {

    }

    public function getType() {
        return get_class(self);
    }
}