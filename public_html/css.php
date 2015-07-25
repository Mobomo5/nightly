<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 22/07/2015
 * Time: 8:56 PM
 */
class CssBuilder {
    public function __construct() {
        $response = Response::fiveHundred();
        $objectCache = ObjectCache::getInstance();
        $minifiedAlready = $objectCache->getObject('minifiedCSS');
        if(! $minifiedAlready) {
            $this->response = $this->buildMinifiedCSS();
            return;
        }
        $response->setRawContent($minifiedAlready);
        $response->setHeader('Content-Type', "text/css");
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
    private function buildMinifiedCSS() {
        $siteObject = Site::getInstance();
        $minifiedSoFar = "";
        $themesCssFiles = glob(EDUCASK_ROOT . "/site/themes/{$siteObject->getTheme()}/css/*.css");
        foreach($themesCssFiles as $cssFile) {
            if(! is_readable($cssFile)) {
                continue;
            }
            $rawFile = file_get_contents($cssFile);
            $rawFile = $this->minifyCssString($rawFile);
            $minifiedSoFar .= $rawFile;
        }
        $minifiedSoFar .= $this->getOtherCssFiles();
        $minifiedSoFar .= $this->getRawCss();
        $objectCache = ObjectCache::getInstance();
        $objectCache->setObject('minifiedCSS', $minifiedSoFar, true);
        $response = Response::fiveHundred();
        $response->setRawContent($minifiedSoFar);
        $response->setHeader('Content-Type', "text/css");
        $response->setResponseCode(200);
        return $response;
    }
    private function getOtherCssFiles() {
        $hookEngine = HookEngine::getInstance();
        $cssFilePaths = $hookEngine->runFilter('getOtherCssFilePaths', '');
        if($cssFilePaths === null) {
            return "";
        }
        $toReturn = "";
        foreach($cssFilePaths as $cssFile) {
            if(! is_readable($cssFile)) {
                continue;
            }
            $rawFile = file_get_contents($cssFile);
            $rawFile = $this->minifyCssString($rawFile);
            $toReturn .= $rawFile;
        }
        return $toReturn;
    }
    private function getRawCss() {
        $hookEngine = HookEngine::getInstance();
        $rawCss = $hookEngine->runFilter('getRawCss', '');
        if($rawCss === null) {
            return "";
        }
        $toReturn = "";
        foreach($rawCss as $css) {
            $toReturn .= $this->minifyCssString($css);
        }
        return $toReturn;
    }
    private function minifyCssString($string) {
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
define('EDUCASK_ROOT', dirname(getcwd()));
require_once(EDUCASK_ROOT . '/core/classes/Bootstrap.php');
Bootstrap::registerAutoloader();
Bootstrap::initializePlugins();
$cssBuilder = new CssBuilder();
$response = $cssBuilder->getResponse();
http_response_code($response->getResponseCode());
$headers = $response->getHeaders();
foreach($headers as $header => $value) {
    header($header . ": " . $value, true);
}
echo $response->getRawContent();