<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 15/06/14
 * Time: 7:24 PM
 */
class urlAlias {
    private $id;
    private $source;
    private $alias;
    public function __construct($inID, $inSource, $inAlias) {
        if(! is_numeric($inID)) {
            return;
        }
        $this->id = $inID;
        $this->source = preg_replace('/\s+/', '', $inSource);
        $this->alias = preg_replace('/\s+/', '', $inAlias);
    }
    public function getID() {
        return $this->id;
    }
    public function getSource() {
        return $this->source;
    }
    public function setSource($inSource) {
        $this->source = preg_replace('/\s+/', '', $inSource);
    }
    public function getAlias() {
        return $this->alias;
    }
    public function setAlias($inAlias) {
        $this->alias = preg_replace('/\s+/', '', $inAlias);
    }
}