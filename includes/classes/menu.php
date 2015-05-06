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
        if(! is_bool($inEnabled)) {
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
        $toReturn = '<ul>';
        foreach ($this->menuItems as $child) {
            $toReturn .= '<li>' . $child->getHTML() . '</li>';
        }
        $toReturn .= '</ul>';
        return $toReturn;
    }
    public function isEnabled() {
        return $this->enabled;
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
        if (! is_bool($inSetEnabled)) {
            return;
        }
        $this->enabled = $inSetEnabled;
    }
    //endregion
    public function __toString() {
        //loop through it's menu items and put them in a HTML list
        return $this->getHTML();
    }
}