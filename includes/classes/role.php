<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 12:45 PM
 */
class role {
    private $id;
    private $name;
    private $description;
    private $MAXNAMESIZE = 50;
    private $MAXDESCSIZE = 500;


    public function __construct($id = '0', $name = '', $description = '') {
        //validate
        if (!is_numeric($id)) {
            return false;
        }

        if (strlen($name) > $this->MAXNAMESIZE) {
            return false;
        }
        if (strlen($description) > $this->MAXDESCSIZE) {
            return false;
        }

        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        return true;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param $inDescription string description to assign the role
     * @return bool
     */
    public function setDescription($inDescription) {
        if (strlen($inDescription) > $this->MAXDESCSIZE) {
            return false;
        }
        $this->description = $inDescription;
        return true;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param $inName string name to give the role
     * @return bool
     */
    public function setName($inName) {
        if (strlen($inName) > $this->MAXNAMESIZE) {
            return false;
        }
        $this->name = $inName;
        return true;
    }

    public function toString() {
        return "roleID = $this->id name = $this->name description = $this->description";
    }


}