<?php
class Config {
    private static $instance;
    private $configXml;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
    private function __construct() {
        $configFilePath = EDUCASK_ROOT . '/site/config.xml';
        if(! is_readable($configFilePath)) {
            $this->configXml = false;
            return;
        }
        $xmlString = file_get_contents($configFilePath);
        $this->configXml = new SimpleXMLElement($xmlString);
    }
    public function getDatabaseServer() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->database[0]['server']->__toString();
    }
    public function getDatabaseName() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->database[0]['name']->__toString();
    }
    public function getDatabaseUsername() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->database[0]['username']->__toString();
    }
    public function getDatabasePassword() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->database[0]['password']->__toString();
    }
    public function getDatabaseType() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->database[0]['type']->__toString();
    }
    public function getSessionProvider() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]->session[0]['provider']->__toString();
    }
    public function getAppKey() {
        if($this->configXml === false) {
            return "";
        }
        return $this->configXml->config[0]['appkey']->__toString();
    }
}