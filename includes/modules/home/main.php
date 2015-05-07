<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 4/25/14
 * Time: 5:19 PM
 */

require_once(MODULE_INTERFACE_FILE);
require_once(ROUTER_OBJECT_FILE);
require_once(LINK_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
class home implements module {
    private $title;
    private $body;
    public function __construct() {
        $user = currentUser::getUserSession();
        if(! $user->isLoggedIn()) {
            $this->title = "Welcome";
            $loginLink = new link("users/login");
            $this->body = "<p>Welcome to Educask. You're not logged in - please <a href='{$loginLink}'>login</a>.</p>";
            return;
        }
        $this->title = "Hi " . $user->getFirstName();
        $this->body = "<p>You're logged in. Eventually your timeline will be located here.</p>";
    }

    public static function getPageType() {
        return 'home';
    }

    public function noGUI() {
        return false;
    }

    public function getReturnPage() {
        return new link('');
    }

    public function getPageContent() {
        return $this->body;
    }
    public function getTitle() {
        return $this->title;
    }
    public function forceFourOhFour() {
        $params = router::getInstance()->getParameters(true);
        if(isset($params[1])) {
            return true;
        }
        return false;
    }
}