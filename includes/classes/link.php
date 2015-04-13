<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 1/4/14
 * Time: 6:33 PM
 */
require_once(SITE_OBJECT_FILE);

class link {
    private $cleanURLEnabled;
    private $physicalFile;
    private $href;
    private $localLinkOnly;

    public function __construct($inHref, $isPhysicalFile = false, $forceCleanURLS = false, $localLinkOnly = false) {
        if(! is_bool($isPhysicalFile)) {
            return;
        }
        if(! is_bool($forceCleanURLS)) {
            return;
        }
        if(! is_bool($localLinkOnly)) {
            return;
        }
        if (strlen($inHref) > 0) {
            if ($inHref[0] == '/') {
                $inHref = substr($inHref, 1);
            }
        }
        $inHref = strip_tags($inHref);
        $this->href = $inHref;
        $this->physicalFile = $isPhysicalFile;
        if ($forceCleanURLS) {
            $this->cleanURLEnabled = true;
            return;
        }
        $site = site::getInstance();
        $this->cleanURLEnabled = $site->areCleanURLsEnabled();
    }
    //return a href based off of the string input when object created.
    public function getHref() {
        if (substr($this->href, 0, 4) == "http") {
            return $this->externalLink();
        }
        if($this->physicalFile) {
            return EDUCASK_WEB_ROOT . '/' . $this->href;
        }
        if ($this->cleanURLEnabled == false) {
            return EDUCASK_WEB_ROOT . '/?p=' . $this->href;
        }
        return EDUCASK_WEB_ROOT . '/' . $this->href;
    }
    private function externalLink() {
        if($this->localLinkOnly) {
            return EDUCASK_WEB_ROOT . '/home';
        }
        return $this->href;
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
    public function __toString() {
        return '' . $this->getHref();
    }
}