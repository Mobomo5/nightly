<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/22/14
 * Time: 8:44 PM
 */
require_once(BLOCK_INTERFACE_FILE);
require_once(USER_OBJECT_FILE);

class loginRegion implements block {
    private $title;
    private $href;
    private $content;
    private $css;

    public function __construct() {

        $user = currentUser::getUserSession()->toUser();

        if ($user->getRoleID() == GUEST_ROLE_ID){
            // You ain't logged in. Convince them to log in.
            $this->title = 'Click here to log in';
            $this->href = '#login-modal';
            $this->content = 'There is no user logged in.';
            $this->css = 'inlineLogIn'; // to ensure the link will open the fancybox

            return;
        }
        // get names
        $this->title = $user->getFirstName() . ' ' . $user->getLastName();

        // get news
        //@todo: news
        $this->content = 'Here\'s the news!';

        // get link to user page
        $this->href = new link('user/' . $user->getUserID());
    }

    public function getTitle() {
        return $this->title;
    }

    public function getCssClass(){
        return $this->css;
    }

    public function getHref(){
        return $this->href;
    }

    public function setTitle($inTitle) {
        $this->title = $inTitle;
    }

    public function getContent() {
        return $this->content;
    }
}