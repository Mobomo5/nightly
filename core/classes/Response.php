<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 13/06/2015
 * Time: 12:19 PM
 */

class Response {
    private $responseCode;
    private $view;
    private $pageTitle;
    private $pageType;
    private $objectToPassToView;
    private $redirectTo;
    private $headers;
    private $rawContent;
    public function __construct($responseCode, $view, $pageTitle, $pageType, $objectToPassToView = null, Link $redirectTo = null, $rawContent = "") {
        $this->headers = array();
        if(! is_numeric($responseCode)) {
            return;
        }
        if(! is_string($view)) {
            return;
        }
        if(! is_string($pageTitle)) {
            return;
        }
        if(! is_string($pageType)) {
            return;
        }
        if(! is_string($rawContent)) {
            return;
        }
        $this->responseCode = $responseCode;
        $this->view = $view;
        $this->pageTitle = $pageTitle;
        $this->pageType = $pageType;
        $this->objectToPassToView = $objectToPassToView;
        $this->redirectTo = $redirectTo;
        $this->rawContent = $rawContent;
    }
    public function getResponseCode() {
        return $this->responseCode;
    }
    public function setResponseCode($inResponseCode) {
        if(! is_numeric($inResponseCode)) {
            return;
        }
        $this->responseCode = $inResponseCode;
    }
    public function getView() {
        return $this->view;
    }
    public function setView($inView) {
        if(! is_string($inView)) {
            return;
        }
        $this->view = $inView;
    }
    public function getPageTitle() {
        return $this->pageTitle;
    }
    public function setPageTitle($inPageTitle) {
        if(! is_string($inPageTitle)) {
            return;
        }
        $this->pageTitle = $inPageTitle;
    }
    public function getPageType() {
        return $this->pageType;
    }
    public function setPageType($inPageType) {
        if(! is_string($inPageType)) {
            return;
        }
        $this->pageType = $inPageType;
    }
    public function getObjectToPassToView() {
        return $this->objectToPassToView;
    }
    public function setObjectToPassToView($inObjectToPassToView) {
        $this->objectToPassToView = $inObjectToPassToView;
    }
    public function getRedirectTo() {
        return $this->redirectTo;
    }
    public function setRedirectTo(Link $inRedirectTo) {
        return $this->redirectTo = $inRedirectTo;
    }
    public static function setCookie($name, $value) {
        if(! is_string($name)) {
            return;
        }
        if(! is_string($value)) {
            return;
        }
        $variableEngine = VariableEngine::getInstance();
        $siteInfo = $variableEngine->getVariables(array("siteWebAddress", "siteWebDirectory"));
        $directory = $siteInfo['siteWebDirectory']->getValue();
        $url = parse_url($siteInfo['siteWebAddress']->getValue());
        if($url === false) {
            setcookie($name, $value, 0, $directory, null, false, true);
            return;
        }
        setcookie($name, $value, 0, $directory, $url['host'], false, true);
    }
    public function getHeader($inHeaderName) {
        if(! is_string($inHeaderName)) {
            return false;
        }
        if(! isset($this->headers[$inHeaderName])) {
            return false;
        }
        return $this->headers[$inHeaderName];
    }
    public function setHeader($inHeaderName, $inHeaderValue, $overwrite = false) {
        if(! is_string($inHeaderName)) {
            return;
        }
        if(! is_string($inHeaderValue)) {
            return;
        }
        if(! is_bool($overwrite)) {
            return;
        }
        if(isset($this->headers[$inHeaderName]) && (! $overwrite)) {
            return;
        }
        $inHeaderName = str_replace(":", "", $inHeaderName);
        $inHeaderValue = str_replace(":", "", $inHeaderValue);
        $this->headers[$inHeaderName] = $inHeaderValue;
    }
    public function getHeaders() {
        return $this->headers;
    }
    public function setRawContent($inRawContent) {
        if(! is_string($inRawContent)) {
            return;
        }
        $this->rawContent = $inRawContent;
    }
    public function getRawContent() {
        return $this->rawContent;
    }
    public static function fourOhThree() {
        return new Response(403, "@httpErrors/fourOhThree.twig", "403: Access Denied", "fourOhThree", Router::getInstance()->getParameters());
    }
    public static function fourOhFour() {
        return new Response(404, "@httpErrors/fourOhFour.twig", "404: Page not Found", "fourOhFour", Router::getInstance()->getParameters());
    }
    public static function fiveHundred() {
        return new Response(500, "@httpErrors/fiveHundred.twig", "500: Internal Server Error", "fiveHundred");
    }
    public static function redirect(Link $to) {
        //Defaults to a 500 if the redirect didn't work.
        return new Response(500, "@httpErrors/fiveHundred.twig", "500: Internal Server Error", "fiveHundred", null, $to);
    }
    public static function raw($rawText, $statusCode=200) {
        if(! is_int($statusCode)) {
            return new Response(200, "", "", "", null, null, $rawText);
        }
        return new Response($statusCode, "", "", "", null, null, $rawText);
    }
}