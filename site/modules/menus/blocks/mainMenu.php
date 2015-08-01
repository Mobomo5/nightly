<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 30/07/2015
 * Time: 9:06 PM
 */
class mainMenu implements IBlock {
    private $title;
    public function __construct($inBlockID){
        $this->title = "Main Menu";
    }
    public function getTitle() {
        return $this->title;
    }
    public function setTitle($inTitle) {
        $this->title = $inTitle;
    }
    public function getContent() {
        $menuEngine = MenuEngine::getInstance();
        $menu = $menuEngine->getMenuByName("mainMenu");
        if(! $menu) {
            return "";
        }
        return $menu->getHTML();
    }
    public function getType() {
        return "menu";
    }
}