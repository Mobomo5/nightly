<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class viewForm implements IModule {
    private $response;
    public function __construct(Request $request) {;
        $parameters = $request->getParameters(true);
        if(count($parameters) > 3) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! isset($parameters[2])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! is_numeric($parameters[2])) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! PermissionEngine::getInstance()->currentUserCanDo("canViewDetailsOfUsers")) {
            $this->response = Response::fourOhThree();
            return;
        }
        $userEngine = UserEngine::getInstance();
        $model = $userEngine->getUser($parameters[2]);
        if(! $model) {
            $this->response = Response::fourOhFour();
            return;
        }
        $this->response = new Response(200, "@users/view.twig", $model->getFullName(), "user", $model);
    }
    public function getResponse() {
        return $this->response;
    }
}