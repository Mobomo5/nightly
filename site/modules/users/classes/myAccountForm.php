<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class myAccountForm implements IModule {
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
        $this->response = new Response(200, "@users/myAccount.twig", "My Account", "user", $currentUser);
    }
    public function getResponse() {
        return $this->response;
    }
}