<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 22/04/14
 * Time: 6:32 AM
 *
 * This file contains details for statuses or comments posted to nodes.
 */

require_once(DATABASE_OBJECT_FILE);

class currentStatus {
    private $statusID; //The unique identifier for the status.
    private $posterID; //The author of the status.
    private $parentStatus; //The status this status may be a comment on.
    private $supporterCount; //The amount of people who "liked" the comment.
    private $nodeID; //The node that the status/comment was posted on.

    private function __construct() {

        $this->statusID = null;
        $this->posterID = null;
        $this->parentStatus = null;
        $this->supporterCount = 0;
        $this->nodeID = null;
    }


} 