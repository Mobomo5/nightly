<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 14/11/2015
 * Time: 7:04 PM
 */
interface ITimelineObject {
    public function getSubView();
    public function getDate();
    public function getPriority();
    public function compareTo(ITimelineObject $other);
}