<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 26/08/2015
 * Time: 6:46 PM
 */
abstract class Controller implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(isset($request->getParameters(true)[1])) {
            $this->response = $this->handleSecondParameter($request);
            return;
        }
        $this->response = $this->index();
    }
    protected function handleSecondParameter(Request $request) {
        $subMethod = $request->getParameters(true)[1];
        if(substr($subMethod, 0, 2) === "do") {
            return Response::fourOhFour();
        }
        if($subMethod === "getResponse") {
            return Response::fourOhFour();
        }
        if($request->isPostRequest()) {
            $subMethod = "do" . ucfirst(($subMethod));
        }
        if(! method_exists($this, $subMethod)) {
            return Response::fourOhFour();
        }
        $reflection = new ReflectionMethod($this, $subMethod);
        if(! $reflection->isPublic()) {
            return Response::fourOhFour();
        }
        return $this->$subMethod();
    }
    public function getResponse() {
        return $this->response;
    }
    public abstract function index();
    protected function redirectToError(Link $to, $message) {
        if(! is_string($message)) {
            return Response::redirect($to);
        }
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, $message));
        return Response::redirect($to);
    }
    protected function redirectToSuccess(Link $to, $message) {
        if(! is_string($message)) {
            return Response::redirect($to);
        }
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::success, $message));
        return Response::redirect($to);
    }
}