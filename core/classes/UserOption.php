<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 10:24 AM
 */
class UserOption {
    private $id;
    private $computerName;
    private $humanName;
    private $description;
    /**
     * @param int $inId
     * @param string $inComputerName
     * @param string $inHumanName
     * @param string $inDescription
     */
    public function __construct($inId, $inComputerName, $inHumanName, $inDescription) {
        if (!is_numeric($inId)) {
            return;
        }
        $this->id = $inId;
        $this->computerName = preg_replace('/\s+/', '', $inComputerName);
        $this->humanName = strip_tags($inHumanName);
        $this->description = strip_tags($inDescription);
    }
    public function setComputerName($computerName) {
        $this->computerName = preg_replace('/\s+/', '', $computerName);
    }
    /**
     * @return string
     */
    public function getComputerName() {
        return $this->computerName;
    }
    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = strip_tags($description);
    }
    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    /**
     * @param string $humanName
     */
    public function setHumanName($humanName) {
        $this->humanName = strip_tags($humanName);
    }
    /**
     * @return string
     */
    public function getHumanName() {
        return $this->humanName;
    }
    /**
     * @return int
     */
    public function getID() {
        return $this->id;
    }
}