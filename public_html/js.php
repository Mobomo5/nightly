<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 22/07/2015
 * Time: 8:56 PM
 */
class JsBuilder {
    public function __construct() {
        $response = Response::fiveHundred();
        $objectCache = ObjectCache::getInstance();
        $minifiedAlready = $objectCache->getObject('minifiedJS');
        if(! $minifiedAlready) {
            $this->response = $this->buildMinifiedJS();
            return;
        }
        $response->setRawContent($minifiedAlready);
        $response->setHeader('Content-Type', "text/javascript");
        if (! isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $response->setResponseCode(200);
            $this->response = $response;
            return;
        }
        if(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < $objectCache->getLastClearTime()) {
            $response->setResponseCode(200);
            $this->response = $response;
            return;
        }
        $response->setResponseCode(304);
        $this->response = $response;
    }
    private function buildMinifiedJS() {
        $siteObject = Site::getInstance();
        $minifiedSoFar = "";
        $themesCssFiles = glob(EDUCASK_ROOT . "/site/themes/{$siteObject->getTheme()}/js/*.js");
        foreach($themesCssFiles as $cssFile) {
            if(! is_readable($cssFile)) {
                continue;
            }
            $rawFile = file_get_contents($cssFile);
            $rawFile = $this->minifyJsString($rawFile);
            $minifiedSoFar .= $rawFile;
        }
        $minifiedSoFar .= $this->getOtherJsFiles();
        $minifiedSoFar .= $this->getRawJs();
        $objectCache = ObjectCache::getInstance();
        $objectCache->setObject('minifiedJS', $minifiedSoFar, true);
        $objectCache->saveInstance();
        $response = Response::fiveHundred();
        $response->setRawContent($minifiedSoFar);
        $response->setHeader('Content-Type', "text/javascript");
        $response->setResponseCode(200);
        return $response;
    }
    private function getOtherJsFiles() {
        $hookEngine = HookEngine::getInstance();
        $jsFilePaths = $hookEngine->runFilter('getOtherJsFilePaths', '');
        if($jsFilePaths === null) {
            return "";
        }
        $toReturn = "";
        foreach($jsFilePaths as $jsFile) {
            if(! is_readable($jsFile)) {
                continue;
            }
            $rawFile = file_get_contents($jsFile);
            $rawFile = $this->minifyJsString($rawFile);
            $toReturn .= $rawFile;
        }
        return $toReturn;
    }
    private function getRawJs() {
        $hookEngine = HookEngine::getInstance();
        $rawJs = $hookEngine->runFilter('getRawJs', '');
        if($rawJs === null) {
            return "";
        }
        $toReturn = "";
        foreach($rawJs as $js) {
            $toReturn .= $this->minifyJsString($js);
        }
        return $toReturn;
    }
    private function minifyJsString($string) {
        if(! is_string($string)) {
            return "";
        }
        $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
        $string = str_replace(': ', ':', $string);
        $string = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $string);
        return $string;
    }
    public function getResponse() {
        return $this->response;
    }
}
date_default_timezone_set("UTC");
define('EDUCASK_ROOT', dirname(getcwd()));
require_once(EDUCASK_ROOT . '/core/classes/Bootstrap.php');
Bootstrap::registerAutoloader();
Bootstrap::initializePlugins();
$jsBuilder = new JsBuilder();
$response = $jsBuilder->getResponse();
http_response_code($response->getResponseCode());
$headers = $response->getHeaders();
foreach($headers as $header => $value) {
    header($header . ": " . $value, true);
}
echo $response->getRawContent();