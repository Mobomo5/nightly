<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/17/14
 * Time: 12:23 PM
 */
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
    public function getBlocks($theme, $pageType, $roleID) {
        $database = Database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return null;
        }
        $theme = $database->escapeString(str_replace('..', '', $theme));
        // get all enabled blocks
        $results = $database->getData('b.blockID, m.moduleName, b.blockName, b.themeRegion, b.title', 'block b, module m', 'b.theme = \'' . $theme . '\' AND b.enabled = 1 AND m.moduleID = b.module ORDER BY weight');
        // if there are none available, return false.
        if ($results === false) {
            return null;
        }
        if ($results === null) {
            return null;
        }
        $blocks = array();
        foreach ($results as $blockData) {
            if (!$this->blockVisible($blockData['blockID'], $pageType, $roleID)) {
                continue;
            }
            $block = $this->getBlock($blockData['moduleName'], $blockData['blockName'], $blockData['blockID']);
            if ($block === false) {
                continue;
            }
            if ($blockData['title'] !== '') {
                $block->setTitle($blockData['title']);
            }
            $blocks[$blockData['themeRegion']][] = $block;
        }
        return $blocks;
    }
    private function getBlock($moduleName, $blockName, $blockID) {
        if(! is_numeric($blockID)) {
            return false;
        }
        $this->includeBlock($blockName, $moduleName);
        if (!$this->validateBlock($blockName)) {
            return false;
        }
        $block = new $blockName($blockID);
        return $block;
    }
    private function includeBlock($moduleName, $blockName) {
        $moduleName = str_replace('..', '', $moduleName);
        $blockName = str_replace('..', '', $blockName);
        $blockPath = $this->getPathToBlock($moduleName, $blockName);
        if (!is_readable($blockPath)) {
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
        if (!is_numeric($blockID)) {
            return false;
        }
        if ($blockID < 1) {
            return false;
        }
        $database = Database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return false;
        }
        $blockID = $database->escapeString($blockID);
        // check to see if it's in the visibility table
        $results = $database->getData('*', 'blockVisibility', 'blockID = ' . $blockID);
        //Default is to display the block unless specified.
        if ($results === null) {
            return true;
        }
        //Query failed. Play it safe and don't display the block.
        if ($results === false) {
            return false;
        }
        $comparators = array('referenceType' => '', 'referenceValue' => '');
        $hookEngine = HookEngine::getInstance();
        $comparators = $hookEngine->runFilter('blockVisibilityComparator', $comparators);
        $comparators[] = array('referenceType' => 'pageType', 'referenceValue' => $pageType);
        $comparators[] = array('referenceType' => 'role', 'referenceValue' => $roleID);
        $finalComparators = array();
        foreach ($comparators as $comparator) {
            if (!isset($comparator['referenceType'])) {
                continue;
            }
            if (!isset($comparator['referenceValue'])) {
                continue;
            }
            if (isset($finalComparators[$comparator['referenceType']])) {
                continue;
            }
            $finalComparators[$comparator['referenceType']] = $comparator['referenceValue'];
        }
        $countOfDoNotDisplays = 0;
        $countOfDoDisplays = 0;
        foreach ($results as $rule) {
            if (!isset($finalComparators[$rule['referenceType']])) {
                continue;
            }
            //If the first character is an !, then negate the operation.
            if ($rule['referenceID'][0] === '!') {
                $vote = $this->blockVisibleNegate($rule, $finalComparators);
                if ($vote === -1) {
                    $countOfDoNotDisplays += 1;
                    continue;
                }
                if ($vote === 1) {
                    $countOfDoDisplays += 1;
                    continue;
                }
                //No vote on any other value.
                continue;
            }
            if ($finalComparators[$rule['referenceType']] != $rule['referenceID']) {
                continue;
            }
            if ((int)$rule['visible'] === 0) {
                $countOfDoNotDisplays += 1;
                continue;
            }
            $countOfDoDisplays += 1;
        }
        if ($countOfDoNotDisplays > $countOfDoDisplays) {
            return false;
        }
        return true;
    }
    private function blockVisibleNegate($rule, $finalComparators) {
        //Take the ! off
        $rule['referenceID'] = substr($rule['referenceID'], 1);
        if (!isset($finalComparators[$rule['referenceType']])) {
            return 0;
        }
        //Negate means vote for anything that does not match. Move on if it matches; don't vote.
        if ($finalComparators[$rule['referenceType']] == $rule['referenceID']) {
            return 0;
        }
        if ((int)$rule['visible'] === 0) {
            return -1;
        }
        return 1;
    }
    public function setBlockVisibility($inBlockID, $referenceID, $referenceType, $isVisible = false) {
        if (!is_numeric($inBlockID)) {
            return false;
        }
        if ($inBlockID < 1) {
            return false;
        }
        if (!is_bool($isVisible)) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inBlockID = $database->escapeString($inBlockID);
        $whereClause = "referenceID='{$referenceID}' AND referenceType='{$referenceType}' AND blockID={$inBlockID}";
        $exists = $database->getData('ruleID', 'blockVisibility', $whereClause);
        if ($exists === false) {
            return false;
        }
        if ($exists != null) {
            return $this->insertNewVisibilityRule($inBlockID, $referenceID, $referenceType, $isVisible);
        }
        if ($isVisible === true) {
            $visible = 1;
        } else {
            $visible = 0;
        }
        $success = $database->updateTable('blockVisibility', "visibile={$visible}", $whereClause);
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    private function insertNewVisibilityRule($inBlockID, $referenceID, $referenceType, $isVisible = false) {
        if (!is_numeric($inBlockID)) {
            return false;
        }
        if ($inBlockID < 1) {
            return false;
        }
        if (!is_bool($isVisible)) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inBlockID = $database->escapeString($inBlockID);
        if ($isVisible === true) {
            $visible = 1;
        } else {
            $visible = 0;
        }
        $success = $database->insertData('blockVisibility', 'referenceID, referenceType, visible, blockID', "'{$referenceID}', '{$referenceType}', {$visible}, {$inBlockID}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function deleteVisibilityRule($inBlockID, $referenceID, $referenceType) {
        if (!is_numeric($inBlockID)) {
            return false;
        }
        if ($inBlockID < 1) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inBlockID = $database->escapeString($inBlockID);
        $success = $database->removeData('blockVisibility', "referenceID='{$referenceID}' AND referenceType='{$referenceType}' AND blockID={$inBlockID}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function addBlock($blockName, $theme, $themeRegion, $weight, $moduleInCharge, $enabled = false) {
        if (!is_numeric($weight)) {
            return false;
        }
        if (!is_numeric($moduleInCharge)) {
            return false;
        }
        if (!is_bool($enabled)) {
            return false;
        }
        $blockName = preg_replace('/\s+/', '', strip_tags($blockName));
        $theme = preg_replace('/\s+/', '', strip_tags($theme));
        $themeRegion = preg_replace('/\s+/', '', strip_tags($themeRegion));
        if ($enabled === true) {
            $isEnabled = 1;
        } else {
            $isEnabled = 0;
        }
        $database = Database::getInstance();
        $blockName = $database->escapeString($blockName);
        $theme = $database->escapeString($theme);
        $themeRegion = $database->escapeString($themeRegion);
        $success = $database->insertData('block', 'blockName, theme, themeRegion, weight, enabled, module', "'{$blockName}', '{$theme}', '{$themeRegion}', {$weight}, {$isEnabled}, {$moduleInCharge}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function setBlockTitle($blockID, $title) {
        if (!is_numeric($blockID)) {
            return false;
        }
        $database = Database::getInstance();
        $title = $database->escapeString(strip_tags($title));
        $blockID = $database->escapeString($blockID);
        $success = $database->updateTable('block', "title='{$title}'", "blockID={$blockID}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function setBlock($blockID, $blockName, $theme, $themeRegion, $weight, $moduleInCharge, $enabled = false) {
        if (!is_numeric($blockID)) {
            return false;
        }
        if (!is_numeric($weight)) {
            return false;
        }
        if (!is_numeric($moduleInCharge)) {
            return false;
        }
        if (!is_bool($enabled)) {
            return false;
        }
        $blockName = preg_replace('/\s+/', '', strip_tags($blockName));
        $theme = preg_replace('/\s+/', '', strip_tags($theme));
        $themeRegion = preg_replace('/\s+/', '', strip_tags($themeRegion));
        if ($enabled === true) {
            $isEnabled = 1;
        } else {
            $isEnabled = 0;
        }
        $database = Database::getInstance();
        $blockName = $database->escapeString($blockName);
        $theme = $database->escapeString($theme);
        $themeRegion = $database->escapeString($themeRegion);
        $blockID = $database->escapeString($blockID);
        $success = $database->updateTable('block', "blockName='{$blockName}', theme='{$theme}', themeRegion='{$themeRegion}', weight={$weight}, enabled={$isEnabled}, module={$moduleInCharge}", "blockID={$blockID}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function deleteBlock($blockID) {
        if (!is_numeric($blockID)) {
            return false;
        }
        $database = Database::getInstance();
        $blockID = $database->escapeString($blockID);
        $success = $database->removeData('block', "blockID={$blockID}");
        if ($success === false) {
            return false;
        }
        if ($success === null) {
            return false;
        }
        return true;
    }
    public function getRawBlockDataByName($blockName) {
        $blockName = preg_replace('/\s+/', '', strip_tags($blockName));
        $database = Database::getInstance();
        $blockName = $database->escapeString($blockName);
        $data = $database->getData('*', 'block', "blockName='{$blockName}'");
        if ($data === false) {
            return false;
        }
        if ($data === null) {
            return false;
        }
        if (count($data) > 1) {
            return false;
        }
        return $data[0];
    }
    public function getRawBlockDataByID($blockID) {
        if (!is_numeric($blockID)) {
            return false;
        }
        $database = Database::getInstance();
        $blockID = $database->escapeString($blockID);
        $data = $database->getData('*', 'block', "blockID={$blockID}");
        if ($data === false) {
            return false;
        }
        if ($data === null) {
            return false;
        }
        return $data;
    }
}