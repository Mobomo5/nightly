<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:03 PM
 */

/**
 * Class sidebar
 *
 * content will return an array in the shape of $content[] = ('href'=>'alksjdal', 'title'=>'aosfas')
 */
class sidebar implements module {

    private $forceFourOhFour = false;
    private $content;

    public function __construct() {

    }

    public static function getPageType() {
        return 'block';
    }

    public function getPageContent() {
        return $this->content;
    }

    public function getTitle() {
        // TODO: Implement getTitle() method.
    }

    public function noGUI() {
        return false;
    }

    public function getReturnPage() {
        // TODO: Implement getReturnPage() method.
    }

    public function forceFourOhFour() {
        return $this->forceFourOhFour;
    }
}