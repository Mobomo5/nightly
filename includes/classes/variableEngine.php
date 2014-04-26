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
        $variableName = $database->escapeString($variableName);
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
        $where = '';
        foreach ($variables as $variable) {
            if ($variable == null) {
                continue;
            }
            if ($variable == '') {
                continue;
            }
            if ($where == '') {
                $where .= 'variableName = \'' . $variable . '\'';
            }
            $where .= ' OR variableName = \'' . $variable . '\'';
        }
        if ($where == '') {
            return null;
        }
        $database = database::getInstance();
        if (!$database->isConnected()) {
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
}