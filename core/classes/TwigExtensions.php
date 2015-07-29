<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 19/7/2015
 * Time: 3:55 PM
 */
class TwigExtensions extends Twig_Extension {
    public function getFunctions() {
        return array(
            new Twig_SimpleFunction('AntiForgeryToken', array($this, 'AntiForgeryToken'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('Honeypot', array($this, 'Honeypot'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('Href', array($this, 'Href')),
            new Twig_SimpleFunction('HrefIfHasPermission', array($this, 'HrefIfHasPermission')),
            new Twig_SimpleFunction('Link', array($this, 'Link'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('LinkIfHasPermission', array($this, 'LinkIfHasPermission'), array('is_safe' => array('html'))),
        );
    }
    public function AntiForgeryToken() {
        return AntiForgeryToken::getInstance();
    }
    public function Honeypot() {
        return Honeypot::getInstance();
    }
    public function Href($inHref, $isPhysicalFile = false, $forceCleanURLS = false, $localLinkOnly = false, $fullLocalURL = false) {
        return new Link($inHref, $isPhysicalFile, $forceCleanURLS, $localLinkOnly, $fullLocalURL);
    }
    public function HrefIfHasPermission($permissionName, $inHref, $isPhysicalFile = false, $forceCleanURLS = false, $localLinkOnly = false, $fullLocalURL = false) {
        if(! is_string($permissionName)) {
            return "";
        }
        $permissionEngine = PermissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo($permissionName)) {
            return "";
        }
        return $this->Href($inHref, $isPhysicalFile, $forceCleanURLS, $localLinkOnly, $fullLocalURL);
    }
    public function Link($text, $inHref, array $attributes = array()) {
        if(! is_string($text)) {
            return;
        }
        $text = htmlspecialchars(trim($text));
        $link = new Link($inHref, false, false, true);
        $attributeString = "";
        foreach($attributes as $attributeName => $attributeValue) {
            if(! is_string($attributeName)) {
                continue;
            }
            if(! is_string($attributeValue)) {
                continue;
            }
            if($attributeName === "href") {
                continue;
            }
            $attributeName = str_replace("=", "", $attributeName);
            $attributeName = str_replace("'", "", $attributeName);
            $attributeName = str_replace('"', "", $attributeName);
            $attributeName = str_replace(' ', "", $attributeName);
            $attributeName = htmlspecialchars($attributeName);
            $attributeValue = str_replace("=", "", $attributeValue);
            $attributeValue = str_replace("'", "", $attributeValue);
            $attributeValue = str_replace('"', "", $attributeValue);
            $attributeValue = str_replace(' ', "", $attributeValue);
            $attributeValue = htmlspecialchars($attributeValue);
            $attributeString .= " {$attributeName}='{$attributeValue}'";
        }
        return "<a href='{$link}'{$attributeString}>{$text}</a>";
    }
    public function LinkIfHasPermission($permissionName, $text, $inHref, array $attributes = array()) {
        if(! is_string($permissionName)) {
            return "";
        }
        $permissionEngine = PermissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo($permissionName)) {
            return "";
        }
        return $this->Link($text, $inHref, $attributes);
    }
    public function getName() {
        return 'EducaskCoreTwigExtensions';
    }
}