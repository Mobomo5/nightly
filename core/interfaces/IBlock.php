<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 8:01 PM
 */
interface IBlock {
    public function __construct($inBlockID);
    public function getTitle();
    public function setTitle($inTitle);
    public function getContent();
    public function getType();
}