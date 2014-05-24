<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/8/14
 * Time: 4:05 PM
 */
interface plugin {
    public static function init();
    public static function run($inContent = '');
    public static function getPriority();
}