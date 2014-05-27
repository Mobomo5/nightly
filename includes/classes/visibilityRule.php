<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 26/05/14
 * Time: 7:18 PM
 */
//Possible template for the future. Do not use.
class visibilityRule {
    private $ruleID;
    private $referenceID;
    private $referenceType;
    private $visible;
    private $ruleTypeID;
    private $ruleType;
    public function __construct($inRuleID, $inReferenceID, $inReferenceType, $inVisible, $inRuleTypeID, $inRuleType) {
        if(! is_integer($inRuleID)) {
            return;
        }
        if($inRuleID < 1){
            return;
        }
        if(! is_bool($inVisible)) {
            return;
        }
        if(! is_integer($inRuleTypeID)) {
            return;
        }
        if($inRuleTypeID < 1){
            return;
        }
        $inReferenceID = preg_replace('/\s+/', '', $inReferenceID);
        $inReferenceType = preg_replace('/\s+/', '', $inReferenceType);
        $inRuleType = preg_replace('/\s+/', '', $inRuleType);
        $this->ruleID = $inRuleID;
        $this->referenceID = $inReferenceID;
        $this->referenceType = $inReferenceType;
        $this->visible = $inVisible;
        $this->ruleTypeID = $inRuleTypeID;
        $this->ruleType = $inRuleType;
    }
    public function getID() {
        return $this->ruleID;
    }
    public function getReferenceID() {
        return $this->referenceID;
    }
    public function getReferenceType() {
        return $this->referenceType;
    }
    public function isVisible() {
        return $this->visible;
    }
    public function setVisible($inIsVisible) {
        if(! is_bool($inIsVisible)) {
            return;
        }
        $this->visible = $inIsVisible;
    }
    public function getRuleTypeID() {
        return $this->ruleTypeID;
    }
    public function getRuleType() {
        return $this->ruleType;
    }
}