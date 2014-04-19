<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 19/04/14
 * Time: 11:05 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(SITE_OBJECT_FILE);
class nodeEngine {
    private static $instance;
    private $sourceURL;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new nodeEngine();
        }

        return self::$instance;
    }
    private function __construct() {
        $this->actionEvents = array();
        $this->filterEvents = array();
    }
    public function isAlias() {
        $database = database::getInstance();
        $site = site::getInstance();
        $results = $database->getData('source', 'urlAlias', 'WHERE alias=\'' . $database->escapeString($site->getCurrentPage()) . '\'');
        if(count($results) != 1) {
            $this->sourceURL =  $site->getCurrentPage();
            return false;
        }
        $this->sourceURL = $results[0]['source'];
        return true;
    }
    public function getURL() {
        if(!isset($this->sourceURL)) {
            return null;
        }
        return $this->sourceURL;
    }
    public function getNode() {
        //Determine if the URL is an alias. We don't care about the value since the URL we need is stored in $this->sourceURL
        $this->isAlias();
        $page = $this->sourceURL;
    }
}