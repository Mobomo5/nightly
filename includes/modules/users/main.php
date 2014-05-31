<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
require_once(MODULE_INTERFACE_FILE);

class users implements module {
    private $params;
    private $noGUI = false;
    private $module;
    private $force404 = false;
    private $title;
    private $content;

    public function __construct() {
        $this->params = router::getInstance()->getParameters(true);
        $this->module = $this->params[0];
        // check the post
        if (!empty($_POST['login'])) {
            $this->doLogin();
            return;
        } elseif (!empty($_POST['logout'])) {
            $this->doLogout();
            return;
        }

        // nothing in the post. Check to see if there are second parameters

        if (empty($this->params[1])) {
            $this->force404 = true;
            return false;
        }

        // handle the possibility of an href logout.
        if ($this->params[1] == 'logout') {
            $this->doLogOut();
            return;
        }

        $userID = $this->params[1];

        if (!is_numeric($userID)) {
            $this->force404 = true;
            return false;
        }

//        check to see if the user has permission to see other users
        if (!permissionEngine::getInstance()->currentUserCanDo('userCanViewOtherUsers')) {
            $this->force404 = true;
            return false;
        }
//        check to see if that is actually a user
        $user = userEngine::getInstance()->getUser($userID);
        if (!$user) {
            $this->force404 = true;
            return false;
        }
//        get the user
//        set the title
        $this->title = $user->getFullName();
        $this->content = userEngine::getInstance()->getUserBio($user);

    }

    private function doLogOut() {
        return currentUser::getUserSession()->logOut();
    }

    private function doLogIn() {
        if (!currentUser::getUserSession()->logIn($_POST['username'], $_POST['password'])) {
            logger::getInstance()->getInstance()->logIt(new logEntry('1', logEntryType::warning, 'Someone failed to log into ' . $_POST['username'] . '\'s account from IP:' . $_SERVER['REMOTE_ADDR'], 0), 0);
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, 'I couldn\'t log you in.'));
        }

        return;
    }

    public static function getPageType() {
        return 'user';
    }

    public function noGUI() {
        return $this->noGUI;
    }

    public function getReturnPage() {
        return new link('');
    }

    public function getPageContent() {
        return $this->content;
    }

    public function getTitle() {
        return $this->title;
    }

    public function forceFourOhFour() {
        return $this->force404;
    }
}