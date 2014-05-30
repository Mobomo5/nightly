<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 15/05/14
 * Time: 10:47 AM
 *
 * menu DB
 * -------
 *
 * menuID
 * menuName
 * themeRegion
 * enabled
 */
require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(MENU_ITEM_OBJECT_FILE);
class menu {
    //vars for the menu based of db schema
    private $menuID;
    private $menuName;
    private $themeRegion;
    private $enabled;
    private $menuItems = array();

    public function __construct($inID, $inName, $inThemeRegion, array $inMenuItems, $inEnabled) {
        //region checks
        if (!is_numeric($inID)) {
            return;
        }
        if (!is_string($inName)) {
            return;
        }
        if (!is_string($inThemeRegion)) {
            return;
        }
        //endregion
        $this->menuID = $inID;
        $this->menuName = $inName;
        $this->themeRegion = $inThemeRegion;
        $this->enabled = $inEnabled;
        $this->menuItems = $inMenuItems;
    }
    //region get
    public function getID() {
        return $this->menuID;
    }
    public function getName() {
        return $this->menuName;
    }
    public function getThemeRegion() {
        return $this->themeRegion;
    }
    public function getMenuItems() {
        return $this->menuItems;
    }
    public function getHTML() {
        $toReturn = '';

        foreach ($this->menuItems as $child) {
            $toReturn .= '<li>' . $child->getHTML() . '</li>';
        }
        return $toReturn;
    }
    public function isEnabled() {
        if ($this->enabled == 0) {
            return false;
        } else {
            return true;
        }
    }
    //endregion
    //region set
    public function setName($inName) {
        $this->menuName = $inName;
    }
    public function setThemeRegion($inThemeRegion) {
        $this->themeRegion = $inThemeRegion;
    }
    public function setEnabled($inSetEnabled) {
        if ($inSetEnabled == false || $inSetEnabled == 0) {
            $this->enabled = 0;
        } else {
            if ($inSetEnabled == true || $inSetEnabled == 1) {
                $this->enabled = 1;
            } else {
                return;
            }
        }
    }
    //endregion
    public function __toString() {
        //loop through it's menu items and put them in a HTML list
        return $this->getHTML();
    }
}

