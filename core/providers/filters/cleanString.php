<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:44 PM
 */
class cleanString implements IFilter {
    public function run($inValue) {
        if(! is_string($inValue)) {
            return "";
        }
        return preg_replace('/[^A-Za-z0-9\-\&\/\.\_]/', '', htmlspecialchars($inValue));
    }
}