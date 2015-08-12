<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 29/07/2015
 * Time: 9:03 PM
 */
class admin implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! PermissionEngine::getInstance()->currentUserCanDo("canAccessAdministration")) {
            $this->response = Response::fourOhThree();
            return;
        }
        if(isset($request->getParameters(true)[1])) {
            $this->response = $this->handleSecondParameter();
            return;
        }
        if(! defined("PAGE_TYPE")){
            define("PAGE_TYPE", "administration");
        }
        $menuEngine = MenuEngine::getInstance();
        $menu = $menuEngine->getMenuByName("adminMenu");
        if(! $menu) {
            $this->response = Response::fiveHundred();
            return;
        }
        $this->response = new Response(200, "@admin/main.twig", "Administration", "administration", $menu);
    }
    private function handleSecondParameter() {
        return Response::fiveHundred();
    }
    public function getResponse() {
        return $this->response;
    }
}