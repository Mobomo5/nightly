<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
require_once(USER_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(ANTI_FORGERY_TOKEN_OBJECT_FILE);
require_once(LINK_OBJECT_FILE);
class logoutForm {
    private $force404;
    private $redirectTo;
    private $noGUI;
    private $isPostRequest;
    public function __construct(array $inParams, $isPostRequest = false) {
        if(isset($inParams[2])) {
            $this->force404= true;
            return;
        }
        if(! is_bool($isPostRequest)) {
            $this->isPostRequest = false;
            return;
        }
        $this->isPostRequest = $isPostRequest;
        if($inParams[1] !== "logout") {
            $this->force404 = true;
            return;
        }
        $this->doLogOut();
    }
    private function doLogOut() {
        if(! currentUser::getUserSession()->isLoggedIn()) {
            $this->force404 = true;
            return;
        }
        $this->noGUI = true;
        $this->redirectTo = new link('');
        currentUser::getUserSession()->logOut();
    }
    public function getTitle() {
        return 'Logout';
    }
    public function getContent() {
        return '';
    }
    public function forceFourOhFour() {
        return $this->force404;
    }
    public function getReturnPage() {
        return $this->redirectTo;
    }
    public function noGUI() {
        return $this->noGUI;
    }
}