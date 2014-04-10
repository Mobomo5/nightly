<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:32 PM
 */
class general {
    private $function;
    public function __construct($inFunction) {
        $functionFile = EDUCASK_ROOT . '/includes/generalFunctions/' . $inFunction . '.php';
        if(! is_file($functionFile)) {
            $this->function = false;
            return;
        }
        require_once($functionFile);
        $this->function = new $inFunction();
    }
    public function run(array $inOptions = array()) {
        if(! $this->function) {
            return false;
        }
        if($this->function->hasOptions()) {
            return $this->function->run($inOptions);
        }
        return $this->function->run();
    }
    public function validatorExists() {
        if(! $this->function) {
            return false;
        }
        return true;
    }
}