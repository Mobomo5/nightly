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
    public function __construct($id, $name, $description = '') {
        //validate
        if (!is_numeric($id)) {
            return;
        }
        if (strlen($name) > $this->MAXNAMESIZE) {
            return;
        }
        if (strlen($description) > $this->MAXDESCSIZE) {
            return;
        }
        $this->id = $id;
        $this->name = strip_tags($name);
        $this->description = strip_tags($description);
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
            return;
        }
        $this->description = strip_tags($inDescription);
    }
    /**
     * @return mixed
     */
    public function getID() {
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
            return;
        }
        $this->name = strip_tags($inName);
    }
}