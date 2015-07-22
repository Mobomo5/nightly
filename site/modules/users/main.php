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
        $className = str_replace('.', '', trim($params[1])) . 'Form';
        $file = EDUCASK_ROOT . "/site/modules/users/classes/{$className}.php";
        if(! is_readable($file)) {
            $this->response = Response::fourOhFour();
            return;
        }
        require_once($file);
        if(! class_exists($className)) {
            $this->response = Response::fiveHundred();
            return;
        }
        $subModule = new $className($request);
        $this->response = $subModule->getResponse();
    }
    public function getResponse() {
        return $this->response;
    }
}