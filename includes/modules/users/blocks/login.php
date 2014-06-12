<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:50 PM
 */
require_once(BLOCK_INTERFACE_FILE);
require_once(LOCKOUT_ENGINE_OBJECT_FILE);

class login implements block {
    private $title;
    private $content;
    private $button;
    private $noGUI;

    public function __construct() {
        $this->title = 'Login';
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
        return $this->title;
    }
    public function setTitle($inTitle) {
        $this->title = strip_tags($inTitle);
    }
    public function getContent() {
        return $this->content;
    }
    public function noGUI() {
        return $this->noGUI;
    }
    public function getReturnPage() {
        return 'home';
    }
    public function getButton() {
        return $this->button;
    }
    private function getLogIn() {
        $lockoutEngine = lockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $content = '<div id="login-form-background">';
            $content .= '<h2>You\'re Locked Out</h2>';
            $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
            if($lockout == false) {
                $content .= '</div>';
                return $content;
            }
            $totalLockoutLength = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
            $lockoutStart = clone $lockout->lastUpdated();
            $lockedOutUntil = $lockoutStart->add(DateInterval::createFromDateString($totalLockoutLength . ' minutes'));
            $currentTime = new DateTime();
            $minutesLeft = $currentTime->diff($lockedOutUntil);
            $minutesLeft = ($minutesLeft->days * 24 * 60) + ($minutesLeft->h * 60) + $minutesLeft->i;
            $content .= "<p>Please wait {$minutesLeft} minutes before trying to log in again.</p>";
            $content .= '</div>';
            return $content;
        }
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
        return 'user';
    }
}