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
    //Constructor Start -- Get database and permissions engine.
    private $db;
    private $permissionObject;
    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    //region Get
    public function getMenu($inMenuID) {
        //get a single menu from the database based off of ID
        if (!is_numeric($inMenuID)) {
            return;
        }
        try {
            // get the menu specified
            $results = $this->db->getData("*", "menu", "menuID = '$inMenuID'");
            $menuID = $results[0]['menuID'];
            $menuName = $results[0]['menuName'];
            $menuRegion = $results[0]['themeRegion'];
            $menuEnabled = !!$results[0]['enabled']; // convert to bool

            // get all top level menu items for that menu
            $itemResults = $this->db->getData("*", "menuItem", "menuID = '$menuID' AND parent = '0' ORDER BY weight");
            // turn each top level into a menuItem object
            $menuItems = array();
            foreach ($itemResults as $item) {
                $itemID = $item['menuItemID'];
                if (!$this->menuItemIsVisible($itemID)) {
                    continue;
                }
                $menuItems[] = $this->getMenuItem($itemID);
            }

            $menu = new menu($menuID, $menuName, $menuRegion, $menuItems, $menuEnabled);
            return $menu;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    public function getMenuItem($inMenuItemID) {
        //get a single menuItem from DB based off of ID
        if (!is_numeric($inMenuItemID)) {
            return;
        }
        try {
            // get all menu items for this menu
            $results = $this->db->getData("*", "menuItem", "menuItemID = '$inMenuItemID' ORDER BY weight");

            if (!$results) {
                return false;
            }
            // are there children?
            $children = array();
            if ($results[0]['hasChildren']) {
                $children = $this->getChildren($results[0]['menuItemID']);
            }
            // make a menuItem Object
            $menuItem = new menuItem($results[0]['menuID'],
                $results[0]['menuItemID'],
                $results[0]['linkText'],
                new link($results[0]['href']),
                $results[0]['weight'],
                !!$results[0]['hasChildren'], // !! as workaround for lack of boolval in php 5.3
                !!$results[0]['enabled'],
                $results[0]['parent'],
                $children);
            return $menuItem;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getChildren($inID) {
        if (!is_numeric($inID)) {
            return false;
        }

        $results = $this->db->getData('*', 'menuItem', "parent = '$inID'");

        if (!$results) {
            return false;
        }

        foreach ($results as $row) {
            $itemID = $row['menuItemID'];
            if (!$this->menuItemIsVisible($itemID)) {
                continue;
            }
            $children[] = $this->getMenuItem($itemID);
        }

        return $children;
    }
    //endregion

    //region Set
    public function setMenu(menu $inMenu) {
        //takes in a menu object and updates DB
        if (!is_object($inMenu)) {
            return;
        }
        if (!$this->permissionObject->checkPermission("userCanSetMenu")) {
            return;
        }
        try {
            $results = $this->db->updateTable("menu",
                "'menuName' = {$inMenu->getName()}, " .
                "'themeRegion' = {$inMenu->getThemeRegion()}, " .
                "'enabled' = {$inMenu->isEnabled()}, ",
                "'menuID' = {$inMenu->getID()}");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    public function setMenuItem(menuItem $inMenuItem) {
        //take sin a menuItem object and updates DB
        if (!is_object($inMenuItem)) {
            return;
        }
        if (!$this->permissionObject->checkPermission("userCanSetMenuItem")) {
            return;
        }
        try {
            $results = $this->db->updateTable("menuItem",
                "'menuID' = {$inMenuItem->getMenuID()}, " .
                "'linkText' = {$inMenuItem->getLinkText()}, " .
                "'href' = {$inMenuItem->getHref()}, " .
                "'weight' = {$inMenuItem->getWeight()}, " .
                "'hasChildren' = {$inMenuItem->getChildren()}, " .
                "'enabled' = {$inMenuItem->isEnabled()}, ",
                "'menuItemID' = {$inMenuItem->getID()}");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    //endregion

    //region Add
    public function addMenu($inName, $inThemeRegion, $inEnabled) {
        //Adds a new menu to the database
        if (!is_string($inName)) {
            return;
        }
        if (!is_string($inThemeRegion)) {
            return;
        }
        if (!is_numeric($inEnabled)) {
            return;
        }
        if (!$this->permissionObject->checkPermission("userCanAddMenu")) {
            return;
        }
        try {
            $results = $this->db->insertData("menu", "'menuName', 'themeRegion', 'enabled'", "$inName, $inThemeRegion, $inEnabled");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    public function addMenuItem($inMenuID, $inLinkText, link $inHref, $inWeight, $inHasChildren, $inEnabled, $inParent) {
        //Adds a new menuItem to the database
        if (!$this->permissionObject->checkPermission("userCanAddMenuItem")) {
            return;
        }
        try {
            $results = $this->db->insertData("menuItem",
                "'menuID', 'linkText', 'href', 'weight', 'hasChildren', 'enabled', 'parent'",
                "$inMenuID, $inLinkText, $inHref, $inWeight, $inHasChildren, $inEnabled, $inParent");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    //endregion

    //region Delete
    public function deleteMenu($inMenuID) {
        //deletes a menu from the DB
        if (!is_numeric($inMenuID)) {
            return;
        }
        if (!$this->permissionObject->checkPermission("userCanDeleteMenu")) {
            return;
        }
        try {
            $results = $this->db->removeData("menu", "'menuID' = $inMenuID");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }
    }
    public function deleteMenuItem($inMenuItemID) {
        //deletes a menuItem from database
        if (!is_numeric($inMenuItemID)) {
            return;
        }
        if (!$this->permissionObject->checkPermission("userCanDeleteMenuItem")) {
            return;
        }
        try {
            $results = $this->db->removeData("menuItem", "'menuItemID' = $inMenuItemID");
            return;
        }
        catch (exception $ex) {
            return $ex->getMessage();
        }

    }

    private function menuItemIsVisible($inID) {
        if (!is_numeric($inID)) {
            return false;
        }
        // get the item from the visibility table
        $results = $this->db->getData('referenceID, referenceType, visible', 'menuItemVisibility', "menuItemID = '$inID'");

        // if it's false, there was an error. For safety, bail out.
        if ($results === false) {
            return false;
        }
        // if it's not there, by default we allow it.
        if ($results === null) {
            return true;
        }
        // it's in there. find out if it's visible

        foreach ($results as $row) {
            $referenceID = $row['referenceID'];
            $referenceType = $row['referenceType'];
            $visible = $row['visible'];

            if (!$visible) {
                continue;
            }
            if ($referenceType == 'roleID') {
                if (currentUser::getUserSession()->getRoleID() == $referenceID) {
                    return true;
                }
            }

        }
        return false;
    }
    //endregion
}