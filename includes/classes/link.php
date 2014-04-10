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
    private $href;

    public function __construct($inHref, $forceCleanURLS = false) {
        if (strlen($inHref) > 0) {
            if ($inHref[0] == '/') {
                $inHref = substr($inHref, 1);
            }
        }

        $this->href = $inHref;
        if($forceCleanURLS) {
            $this->cleanURLEnabled = true;
            return;
        }
        $site = site::getInstance();
        $this->cleanURLEnabled = $site->areCleanURLsEnabled();
    }

    //return a href based off of the string input when object created.
    public function getHref() {
        if (substr($this->href, 0, 4) == "http") {
            return $this->href;
        }
        if (! $this->cleanURLEnabled) {
            return EDUCASK_WEB_ROOT . '?p=' .$this->href;
        }

        return EDUCASK_WEB_ROOT . $this->href;
    }

    public function __toString()
    {
        return '' . $this->getHref();
    }
}