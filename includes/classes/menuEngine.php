<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 */

require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

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

    public function getMenu($inMenuID) {
        //get a single menu from the database based off of ID
        try {

            $results = $this->db->getData("*", "menu", "'menuID' = $inMenuID");

            $menu = new menu($results[0]['menuID'],
                $results[0]['menuName'],
                $results[0]['themeRegion'],
                $this->getMenuItem($results[0]['menuID']),
                $results[0]['enabled']);

            return $menu;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMenuItem($inMenuID) {
        //get a single menuItem from DB based off of ID


        try {
            $results = $this->db->getData("*", "menuItem", "'menuID' = $inMenuID");

            $menuItem = new menuItem($results[0]['menuID'],
                $results[0]['menuItemID'],
                $results[0]['linkText'],
                $results[0]['href'],
                $results[0]['weight'],
                $results[0]['hasChildren'],
                $results[0]['enabled'],
                $results[0]['parent']);

            return $menuItem;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function setMenu(menu $inMenu) {
        //takes in a menu object and updates DB
        if (!is_object($inMenu)) {
            return;
        }

        try {

            $results = $this->db->updateTable("menu",
                "'menuName' = {$inMenu->getName()}, " .
                "'themeRegion' = {$inMenu->getThemeRegion()}, " .
                "'enabled' = {$inMenu->isEnabled()}, ",
                "'menuID' = {$inMenu->getID()}");

            return;

        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function setMenuItem(menuItem $inMenuItem) {
        //take sin a menuItem object and updates DB
        if (!is_object($inMenuItem)) {
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

        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function addMenu($inName, $inThemeRegion, $inEnabled) {
        //Adds a new menu to the database
        if (!is_string($inName)) return;
        if (!is_string($inThemeRegion)) return;
        if (!is_numeric($inEnabled)) return;

        try {
            $results = $this->db->insertData("menu", "'menuName', 'themeRegion', 'enabled'", "$inName, $inThemeRegion, $inEnabled");
            return;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function addMenuItem($inMenuID, $inLinkText, link $inHref, $inWeight, $inHasChildren, $inEnabled, $inParent) {
        //Adds a new menuItem to the database
        try {
            $results = $this->db->insertData("menuItem",
                "'menuID', 'linkText', 'href', 'weight', 'hasChildren', 'enabled', 'parent'",
                "$inMenuID, $inLinkText, $inHref, $inWeight, $inHasChildren, $inEnabled, $inParent");
            return;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function deleteMenu($inMenuID) {
        //deletes a menu from the DB
        if(!is_numeric($inMenuID)) return;

        try {
            $results = $this->db->removeData("menu","'menuID' = $inMenuID");
            return;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function deleteMenuItem($inMenuItemID) {
        //deletes a menuItem from database
        if(!is_numeric($inMenuItemID)) return;

        try {
            $results = $this->db->removeData("menuItem","'menuItemID' = $inMenuItemID");
            return;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }
}