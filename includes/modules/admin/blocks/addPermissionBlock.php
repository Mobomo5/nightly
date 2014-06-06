<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/5/14
 * Time: 6:44 PM
 */
class addPermissionBlock implements block {

    private $title;
    private $content;

    public function __construct() {

        $perm = permissionEngine::getInstance()->getPermission('userCanAddPermissions');
        if (!$perm->canDo()) {
            return false;
        }
        $this->title = 'Add permissions';
        $this->content = '<form method="post" action="' . new link('admin') . '">
                            <label>Permission Name:</label><input type="text"/>

                            </form>';
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
    }

    public function getType() {
        return get_class($this);
    }
}