<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(MENU_ITEM_OBJECT_FILE);
require_once(MENU_OBJECT_FILE);
class menuEngine {
    /* Checking to see if the instance variable is holding onto to status engine object
     * and if it's not create one.
     */
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new menuEngine();
        }
        return self::$instance;
    }
    private $foundMenus;
    private $foundMenuItems;
    private function __construct() {
        $this->foundMenus = array();
        $this->foundMenuItems = array();
    }
    //region Get
    public function getMenu($inMenuID) {
        //get a single menu from the database based off of ID
        if (!is_numeric($inMenuID)) {
            return false;
        }
        if(isset($this->foundMenus[$inMenuID])) {
            return $this->foundMenus[$inMenuID];
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inMenuID = $database->escapeString($inMenuID);
        // get the menu specified
        $results = $database->getData("*", "menu", "menuID = {$inMenuID}");
        if ($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $menuID = $results[0]['menuID'];
        $menuName = $results[0]['menuName'];
        $menuRegion = $results[0]['themeRegion'];
        $menuEnabled = !!$results[0]['enabled']; // convert to bool
        // get all top level menu items for that menu
        $itemResults = $database->getData("*", "menuItem", "menuID = {$menuID} AND parent = 0 ORDER BY weight");
        // turn each top level into a menuItem object
        $menuItems = array();
        foreach ($itemResults as $item) {
            $itemID = $item['menuItemID'];
            if (!$this->menuItemIsVisible($itemID, PAGE_TYPE, currentUser::getUserSession()->getRoleID())) {
                continue;
            }
            $menuItem = $this->getMenuItem($itemID);
            if($menuItem == false) {
                continue;
            }
            $menuItems[] = $menuItem;
        }
        $menu = new menu($menuID, $menuName, $menuRegion, $menuItems, $menuEnabled);
        $this->foundMenus[$menuID] = $menu;
        return $menu;
    }
    public function getMenuItem($inMenuItemID) {
        //get a single menuItem from DB based off of ID
        if (!is_numeric($inMenuItemID)) {
            return false;
        }
        if(! $this->menuItemIsVisible($inMenuItemID, PAGE_TYPE, currentUser::getUserSession()->getRoleID())) {
            return false;
        }
        if(isset($this->foundMenuItems[$inMenuItemID])) {
            return $this->foundMenuItems[$inMenuItemID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inMenuItemID = $database->escapeString($inMenuItemID);
        // get all menu items for this menu
        $results = $database->getData("*", "menuItem", "menuItemID = {$inMenuItemID} ORDER BY weight");
        if ($results == false) {
            return false;
        }
        if ($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        // are there children?
        $children = null;
        $hasChildren = null;
        if ($results[0]['hasChildren'] == 1) {
            $hasChildren = true;
            $children = $this->getMenuItemChildren($results[0]['menuItemID']);
        }
        if ($children == false) {
            $hasChildren = false;
            $children = array();
        }
        // make a menuItem Object
        $menuItem = new menuItem($results[0]['menuID'],
            $results[0]['menuItemID'],
            $results[0]['linkText'],
            new link($results[0]['href']),
            $results[0]['weight'],
            $hasChildren, // !! as workaround for lack of boolval in php 5.3
            !!$results[0]['enabled'],
            $results[0]['parent'],
            $children);
        return $menuItem;
    }
    private function getMenuItemChildren($inID) {
        if (!is_numeric($inID)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inID = $database->escapeString($inID);
        $results = $database->getData('*', 'menuItem', "parent = {$inID}");
        if ($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        foreach ($results as $row) {
            $itemID = $row['menuItemID'];
            if (!$this->menuItemIsVisible($itemID, PAGE_TYPE, currentUser::getUserSession()->getRoleID())) {
                continue;
            }
            $children[] = $this->getMenuItem($itemID);
        }
        return $children;
    }
    public function setMenu(menu $inMenu) {
        //takes in a menu object and updates DB
        $permissionEngine = permissionEngine::getInstance();
        if (!$permissionEngine->checkPermission("userCanEditMenus")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $menuID = $database->escapeString($inMenu->getID());
        $menuName = $database->escapeString($inMenu->getName());
        $themeRegion = $database->escapeString($inMenu->getThemeRegion());
        $enabled = $inMenu->isEnabled();
        if($enabled == true) {
            $enabled = 1;
        } else {
            $enabled = 0;
        }
        $results = $database->updateTable("menu", "menuName = '{$menuName}', themeRegion = '{$themeRegion}', enabled = {$enabled}", "menuID = {$menuID}");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function setMenuItem(menuItem $inMenuItem) {
        $permissionEngine = permissionEngine::getInstance();
        if (!$permissionEngine->checkPermission("userCanEditMenuItems")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $menuItemID = $database->escapeString($inMenuItem->getID());
        $menuID = $database->escapeString($inMenuItem->getMenuID());
        $linkText = $database->escapeString($inMenuItem->getLinkText());
        $linkHref = $database->escapeString($inMenuItem->getHref()->getRawHref());
        $weight = $database->escapeString($inMenuItem->getWeight());
        $parent = $database->escapeString($inMenuItem->getParent());
        if($inMenuItem->hasChildren() == true) {
            $hasChildren = 1;
        } else {
            $hasChildren = 0;
        }
        if($inMenuItem->isEnabled() == true) {
            $enabled = 1;
        } else {
            $enabled = 0;
        }
        $results = $database->updateTable("menuItem", "menuID = {$menuID}, linkText = '{$linkText}', href = '{$linkHref}', weight = {$weight}, parent={$parent}, hasChildren = {$hasChildren}, enabled = {$enabled}", "menuItemID = {$menuItemID}");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function addMenu(menu $inMenu) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo("userCanAddMenus")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $menuName = $database->escapeString($inMenu->getName());
        $themeRegion = $database->escapeString($inMenu->getThemeRegion());
        if($inMenu->isEnabled()) {
            $enabled = 1;
        } else {
            $enabled = 0;
        }
        $results = $database->insertData("menu", "'menuName', 'themeRegion', 'enabled'", "'{$menuName}', '{$themeRegion}', {$enabled}");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function addMenuItem(menuItem $inMenuItem) {
        $permissionEngine = permissionEngine::getInstance();
        if (!$permissionEngine->currentUserCanDo("userCanAddMenuItems")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $menuID = $database->escapeString($inMenuItem->getMenuID());
        $linkText = $database->escapeString($inMenuItem->getLinkText());
        $linkHref = $database->escapeString($inMenuItem->getHref()->getRawHref());
        $weight = $database->escapeString($inMenuItem->getWeight());
        $parent = $database->escapeString($inMenuItem->getParent());
        if($inMenuItem->hasChildren()) {
            $hasChildren = 1;
        } else {
            $hasChildren = 0;
        }
        if($inMenuItem->isEnabled()) {
            $enabled = 1;
        } else {
            $enabled = 0;
        }
        $results = $database->insertData("menuItem", "menuID, linkText, href, weight, hasChildren, enabled, parent", "{$menuID}, '{$linkText}', '{$linkHref}', {$weight}, {$hasChildren}, {$enabled}, {$parent}");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function deleteMenu($inMenuID) {
        if (!is_numeric($inMenuID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if (! $permissionEngine->currentUserCanDo("userCanDeleteMenus")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inMenuID = $database->escapeString($inMenuID);
        $results = $database->removeData("menu", "menuID = {$inMenuID}");
        if($results == false) {
            return false;
        }
        return true;
    }
    public function deleteMenuItem($inMenuItemID) {
        if (!is_numeric($inMenuItemID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if (! $permissionEngine->currentUserCanDo("userCanDeleteMenuItems")) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inMenuItemID = $database->escapeString($inMenuItemID);
        $results = $database->removeData("menuItem", "menuItemID = {$inMenuItemID}");
        if($results == false) {
            return false;
        }
        return true;
    }
    private function menuItemIsVisible($menuItemID, $pageType, $roleID) {
        if (!is_numeric($menuItemID)) {
            return false;
        }
        if ($menuItemID < 1) {
            return false;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $menuItemID = $database->escapeString($menuItemID);
        // check to see if it's in the visibility table
        $results = $database->getData('*', 'menuItemVisibility', 'menuItemID = ' . $menuItemID);
        //Default is to display the block unless specified.
        if ($results === null) {
            return true;
        }
        //Query failed. Play it safe and don't display the block.
        if ($results === false) {
            return false;
        }
        $comparators = array('referenceType' => '', 'referenceValue' => '');
        $hookEngine = hookEngine::getInstance();
        $comparators = $hookEngine->runFilter('menuItemVisibilityComparator', $comparators);
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
            if ($rule['referenceID'][0] == '!') {
                $vote = $this->menuItemVisibleNegate($rule, $finalComparators);
                if ($vote == -1) {
                    $countOfDoNotDisplays += 1;
                    continue;
                }
                if ($vote == 1) {
                    $countOfDoDisplays += 1;
                    continue;
                }
                //No vote on any other value.
                continue;
            }
            if ($finalComparators[$rule['referenceType']] != $rule['referenceID']) {
                continue;
            }
            if ($rule['visible'] == 0) {
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
    private function menuItemVisibleNegate($rule, $finalComparators) {
        //Take the ! off
        $rule['referenceID'] = substr($rule['referenceID'], 1);
        if (!isset($finalComparators[$rule['referenceType']])) {
            return 0;
        }
        //Negate means vote for anything that does not match. Move on if it matches; don't vote.
        if ($finalComparators[$rule['referenceType']] == $rule['referenceID']) {
            return 0;
        }
        if ($rule['visible'] == 0) {
            return -1;
        }
        return 1;
    }
    public function setMenuItemVisibility($inMenuItemID, $referenceID, $referenceType, $isVisible = false) {
        if (!is_numeric($inMenuItemID)) {
            return false;
        }
        if ($inMenuItemID < 1) {
            return false;
        }
        if (!is_bool($isVisible)) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inMenuItemID = $database->escapeString($inMenuItemID);
        $whereClause = "referenceID='{$referenceID}' AND referenceType='{$referenceType}' AND menuItemID={$inMenuItemID}";
        $exists = $database->getData('ruleID', 'menuItemVisibility', $whereClause);
        if ($exists == false) {
            return false;
        }
        if ($exists != null) {
            return $this->insertNewVisibilityRule($inMenuItemID, $referenceID, $referenceType, $isVisible);
        }
        if ($isVisible == true) {
            $visible = 1;
        } else {
            $visible = 0;
        }
        $success = $database->updateTable('menuItemVisibility', "visibile={$visible}", $whereClause);
        if ($success == false) {
            return false;
        }
        if ($success == null) {
            return false;
        }
        return true;
    }
    private function insertNewVisibilityRule($inMenuItemID, $referenceID, $referenceType, $isVisible = false) {
        if (!is_numeric($inMenuItemID)) {
            return false;
        }
        if ($inMenuItemID < 1) {
            return false;
        }
        if (!is_bool($isVisible)) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inMenuItemID = $database->escapeString($inMenuItemID);
        if ($isVisible == true) {
            $visible = 1;
        } else {
            $visible = 0;
        }
        $success = $database->insertData('menuItemVisibility', 'referenceID, referenceType, visible, menuItemID', "'{$referenceID}', '{$referenceType}', {$visible}, {$inMenuItemID}");
        if ($success == false) {
            return false;
        }
        if ($success == null) {
            return false;
        }
        return true;
    }
    public function deleteMenuItemVisibilityRule($inMenuItemID, $referenceID, $referenceType) {
        if (!is_numeric($inMenuItemID)) {
            return false;
        }
        if ($inMenuItemID < 1) {
            return false;
        }
        $referenceID = preg_replace('/\s+/', '', strip_tags($referenceID));
        $referenceType = preg_replace('/\s+/', '', strip_tags($referenceType));
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $referenceType = $database->escapeString($referenceType);
        $referenceID = $database->escapeString($referenceID);
        $inMenuItemID = $database->escapeString($inMenuItemID);
        $success = $database->removeData('menuItemVisibility', "referenceID='{$referenceID}' AND referenceType='{$referenceType}' AND menuItemID={$inMenuItemID}");
        if ($success == false) {
            return false;
        }
        if ($success == null) {
            return false;
        }
        return true;
    }
}