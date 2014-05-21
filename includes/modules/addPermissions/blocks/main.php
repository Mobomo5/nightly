<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 5:39 PM
 */
class main implements node {

    private $content;
    private $title;

    public function __construct() {
        $this->title = 'Add an permission';
        // get all possible roles from the db
        $db = database::getInstance();
        $roles = $db->getData('*', 'role');


        if (!empty($_POST['humanName']) AND !empty($_POST['computerName'])) {

            $description = '';


            if (!empty($_POST['description'])) {
                $description = $_POST['description'];
            }
            if (permissionEngine::getInstance()->addPermission(new permission(123, $_POST['computerName'], $_POST['humanName'], $description))) {
                $this->title = 'submitted!';
            }

        }

        $this->content = '
            <form name="addPermission" action="#" method="post">
                Human Name: <input type="text" name="humanName" /><br/>
                Computer Name: <input type="text" name="computerName" /><br/>
                Description: <input type="text" name="description" /><br/>';
        foreach ($roles as $role) {
            $this->content .= '<input type="checkbox" name="role" value="' . $role['roleID'] . '"> ' . $role['roleName'] . '<br/>';
        }

        $this->content .= '<input type="submit"/>
            </form>
        ';
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

    public function pageAuthorIsVisible() {
        // TODO: Implement pageAuthorIsVisible() method.
    }

    public function datePagePublishedIsVisible() {
        // TODO: Implement datePagePublishedIsVisible() method.
    }

    public function getDatePagePublished() {
        // TODO: Implement getDatePagePublished() method.
    }

    public function getPageAuthor() {
        // TODO: Implement getPageAuthor() method.
    }

    public static function getNodeType() {
        // TODO: Implement getNodeType() method.
    }

    public function statusesAreVisible() {
        // TODO: Implement statusesAreVisible() method.
    }

    public function getStatuses() {
        // TODO: Implement getStatuses() method.
    }

    public function noGUI() {
        // TODO: Implement noGUI() method.
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }
}