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
    private $noGui = false;
    private $module;

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
        } elseif (!empty($_POST[''])) {

        }

        // nothing in the post. Check to see if there are second parameters

        if (empty($this->params[1])) {
            return false;
        }

        $userID = $this->params[1];

        if (!is_numeric($userID)) {
            return false;
        }

//        check to see if the user has permission to see other users
//        check to see if that is actually a user
//        get the user
//        $userObject =

    }

    private function doLogOut() {
        return currentUser::getUserSession()->logOut();
    }

    private function doLogIn() {
        if (!currentUser::getUserSession()->logIn($_POST['username'], $_POST['password'])) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, 'I couldn\'t log you in.'));
        }

        return;
    }

    public static function getPageType() {
        return 'user';
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    public function getPageContent() {
        // TODO: Implement getPageContent() method.
    }
}