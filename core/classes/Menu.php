<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 15/05/14
 * Time: 10:47 AM
 *
 * menus DB
 * -------
 *
 * menuID
 * menuName
 * themeRegion
 * enabled
 */
class Menu {
    //vars for the menus based of db schema
    private $menuID;
    private $computerName;
    private $humanName;
    private $themeRegion;
    private $enabled;
    private $menuItems = array();
    public function __construct($inID, $inComputerName, $inHumanName, $inThemeRegion, array $inMenuItems, $inEnabled) {
        //region checks
        if (!is_numeric($inID)) {
            return;
        }
        if(! is_string($inComputerName)) {
            return;
        }
        if (!is_string($inHumanName)) {
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
        $this->computerName = str_replace(' ', '', $inComputerName);
        $this->humanName = $inHumanName;
        $this->themeRegion = $inThemeRegion;
        $this->enabled = $inEnabled;
        $this->menuItems = $inMenuItems;
    }
    //region get
    public function getID() {
        return $this->menuID;
    }
    public function getComputerName() {
        return $this->computerName;
    }
    public function getHumanName() {
        return $this->humanName;
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
    public function setComputerName($inComputerName) {
        if(! is_string($inComputerName)) {
            return;
        }
        $this->computerName = str_replace(' ', '', $inComputerName);
    }
    public function setHumanName($inHumanName) {
        if(! is_string($inHumanName)) {
            return;
        }
        $this->humanName = $inHumanName;
    }
    public function setThemeRegion($inThemeRegion) {
        if(! is_string($inThemeRegion)) {
            return;
        }
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
        //loop through it's menus items and put them in a HTML list
        return $this->getHTML();
    }
}