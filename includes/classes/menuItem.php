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

    public function __construct($inID, $inMenuID, $inLinkText, link $inHref, $inWeight, $inHasChildren, $inEnabled, menuItem $inParent = null, array $inChildren = array()) {
        //region constructor checks
        if (!is_numeric($inID)) {
            return;
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
        $this->parent = $inParent;
        $this->children = $inChildren;
    }

    //region get
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

    public function getWeight()
    {
        return $this->weight;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return $this->hasChildren;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }
    //endregion

    //region set
    public function setMenuID($inMenuID)
    {
        if (!is_numeric($inMenuID)) {
            return;
        }
        if ($inMenuID < 1) {
            return;
        }
        $this->menuID = $inMenuID;
    }

    public function setLinkText($inLinkText)
    {
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
    //endregion

    //region child functions
    public function addChild(menuItem $inChild) {
        if (!is_object($inChild)) {
            return;
        }

        array_push($this->children, $inChild);
    }

    public function removeChild($inChildID) {
        if (!is_numeric($inChildID)) {
            return;
        }
        if ($inChildID < 1) {
            return;
        }
        for ($i = 0; $i < count($this->children); $i++) {
            if ($this->children[$i]->getID() == $inChildID) {
                unset($this->children[$i]);
            }
        }
    }
    //endregion
}