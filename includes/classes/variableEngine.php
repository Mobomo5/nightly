<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 1/1/14
 * Time: 5:51 PM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
class variableEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new variableEngine();
        }
        return self::$instance;
    }
    public function getVariable($variableName) {
        if ($variableName == '') {
            return null;
        }
        if ($variableName == null) {
            return null;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return null;
        }
        $variableName = $database->escapeString(htmlspecialchars($variableName));
        $variableValue = $database->getData('variableValue, readOnly', 'variable', 'WHERE variableName=\'' . $variableName . '\'');
        if ($variableValue == false) {
            return null;
        }
        if (count($variableValue) > 1) {
            return null;
        }
        if ($variableValue == 1) {
            $toReturn = new variable($variableName, $variableValue, true);
            return $toReturn;
        }
        $toReturn = new variable($variableName, $variableValue);
        return $toReturn;
    }
    public function getVariables(array $variables = array()) {
        if (count($variables) == 0) {
            return null;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
            return null;
        }
        $where = '';
        foreach ($variables as $variable) {
            if ($variable == null) {
                continue;
            }
            if ($variable == '') {
                continue;
            }
            $variable = $database->escapeString(htmlspecialchars($variable));
            if ($where == '') {
                $where .= 'variableName = \'' . $variable . '\'';
            }
            $where .= ' OR variableName = \'' . $variable . '\'';
        }
        if ($where == '') {
            return null;
        }
        $results = $database->getData('variableName, variableValue, readOnly', 'variable', $where);
        if ($results == false) {
            return null;
        }
        $toReturn = array();
        foreach ($results as $result) {
            $variableName = $result['variableName'];
            $variableValue = $result['variableValue'];
            $readOnly = $result['readOnly'];
            if ($readOnly == 1) {
                $variable = new variable($variableName, $variableValue, true);
                $toReturn[$variableName] = $variable;
                continue;
            }
            $variable = new variable($variableName, $variableValue);
            $toReturn[$variableName] = $variable;
        }
        return $toReturn;
    }
    public function saveVariable(variable $variableToSave) {
        $database = database::getInstance();
        $variableName = $database->escapeString(htmlspecialchars($variableToSave->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToSave->getValue()));
        $isReadOnly = $database->escapeString(htmlspecialchars($variableToSave->isReadOnly()));
        if($isReadOnly == true) {
            $isReadOnly = 1;
        } else {
            $isReadOnly = 0;
        }
        if(! $database->updateTable('variable', 'variableName, variableValue, readOnly', "{$variableName}, {$variableValue}, {$isReadOnly}")) {
            return false;
        }
        return true;
    }
    public function addVariable(variable $variableToAdd) {
        $database = database::getInstance();
        $variableName = $database->escapeString(htmlspecialchars($variableToAdd->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToAdd->getValue()));
        $isReadOnly = $variableToAdd->isReadOnly();
        if($isReadOnly == true) {
            $isReadOnly = 1;
        } else {
            $isReadOnly = 0;
        }
        if(! $database->insertData('variable', 'variableName, variableValue, readOnly', "{$variableName}, {$variableValue}, {$isReadOnly}")) {
            return false;
        }
        return true;
    }
    public function deleteVariable(variable $variableToDelete) {
        if(! $variableToDelete->isReadOnly()) {
            return false;
        }
        $database = database::getInstance();
        $variableName = $database->escapeString(htmlspecialchars($variableToDelete->getName()));
        $variableValue = $database->escapeString(htmlspecialchars($variableToDelete->getValue()));
        $database->removeData('variable', "variableName = {$variableName} AND variableValue = {$variableValue}");
    }
}