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
    public function getBlocks($theme, $parameters, $nodeType, $roleID) {
        $database = database::getInstance();
        $database->connect();
        if(! $database->isConnected()) {
            return false;
        }
        $results = $database->getData('blockID, module, blockName, themeRegion, title', 'block', 'WHERE theme = \'' . $theme . '\' AND enabled = 1 ORDER BY weight');
        $blocks = array();
        foreach ($results as $blockData) {
            if (!$this->blockExists($blockData['blockName'], $blockData['module'])) {
                continue;
            }
            if (!$this->blockVisible($blockData['blockID'], $nodeType, $roleID)) {
                continue;
            }
            if ($blockData['title'] == '') {
                $blocks[$blockData['themeRegion']][] = $this->getBlock($blockData['module'], $blockData['blockID'], $blockData['blockName'], $parameters, $nodeType, $roleID);
                continue;
            }

            $blocks[$blockData['themeRegion']][] = $this->getBlock($blockData['module'], $blockData['blockID'], $blockData['blockName'], $parameters, $nodeType, $roleID, $blockData['title']);
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
        if (! $this->blockVisible($blockID, $nodeType, $roleID)) {
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
        $results = $database->getData('visible', 'blockVisibility', 'WHERE blockID = \'' . $blockID . '\' AND ((referenceType = \'nodeType\' AND referenceID = \'*\') OR (referenceType = \'roleID\' AND referenceID = \'*\'))');
        //Default block is visible unless specified
        if($results == null) {
            $results = $database->getData('visible', 'blockVisibility', 'WHERE blockID = \'' . $blockID . '\' AND ((referenceType = \'nodeType\' AND referenceID = \'' . $nodeType . '\') OR (referenceType = \'roleID\' AND referenceID = \'' . $roleID . '\')) AND visible = 0');

            if ($results != NULL) {
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
            $results = $database->getData('*', 'blockVisibility', 'WHERE blockID = \'' . $blockID . '\' AND ((referenceType = \'nodeType\' AND referenceID = \'' . $nodeType . '\') OR (referenceType = \'roleID\' AND referenceID = \'' . $roleID . '\')) AND visible = 1');

            if ($results == NULL) {
                return false;
            }

            return true;
        }
        return true;
    }
    public function getPathToBlock($blockName, $moduleName) {
        return EHQ_SIMPLE_CMS_ROOT . '/includes/modules/' . $moduleName . '/blocks/' . $blockName . '.php';
    }
}