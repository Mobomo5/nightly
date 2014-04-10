<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 5:20 PM
 */

class bootstrap {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bootstrap();
        }

        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function init() {
        $this->declareConstants();
        $this->doRequires();
        $this->connectDatabase();
        $this->initializePlugins();
        $this->getVariables();
        $this->render();
    }
    private function declareConstants() {
        define('VARIABLE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/variable.php');
        define('VARIABLE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/variableEngine.php');
    }
    private function doRequires() {

    }
    private function connectDatabase() {

    }
    private function initializePlugins() {

    }
    private function getVariables() {

    }
    private function render() {

    }
}