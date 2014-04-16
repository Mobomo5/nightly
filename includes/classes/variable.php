<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:12 PM
 */
require_once(DATABASE_OBJECT_FILE);

class variable
{
    private $name;
    private $value;
    private $readOnly;

    public function __construct($inName, $inValue, $inReadOnly = false)
    {
        $this->name = $inName;
        $this->value = $inValue;
        $this->readOnly = $inReadOnly;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isReadOnly()
    {
        return $this->readOnly;
    }

    public function setName($inName)
    {
        if ($this->readOnly) {
            return false;
        }
        $database = databaseInterface::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return false;
        }
        $name = $database->escapeString($inName);
        if (!$database->updateTable('variable', 'variableName = \'' . $name . '\'', 'variableName = \'' . $this->name . '\'')) {
            return false;
        }
        $this->name = $name;
    }

    public function setValue($inValue)
    {
        if ($this->readOnly) {
            return false;
        }
        $database = databaseInterface::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return false;
        }
        $value = $database->escapeString($inValue);
        if (!$database->updateTable('variable', 'variableValue = \'' . $value . '\'', 'variableName = \'' . $this->name . '\'')) {
            return false;
        }
        $this->value = $value;

        return true;
    }

    public function setReadOnly($inReadOnly = true)
    {
        $database = databaseInterface::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            return false;
        }
        if ($inReadOnly == true) {
            if (!$database->updateTable('variable', 'readOnly = \'1\'', 'variableName = \'' . $this->name . '\'')) {
                return false;
            }
            $this->readOnly = true;
            return true;
        }
        if ($inReadOnly == false) {
            if (!$database->updateTable('variable', 'readOnly = \'0\'', 'variableName = \'' . $this->name . '\'')) {
                return false;
            }
            $this->readOnly = false;
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function save()
    {
        if (!$this->setName($this->name)) {
            return false;
        }
        if (!$this->setValue($this->value)) {
            return false;
        }
        if (!$this->setReadOnly($this->readOnly)) {
            return false;
        }
        return true;
    }
}