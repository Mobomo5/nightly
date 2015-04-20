<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/2/14
 * Time: 6:52 PM
 */

require_once(NODE_ENGINE_OBJECT_FILE);

class statusBlock implements block {

    private $content;

    public function __construct($inBlockID) {

        if (!permissionEngine::getInstance()->currentUserCanDo('userCanViewStatuses')) {
            return false;
        }

        // get params
        $params = router::getInstance()->getParameters(true);
        if (empty($params[1])) {
            return false;
        }
        if (!is_numeric(end($params))) {
            return false;
        }

        // is it a user page?
        if (strtolower($params[0]) === 'users') {

            $statuses = statusEngine::getInstance()->retrieveStatusFromDatabaseByUser($params[1]);
        } else {
            $statuses = statusEngine::getInstance()->retrieveStatusFromDatabaseByNode($params[1]);
        }

        if (!$statuses) {
            return false;
        }

        $this->content = $statuses;
    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
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