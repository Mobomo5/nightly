<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 10:24 AM
 */
class userOption {
    private $id;
    private $computerName;
    private $humanName;
    private $description;

    /**
     * @param int $inId
     * @param string $inComputerName
     * @param string $inHumanName
     * @param string $inDescription
     * @internal param bool $inValue
     */
    public function __construct($inId, $inComputerName, $inHumanName, $inDescription) {

        if (!is_numeric($inId)) {
            return false;
        }
        $nameVal = new validator('optionName');
        if (!$nameVal->validate($inComputerName)) {
            return false;
        }

        $this->id = $inId;
        $this->computerName = $inComputerName;
        $this->humanName = $inHumanName;
        $this->description = $inDescription;


    }

    /**
     * @param string $computerName
     * @return bool
     */
    public function setComputerName($computerName) {
        $nameVal = new validator('optionName');
        if (!$nameVal->validate($computerName)) {
            return false;
        }

        $this->computerName = $computerName;
        return true;
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
        $this->description = $description;
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
        $this->humanName = $humanName;
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
    public function getId() {
        return $this->id;
    }

    /**
     * @param bool $value
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function getValue() {
        return $this->value;
    }

}