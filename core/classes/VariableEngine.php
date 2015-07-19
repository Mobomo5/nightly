<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 1/1/14
 * Time: 5:51 PM
 */
class VariableEngine {
    private static $instance;
    private $foundVariables;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new VariableEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundVariables = array();
    }
    public function getVariable($variableName) {
        if ($variableName === '') {
            return false;
        }
        if ($variableName === null) {
            return false;
        }
        $variableName = preg_replace('/\s+/', '', $variableName);
        if(isset($this->foundVariables[$variableName])) {
            return $this->foundVariables[$variableName];
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $variableName = $database->escapeString(htmlspecialchars($variableName));
        $variableValue = $database->getData('variableValue, readOnly', 'variable', 'variableName=\'' . $variableName . '\'');
        if ($variableValue === false) {
            return false;
        }
        if($variableValue === null) {
            return false;
        }
        if (count($variableValue) > 1) {
            return false;
        }
        if ($variableValue[0]['readOnly'] === 1) {
            $toReturn = new Variable($variableName, $variableValue[0]['variableValue'], true);
            $this->foundVariables[$toReturn->getName()] = $toReturn;
            return $toReturn;
        }
        $toReturn = new Variable($variableName, $variableValue[0]['variableValue']);
        $this->foundVariables[$toReturn->getName()] = $toReturn;
        return $toReturn;
    }
    public function getVariables(array $variables = array()) {
        if (count($variables) === 0) {
            return false;
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $where = '';
        $toReturn = array();
        foreach ($variables as $variable) {
            if ($variable === null) {
                continue;
            }
            if ($variable === '') {
                continue;
            }
            $variable = preg_replace('/\s+/', '', $variable);
            if(isset($this->foundVariables[$variable])) {
                $toReturn[$variable] = $this->foundVariables[$variable];
                continue;
            }
            $variable = $database->escapeString(htmlspecialchars($variable));
            if ($where === '') {
                $where .= 'variableName = \'' . $variable . '\'';
            }
            $where .= ' OR variableName = \'' . $variable . '\'';
        }
        if ($where === '') {
            if(count($toReturn) > 0) {
                return $toReturn;
            }
            return false;
        }
        $results = $database->getData('variableName, variableValue, readOnly', 'variable', $where);
        if ($results === false) {
            return false;
        }
        if ($results === null) {
            return false;
        }
        foreach ($results as $result) {
            $variableName = $result['variableName'];
            $variableValue = $result['variableValue'];
            $readOnly = $result['readOnly'];
            if ($readOnly === 1) {
                $variable = new Variable($variableName, $variableValue, true);
                $toReturn[$variableName] = $variable;
                $this->foundVariables[$variableName] = $variable;
                continue;
            }
            $variable = new Variable($variableName, $variableValue);
            $this->foundVariables[$variableName] = $variable;
            $toReturn[$variableName] = $variable;
        }
        return $toReturn;
    }
    public function saveVariable(Variable $variableToSave) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $variableName = $database->escapeString(htmlspecialchars($variableToSave->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToSave->getValue()));
        $isReadOnly = $variableToSave->isReadOnly();
        if ($isReadOnly === true) {
            $isReadOnly = 1;
        } else {
            $isReadOnly = 0;
        }
        $originalName = $variableToSave->getOldName();
        if ($originalName != null) {
            $originalName = $database->escapeString(htmlspecialchars($originalName));
        } else {
            $originalName = $variableName;
        }
        if (!$database->updateTable('variable', "variableName='{$variableName}', variableValue='{$variableValue}', readOnly={$isReadOnly}", "variableName='{$originalName}'")) {
            return false;
        }
        return true;
    }
    public function addVariable(Variable $variableToAdd) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $variableName = $database->escapeString(htmlspecialchars($variableToAdd->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToAdd->getValue()));
        $isReadOnly = $variableToAdd->isReadOnly();
        if ($isReadOnly === true) {
            $isReadOnly = 1;
        } else {
            $isReadOnly = 0;
        }
        if (!$database->insertData('variable', 'variableName, variableValue, readOnly', "'{$variableName}', '{$variableValue}', {$isReadOnly}")) {
            return false;
        }
        return true;
    }
    public function deleteVariable(Variable $variableToDelete) {
        if ($variableToDelete->isReadOnly()) {
            return false;
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $variableName = $database->escapeString(htmlspecialchars($variableToDelete->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToDelete->getValue()));
        if (!$database->removeData('variable', "variableName = '{$variableName}' AND variableValue = '{$variableValue}'")) {
            return false;
        }
        return true;
    }
}