<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/17/14
 * Time: 12:23 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(HOOK_ENGINE_OBJECT_FILE);

class blockEngine {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new blockEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing;
    }
    public function getBlocks($theme, $parameters, $pageType, $roleID) {
        $database = database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return null;
        }
        $theme = $database->escapeString(str_replace('..', '', $theme));
        // get all enabled blocks
        $results = $database->getData('b.blockID, m.moduleName, b.blockName, b.themeRegion, b.title', 'block b, module m', 'b.theme = \'' . $theme . '\' AND b.enabled = 1 AND m.moduleID = b.module ORDER BY weight');
        // if there are none available, return false.
        if ($results == false) {
            return null;
        }
        if ($results == null) {
            return null;
        }
        $blocks = array();
        foreach ($results as $blockData) {
            if (!$this->blockVisible($blockData['blockID'], $pageType, $roleID)) {
                continue;
            }
            $block = $this->getBlock($blockData['moduleName'], $blockData['blockName'], $parameters);
            if($block == false) {
                continue;
            }
            if($blockData['title'] != '') {
                $block->setTitle($blockData['title']);
            }
            $blocks[$blockData['themeRegion']][] = $block;
        }
        return $blocks;
    }
    private function getBlock($moduleName, $blockName, $parameters) {
        $this->includeBlock($blockName, $moduleName);
        if(! $this->validateBlock($blockName)) {
            return false;
        }
        $block = new $blockName($parameters);
        return $block;
    }
    private function includeBlock($moduleName, $blockName){
        $moduleName = str_replace('..', '', $moduleName);
        $blockName = str_replace('..', '', $blockName);
        $blockPath = $this->getPathToBlock($moduleName, $blockName);
        if(! is_file($blockPath)) {
            return;
        }
        require_once($blockPath);
    }
    private function validateBlock($blockName) {
        if (!class_exists($blockName)) {
            return false;
        }
        $interfacesThatClassImplements = class_implements($blockName);
        if ($interfacesThatClassImplements === false) {
            return false;
        }
        if (!in_array('block', $interfacesThatClassImplements)) {
            return false;
        }
        return true;
    }
    private function getPathToBlock($blockName, $moduleName) {
        $moduleName = str_replace('..', '', $moduleName);
        $blockName = str_replace('..', '', $blockName);
        return EDUCASK_ROOT . '/includes/modules/' . $moduleName . '/blocks/' . $blockName . '.php';
    }
    private function blockVisible($blockID, $pageType, $roleID) {
        if(! is_numeric($blockID)) {
            return false;
        }
        if($blockID < 1) {
            return false;
        }
        $database = database::getInstance();
        $database->connect();
        if(! $database->isConnected()) {
            return false;
        }

        // check to see if it's in the visibility table
        $results = $database->getData('*', 'blockVisibility', 'blockID = ' . $blockID);
        //Query failed. Play it safe and don't display the block.
        if($results == false) {
            return false;
        }
        //Default is to display the block unless specified.
        if($results == null) {
            return true;
        }
        foreach($results as $rule) {
            if($rule['visible'] == 1) {
                continue;
            }
            if($rule['referenceType'] == 'pageType') {
                if($pageType == $rule['referenceID']) {
                    return false;
                }
            }
            if($rule['referenceType'] == 'role') {
                if($roleID == intval($rule['referenceID'])) {
                    return false;
                }
            }
        }
        return true;
    }
}