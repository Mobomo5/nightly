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

    public function addAction($inEventName, $plugin) {
        if (!is_object($plugin)) {
            return false;
        }
        if (!in_array('plugin', class_implements($plugin))) {
            return false;
        }
        $this->actionEvents[$inEventName][] = $plugin;
        return true;
    }

    public function runAction($inEventName) {
        if (!isset($this->actionEvents[$inEventName])) {
            return null;
        }
        $this->pluginSort($this->actionEvents[$inEventName]);
        foreach ($this->actionEvents[$inEventName] as $plugin) {
            $plugin::run();
        }
    }

    public function  addFilter($inEventName, $plugin) {
        if (!is_object($plugin)) {
            return false;
        }
        if (!in_array('plugin', class_implements($plugin))) {
            return false;
        }
        $this->filterEvents[$inEventName][] = $plugin;
        return true;
    }

    public function runFilter($inEventName, $inContent) {
        if (!isset($this->filterEvents[$inEventName])) {
            return null;
        }
        $content = array();
        $this->pluginSort($this->filterEvents[$inEventName]);
        foreach ($this->filterEvents[$inEventName] as $plugin) {
            $content[] = $plugin::run($inContent);
        }
        return $content;
    }

    public function runAddToFilter($inEventName, $inContent) {
        if (!isset($this->filterEvents[$inEventName])) {
            return null;
        }
        $content = '';
        $this->pluginSort($this->filterEvents[$inEventName]);
        foreach ($this->filterEvents[$inEventName] as $plugin) {
            $content .= $plugin::run($inContent);
        }
        return $content;
    }

    private function pluginSort(array $plugins) {
        //Sort the array. The function is the comparison.
        if (!uasort($plugins, 'comparePlugins')) {
            //If the sorting failed, return false;
            return false;
        }
        return $plugins;
    }

    public function comparePlugins($a, $b) {
        if (!is_object($a)) {
            return false;
        }
        if (!in_array('plugin', class_implements($a))) {
            return false;
        }
        if (!is_object($b)) {
            return false;
        }
        if (!in_array('plugin', class_implements($b))) {
            return false;
        }
        //If the two plugins priority is the same, return 0;
        if ($a::getPriority() == $b::getPriority()) {
            return 0;
        }
        //If the first plugin has a lesser priority, return -1
        if ($a::getPriority() < $b::getPriority()) {
            return -1;
        }
        //The first plugin has a larger priority.
        return 1;
    }
}