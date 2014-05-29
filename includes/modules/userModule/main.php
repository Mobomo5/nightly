<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
require_once(MODULE_INTERFACE_FILE);

class userModule implements module {
    private $params;
    private $noGui = false;
    private $module;
    private $subModule;

    public function __construct() {
        $this->params = router::getInstance()->getParameters(true);
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
        // TODO: Implement getPageType() method.
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }
}