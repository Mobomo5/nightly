<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 4/23/14
 * Time: 2:52 PM
 */
require_once(NODE_INTERFACE_FILE);

class login implements node {
    private $noGui;

    public function __construct() {
        $this->noGUI = true;

        $user = currentUser::getUserSession();
        if ($user->isLoggedIn()) {
            echo "LOGGED IN";
            exit;
        }
        if (empty($_POST['username']) OR empty($_POST['password'])) {
            noticeEngine::getInstance()->addNotice(new notice('error', 'Please enter a username and a password'));
            return;
        }
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (!$user->logIn($username, $password)) {
            noticeEngine::getInstance()->addNotice(new notice('error', 'Wrong credentials')); //@todo: better error
            return;
        }


    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
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
        return $this->noGUI;
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }
}