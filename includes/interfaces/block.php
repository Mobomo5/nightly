<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 8:01 PM
 */
interface block {
    public function __construct();

    public function getTitle();

    public function setTitle($inTitle);

    public function getContent();
}