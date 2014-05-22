<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 3:51 PM
 */
interface subValidator {
    public function validate($inValue);

    public function hasOptions();
}