<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 1/4/14
 * Time: 6:33 PM
 */
class Link {
    private $cleanURLEnabled;
    private $physicalFile;
    private $href;
    private $localLinkOnly;
    private $fullLocalURL;

    public function __construct($inHref, $isPhysicalFile = false, $forceCleanURLS = false, $localLinkOnly = false, $fullLocalURL = false) {
        if(! is_bool($isPhysicalFile)) {
            return;
        }
        if(! is_bool($forceCleanURLS)) {
            return;
        }
        if(! is_bool($localLinkOnly)) {
            return;
        }
        if(! is_bool($fullLocalURL)) {
            return;
        }
        if (strlen($inHref) > 0) {
            if ($inHref[0] === '/') {
                $inHref = substr($inHref, 1);
            }
        }
        $inHref = strip_tags($inHref);
        $this->href = $inHref;
        $this->physicalFile = $isPhysicalFile;
        $this->fullLocalURL = $fullLocalURL;
        if ($forceCleanURLS) {
            $this->cleanURLEnabled = true;
            return;
        }
        $site = Site::getInstance();
        $this->cleanURLEnabled = $site->areCleanURLsEnabled();
    }
    //return a href based off of the string input when object created.
    public function getHref() {
        if(! $this->isLocalLink()) {
            return $this->externalLink();
        }
        if($this->fullLocalURL) {
            return $this->fullLocalURL();
        }
        if($this->physicalFile) {
            return EDUCASK_WEB_ROOT . $this->href;
        }
        if ($this->cleanURLEnabled === false) {
            return EDUCASK_WEB_ROOT . '?p=' . $this->href;
        }
        return EDUCASK_WEB_ROOT . $this->href;
    }
    private function externalLink() {
        if($this->localLinkOnly) {
            return EDUCASK_WEB_ROOT . '';
        }
        return $this->href;
    }
    private function fullLocalURL() {
        $site = Site::getInstance();
        $base = $site->getWebAddress(true, true);
        if ($this->cleanURLEnabled === false) {
            return $base . '?p=' . $this->href;
        }
        return $base . $this->href;
    }
    public function getRawHref() {
        return $this->href;
    }
    public function togglePhysicalFile($isPhysicalFile = false) {
        if(! is_bool($isPhysicalFile)) {
            return;
        }
        $this->physicalFile = $isPhysicalFile;
    }
    public function isPhysicalFile() {
        return $this->physicalFile;
    }
    public function isLocalLinkOnly() {
        return $this->localLinkOnly;
    }
    public function isLocalLink() {
        if (substr($this->href, 0, 4) === "http") {
            return false;
        }
        if (substr($this->href, 0, 2) === "//") {
            return false;
        }
        return true;
    }
    public function __toString() {
        return '' . $this->getHref();
    }
}