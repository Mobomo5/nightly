<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 07/01/14
 * Time: 8:01 PM
 */
interface block {
    public function __construct($inParameters);
    public function getTitle();
    public function setTitle();
    public function getContent();
}