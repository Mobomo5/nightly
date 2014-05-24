<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/17/14
 * Time: 12:23 PM
 */
require_once(DATABASE_OBJECT_FILE);

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

    public function getBlocks($theme, $parameters, $nodeType, $roleID) {
        $database = database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return null;
        }
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
            if (!$this->blockExists($blockData['blockName'], $blockData['moduleName'])) {
                continue;

            }
            if (!$this->blockVisible($blockData['blockID'], $nodeType, $roleID)) {
                continue;
            }
            if ($blockData['title'] == '') {
                $blocks[$blockData['themeRegion']][] = $this->getBlock($blockData['moduleName'], $blockData['blockID'], $blockData['blockName'], $parameters, $nodeType, $roleID);
                continue;
            }
            $blocks[$blockData['themeRegion']][] = $this->getBlock($blockData['moduleName'], $blockData['blockID'], $blockData['blockName'], $parameters, $nodeType, $roleID, $blockData['title']);
        }

        return $blocks;
    }

    public function getBlock($moduleName, $blockID, $blockName, $parameters, $nodeType, $roleID, $title = null) {
        $this->includeBlock($blockName, $moduleName, $blockID, $nodeType, $roleID);
        $block = new $blockName($parameters);
        if ($title != null) {
            $block->setTitle($title);
        }
        return $block;
    }

    public function includeBlock($blockName, $moduleName, $blockID, $nodeType, $roleID) {
        if (!$this->blockExists($blockName, $moduleName)) {
            return;
        }
        if (!$this->blockVisible($blockID, $nodeType, $roleID)) {
            return;
        }

        require_once($this->getPathToBlock($blockName, $moduleName));
    }

    public function blockExists($blockName, $moduleName) {
        $block = $this->getPathToBlock($blockName, $moduleName);
        return file_exists($block);
    }

    public function blockVisible($blockID, $nodeType, $roleID) {
        $database = database::getInstance();
        $database->connect();

        // check to see if it's in the visibility table
        $results = $database->getData('visible', 'blockVisibility', 'blockID = \'' . $blockID . '\''); //AND ((referenceType = \'nodeType\' AND referenceID = \'' . $nodeType . '\') OR (referenceType = \'roleID\'))');
        //Default block is visible unless specified

        // if results are empty
        if ($results == false) {
            return true;
            // it's not in the table, check to see
            $results = $database->getData('visible', 'blockVisibility', 'blockID = \'' . $blockID . '\' AND ((referenceType = \'nodeType\' AND referenceID = \'' . $nodeType . '\') OR (referenceType = \'roleID\' AND referenceID = \'' . $roleID . '\')) AND visible = 0');
            if ($results != null) {
                return false;
            }
            return true;
        }
        //Block is visible everywhere
        if ($results[0]['visible'] == 1) {
            return true;
        }
        //Block is only visible on specified pages
        if ($results[0]['visible'] == 0) {
            $results = $database->getData('*', 'blockVisibility', 'blockID = \'' . $blockID . '\' AND ((referenceType = \'nodeType\' AND referenceID = \'' . $nodeType . '\') OR (referenceType = \'roleID\' AND referenceID = \'' . $roleID . '\')) AND visible = 0');
            if ($results == false) {
                return false;
            }
            return true;
        }

        return true;
    }

    public function getPathToBlock($blockName, $moduleName) {
        $moduleName = str_replace('..', '', $moduleName);
        $blockName = str_replace('..', '', $blockName);
        return EDUCASK_ROOT . '/includes/modules/' . $moduleName . '/blocks/' . $blockName . '.php';
    }

    public function addBlock($blockName, $title = '', $theme = 'default', $themeRegion, $weight = 1, $enabled = 1, $module = 1) {

    }
}