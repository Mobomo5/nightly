<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/06/2015
 * Time: 9:17 PM
 */
class home implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 1) {
            $this->response = Response::fourOhFour();
            return;
        }
        $user = CurrentUser::getUserSession();
        if(! $user->isLoggedIn()) {
            $this->response = new Response(200, "@home/notLoggedIn.twig", "Welcome", "home");
            return;
        }
        $this->response = new Response(200, "@home/main.twig", "Hi {$user->getFirstName()}", "home", $user);
    }
    public function getResponse() {
        return $this->response;
    }
}