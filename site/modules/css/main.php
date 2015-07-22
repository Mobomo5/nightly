<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/06/2015
 * Time: 9:17 PM
 */
class css implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 1) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(Request::isPostRequest()) {
            $this->response = Response::fiveHundred();
            return;
        }
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