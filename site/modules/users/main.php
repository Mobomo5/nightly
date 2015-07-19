<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/21/14
 * Time: 2:02 PM
 */
class users implements IModule {
    private $response;
    public function __construct(Request $request) {
        $params = $request::getParameters(true);
        if(! isset($params[1])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if (empty($params[1])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if($params[1] === "login") {
            require_once('classes/loginForm.php');
            $this->subModule = new loginForm($params, $this->isPostRequest());
            return;
        }
        if($params[1] === "logout") {
            require_once('classes/logoutForm.php');
            $this->subModule = new logoutForm($params, $this->isPostRequest());
            return;
        }
        if($params[1] === "forgotPassword") {
            require_once('classes/forgotPasswordForm.php');
            $subMod = new forgotPasswordForm($request);
            $this->response = $subMod->getResponse();
            return;
        }
        $this->response = Response::fourOhFour();
    }
    public function getResponse() {
        return $this->response;
    }
}