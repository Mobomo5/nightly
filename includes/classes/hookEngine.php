<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/10/14
 * Time: 10:47 AM
 */
class hookEngine {
    private $actionEvents;
    private $filterEvents;
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new hookEngine();
        }

        return self::$instance;
    }
    private function __construct() {
        $this->actionEvents = array();
        $this->filterEvents = array();
    }
    public function runAction($inEventName) {
        if(! isset($this->actionEvents[$inEventName])) {
            return;
        }
        foreach ($this->actionEvents[$inEventName] as $function) {
            if (!function_exists($function)) {
                //@ToDo: add notification that event didn't work
                continue;
            }
            call_user_func($function);
        }
    }
    public function addAction($inEventName, $inFunctionName){
        $this->actionEvents[$inEventName][] = $inFunctionName;
    }
    public function runFilter($inEventName, $inContent) {
        if(! isset($this->filterEvents[$inEventName])) {
            return false;
        }
        $content = '';
        foreach ($this->actionEvents[$inEventName] as $function) {
            if (!function_exists($function)) {
                //@ToDo: add notification that event didn't work
                continue;
            }
            $content = call_user_func($function, $inContent);
        }
        return $content;
    }
    public function runAddToFilter($inEventName, $inContent) {
        if(! isset($this->filterEvents[$inEventName])) {
            return false;
        }
        $content = '';
        foreach ($this->actionEvents[$inEventName] as $function) {
            if (!function_exists($function)) {
                //@ToDo: add notification that event didn't work
                continue;
            }
            $content .= call_user_func($function, $inContent);
        }
        return $content;
    }
    public function  addFilter($inEventName, $inFunctionName) {
        $this->filterEvents[$inEventName][] = $inFunctionName;
    }
}