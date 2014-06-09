<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:01 PM
 */
require_once(VARIABLE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);

class site {
    private $title;
    private $email;
    private $url;
    private $urlSecure;
    private $baseDirectory;
    private $theme;
    private $educaskVersion;
    private $guestRoleID;
    private $cleanURLs;
    private $timeZone;
    private $maintenanceMode;
    //@TODO: Add Cron Stuff
    //@TODO: Add logo and favicon.

    public static function getInstance() {
        if (!isset($_SESSION['educaskSite'])) {
            self::setInstance(new site());
        }

        return $_SESSION['educaskSite'];
    }

    private static function setInstance(site $object) {
        //verify the variable given is a site object. If it is not, get out of here.
        if (get_class($object) != 'site') {
            return;
        }
        $_SESSION['educaskSite'] = $object;
    }

    private function __construct() {
        $variableEngine = variableEngine::getInstance();
        $variablesWanted[] = 'siteTitle';
        $variablesWanted[] = 'siteEmail';
        $variablesWanted[] = 'siteTheme';
        $variablesWanted[] = 'siteWebAddress';
        $variablesWanted[] = 'siteWebAddressSecure';
        $variablesWanted[] = 'siteWebDirectory';
        $variablesWanted[] = 'educaskVersion';
        $variablesWanted[] = 'guestRoleID';
        $variablesWanted[] = 'cleanURLsEnabled';
        $variablesWanted[] = 'siteTimeZone';
        $variablesWanted[] = 'maintenanceMode';
        //@TODO: Add Cron Stuff
        $variables = $variableEngine->getVariables($variablesWanted);
        $this->title = $variables['siteTitle'];
        $this->email = $variables['siteEmail'];
        $this->url = $variables['siteWebAddress'];
        $this->urlSecure = $variables['siteWebAddressSecure'];
        $this->baseDirectory = $variables['siteWebDirectory'];
        $this->theme = $variables['siteTheme'];
        $this->educaskVersion = $variables['educaskVersion'];
        $this->guestRoleID = $variables['guestRoleID'];
        $this->cleanURLs = $variables['cleanURLsEnabled'];
        $this->timeZone = $variables['siteTimeZone'];
        $this->maintenanceMode = $variables['maintenanceMode'];
        //@TODO: Add Cron Stuff
    }

    public function getTitle() {
        return $this->title;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setTitle($inTitle) {
        if (!$this->title->setValue($inTitle)) {
            return false;
        }
        $this->title->save();
        self::setInstance($this);
    }

    public function setEmail($inEmail) {
        $validator = new validator('email');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($inEmail)) {
            return false;
        }
        if (!$this->email->setValue($inEmail)) {
            return false;
        }
        $this->email->save();
        self::setInstance($this);
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme($inTheme) {
        $inTheme = str_replace('..', '', $inTheme);
        $tempName = '/includes/themes/' . $inTheme;
        $validator = new validator('dir');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($tempName)) {
            return false;
        }
        if (!$this->theme->setValue($inTheme)) {
            return false;
        }
        $this->theme->save();
        self::setInstance($this);
    }

    public function getWebAddress($secure = false, $withBaseDirectory = false) {
        if ($secure == true) {
            if ($withBaseDirectory == true) {
                return $this->urlSecure . $this->baseDirectory;
            }
            return $this->urlSecure;
        }
        if ($withBaseDirectory == true) {
            return $this->url . $this->baseDirectory;
        }
        return $this->url;
    }

    public function setWebAddress($inUrl) {
        $validator = new validator('url');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpOnly'))) {
            return false;
        }
        if (!$this->url->setValue($inUrl)) {
            return false;
        }
        $this->url->save();
        self::setInstance($this);
    }

    public function setSecureWebAddress($inUrl) {
        $validator = new validator('url');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpsOnly'))) {
            return false;
        }
        if (!$this->urlSecure->setValue($inUrl)) {
            return false;
        }
        $this->urlSecure->save();
        self::setInstance($this);
    }

    public function getBaseDirectory() {
        return $this->baseDirectory;
    }

    public function setBaseDirectory($inDirectory) {
        $inDirectory = str_replace('..', '', $inDirectory);
        $validator = new validator('dir');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($inDirectory)) {
            return false;
        }
        if (!$this->baseDirectory->setValue($inDirectory)) {
            return false;
        }
        $this->baseDirectory->save();
        self::setInstance($this);
    }

    public function getEducaskVersion() {
        return $this->educaskVersion;
    }

    public function getGuestRoleID() {
        return $this->guestRoleID;
    }

    public function setGuestRoleID($inID) {
        if (!is_int($inID)) {
            return false;
        }
        if (!$this->guestRoleID->setValue($inID)) {
            return false;
        }
        $this->guestRoleID->save();
        self::setInstance($this);
    }

    public function areCleanURLsEnabled() {
        return $this->cleanURLs;
    }

    public function setCleanURLs($areEnabled = true) {
        if(! is_bool($areEnabled)) {
            return false;
        }
        if ($areEnabled == false) {
            if (!$this->cleanURLs->setValue('false')) {
                return false;
            }
            $this->cleanURLs->save();
            self::setInstance($this);
            return;
        }
        if (!$this->cleanURLs->setValue('true')) {
            return false;
        }
        $this->cleanURLs->save();
        self::setInstance($this);
    }

    public function getTimeZone() {
        return $this->timeZone;
    }

    public function setTimeZone($inTimeZone) {
        $validator = new validator('phpTimeZone');
        if (!$validator->validatorExists()) {
            return false;
        }
        if (!$validator->validate($inTimeZone)) {
            return false;
        }
        if (!$this->timeZone->setValue($inTimeZone)) {
            return false;
        }
        $this->timeZone->save();
        self::setInstance($this);
    }
    public function isInMaintenanceMode() {
        if($this->maintenanceMode->getValue() != 'true') {
            return false;
        }
        return true;
    }
    public function setMaintenanceMode($maintenanceMode = false) {
        if(! is_bool($maintenanceMode)) {
            return false;
        }
        if($maintenanceMode == false) {
            if (!$this->timeZone->setValue('false')) {
                return false;
            }
            $this->maintenanceMode->save();
            self::setInstance($this);
            return true;
        }
        if (!$this->timeZone->setValue('true')) {
            return false;
        }
        $this->maintenanceMode->save();
        self::setInstance($this);
        return true;
    }
    //@TODO: Add Cron Stuff
}