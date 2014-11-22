<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 *
 * menuItem DB
 * -----------
 * menuItemID INT
 * menuID INT
 * linkText VARCHAR(50)
 * href VARCHAR(2000)
 * weight INT
 * hasChildren INT
 * enabled INT
 * parent INT
 */
require_once(LINK_OBJECT_FILE);
class menuItem {
    private $id;
    private $menuID;
    private $linkText;
    private $href;
    private $weight;
    private $hasChildren;
    private $enabled;
    private $parent;
    private $children = array();
    public function __construct($inID, $inMenuID, $inLinkText, link $inHref, $inWeight, $inHasChildren, $inEnabled, $parent, array $inChildren = array()) {
        if (!is_numeric($inID)) {
            return;
        }
        if (!is_numeric($parent)) {
            return false;
        }
        if ($inID < 1) {
            return;
        }
        if (!is_numeric($inMenuID)) {
            return;
        }
        if ($inMenuID < 1) {
            return;
        }
        if (!is_string($inLinkText)) {
            return;
        }
        if (!is_numeric($inWeight)) {
            return;
        }
        if (!is_bool($inHasChildren)) {
            return;
        }
        if (!is_bool($inEnabled)) {
            return;
        }
        //endregion
        $this->id = $inID;
        $this->menuID = $inMenuID;
        $this->linkText = trim(htmlspecialchars($inLinkText));
        $this->href = $inHref;
        $this->weight = $inWeight;
        $this->hasChildren = $inHasChildren;
        $this->enabled = $inEnabled;
        $this->parent = $parent;
        $this->children = $inChildren;
    }
    public function getID() {
        return $this->id;
    }
    public function getMenuID() {
        return $this->menuID;
    }
    public function getLinkText() {
        return $this->linkText;
    }
    public function getHref() {
        return $this->href;
    }
    public function getWeight() {
        return $this->weight;
    }
    public function getParent() {
        return $this->parent;
    }
    public function getChildren() {
        return $this->children;
    }
    public function hasChildren() {
        return $this->hasChildren;
    }
    public function isEnabled() {
        return $this->enabled;
    }
    public function getHTML() {
        $toReturn = '<a href="' . $this->getHref() . '">' . $this->getLinkText() . '</a>';
        if (!$this->hasChildren()) {
            return $toReturn;
        }
        $toReturn .= '<ul>';
        foreach ($this->children as $child) {
            $toReturn .= '<li>' . $child->getHTML() . '</li>';
        }
        $toReturn .= '</ul>';
        return $toReturn;
    }
    public function setMenuID($inMenuID) {
        if (!is_numeric($inMenuID)) {
            return;
        }
        if ($inMenuID < 1) {
            return;
        }
        $this->menuID = $inMenuID;
    }
    public function setLinkText($inLinkText) {
        if (!is_string($inLinkText)) {
            return;
        }
        $this->linkText = trim(htmlspecialchars($inLinkText));
    }
    public function setHref(link $inHref) {
        $this->href = $inHref;
    }
    public function setWeight($inWeight) {
        if (!is_numeric($inWeight)) {
            return;
        }
        $this->weight = $inWeight;
    }
    public function setHasChildren($inHasChildren) {
        if (!is_bool($inHasChildren)) {
            return;
        }
        $this->hasChildren = $inHasChildren;
    }
    public function setEnabled($inSetEnabled) {
        if (!is_bool($inSetEnabled)) {
            return;
        }
        $this->enabled = $inSetEnabled;
    }
    public function setParent(menuItem $inParent) {
        $this->parent = $inParent->getID();
    }
}