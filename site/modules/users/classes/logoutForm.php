<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class logoutForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        $currentUser = CurrentUser::getUserSession();
        if(! $currentUser->isLoggedIn()) {
            $this->response = Response::fourOhFour();
            return;
        }
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('userIsLoggingOut');
        $currentUser->logOut();
        session_regenerate_id(true);
        $hookEngine->runAction('userLoggedOut');
        NoticeEngine::getInstance()->addNotice(new Notice("neutral", "You're now logged out."));
        $this->response = Response::redirect(new Link(""));
    }
    public function getResponse() {
        return $this->response;
    }
}