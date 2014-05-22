<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 8:16 PM
 */
require_once(VALIDATOR_INTERFACE_FILE);
require_once(VALIDATOR_OBJECT_FILE);

class url implements subValidator {
    public function validate($inValue, array $inOptions = array()) {
        $options = array();
        foreach ($inOptions as $option) {
            $options[$option] = $this->$option($inValue);
        }
        if (!empty($options['noDirectories'])) {
            if (!$options['noDirectories']) {
                return false;
            }
        }
        if (!empty($options['httpOrHttpsOnly'])) {
            if (!$options['httpOrHttpsOnly']) {
                return false;
            }
        }
        if (!empty($options['httpOnly'])) {
            if (!$options['httpOnly']) {
                return false;
            }
        }
        if (!empty($options['httpsOnly'])) {
            if (!$options['httpsOnly']) {
                return false;
            }
        }
        if (!empty($options['mightBeIP'])) {
            if ($options['mightBeIP'] == true) {
                return true;
            }
        }
        return filter_var($inValue, FILTER_VALIDATE_URL);
    }

    public function hasOptions() {
        return true;
    }

    private function mightBeIP($inValue) {
        $validator = new validator('ip');
        if (!$validator->validatorExists()) {
            return false;
        }
        return $validator->validate($inValue);
    }

    private function noDirectories($inValue) {
        $parsed = parse_url($inValue);
        if (!empty($parsed['path'])) {
            return false;
        }
        return true;
    }

    private function httpOrHttpsOnly($inValue) {
        $parsed = parse_url($inValue);
        if (empty($parsed['scheme'])) {
            return false;
        }
        if ($parsed['scheme'] != 'http' || $parsed['scheme'] != 'https') {
            return false;
        }
        return true;
    }

    private function httpOnly($inValue) {
        $parsed = parse_url($inValue);
        if (empty($parsed['scheme'])) {
            return false;
        }
        if ($parsed['scheme'] != 'http') {
            return false;
        }
        return true;
    }

    private function httpsOnly($inValue) {
        $parsed = parse_url($inValue);
        if (empty($parsed['scheme'])) {
            return false;
        }
        if ($parsed['scheme'] != 'https') {
            return false;
        }
        return true;
    }
}