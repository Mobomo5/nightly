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
    private $value;

    /**
     * @param int $inId
     * @param string $inComputerName
     * @param string $inHumanName
     * @param string $inDescription
     * @param bool $inValue
     */
    public function __construct($inId, $inComputerName = null, $inHumanName = null, $inDescription = null, $inValue = null) {
        if (!empty($inId)) {
            $this->id = $inId;
        }
        if (!empty($inComputerName)) {
            $this->computerName = $inComputerName;
        }
        if (!empty($inHumanName)) {
            $this->humanName = $inHumanName;
        }
        if (!empty($inDescription)) {
            $this->description = $inDescription;
        }
        if (!empty($inValue)) {
            $this->value = $inValue;
        }
    }

    /**
     * @param string $computerName
     */
    public function setComputerName($computerName) {
        $this->computerName = $computerName;
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