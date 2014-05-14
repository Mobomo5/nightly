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
        $this->failed = array();
    }
    public function addAction($inEventName, $plugin){
        if (!is_object($plugin)) {
            return false;
        }
        if(! in_array('plugin', class_implements($plugin))) {
            return false;
        }
        $this->actionEvents[$inEventName][] = $plugin;
    }
    public function runAction($inEventName) {
        if(! isset($this->actionEvents[$inEventName])) {
            return;
        }
        foreach ($this->actionEvents[$inEventName] as $plugin) {
            $plugin::run();
        }
    }
    public function  addFilter($inEventName, $plugin) {
        if (!is_object($plugin)) {
            return false;
        }
        if(! in_array('plugin', class_implements($plugin))) {
            return false;
        }
        $this->filterEvents[$inEventName][] = $plugin;
    }
    public function runFilter($inEventName, $inContent) {
        if(! isset($this->filterEvents[$inEventName])) {
            return;
        }
        $content = array();
        foreach ($this->filterEvents[$inEventName] as $plugin) {
            $content[] = $plugin::run($inContent);
        }
        return $content;
    }
    public function runAddToFilter($inEventName, $inContent) {
        if(! isset($this->filterEvents[$inEventName])) {
            return;
        }
        $content = '';
        foreach ($this->filterEvents[$inEventName] as $plugin) {
            $content .= $plugin::run($inContent);
        }
        return $content;
    }
    private function pluginSort(array $plugins) {
        //I know this is bad, but it's the best way to do it so far without letting the plugins decide their priority.
        //Sort the array. The function is the comparison.
        if(! uasort($plugins, function ($a, $b) {
            //If the two plugins priority is the same, return 0;
            if($a::getPriority() == $b::getPriority()) {
                return 0;
            }
            //If the first plugin has a lesser priority, return -1
            if($a::getPriority() < $b::getPriority()) {
                return -1;
            }
            //The first plugin has a larger priority.
            return 1;
        })) {
            //If the sorting failed, return false;
            return false;
        }
        return $plugins;
    }
}