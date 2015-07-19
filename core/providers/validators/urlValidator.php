<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 18/12/13
 * Time: 8:16 PM
 */
class urlValidator implements IValidator {
    public function validate($inValue, array $inOptions = array()) {
        $options = array();
        foreach ($inOptions as $option) {
            $options[$option] = $this->$option($inValue);
        }
        if (isset($options['noDirectories'])) {
            if ($options['noDirectories'] === false) {
                return false;
            }
        }
        if (isset($options['httpOrHttpsOnly'])) {
            if ($options['httpOrHttpsOnly'] === false) {
                return false;
            }
        }
        if (isset($options['httpOnly'])) {
            if ($options['httpOnly'] === false) {
                return false;
            }
        }
        if (isset($options['httpsOnly'])) {
            if ($options['httpsOnly'] === false) {
                return false;
            }
        }
        if (isset($options['mightBeIP'])) {
            if ($options['mightBeIP'] === true) {
                return true;
            }
        }
        return filter_var($inValue, FILTER_VALIDATE_URL);
    }
    private function mightBeIP($inValue) {
        $validator = new ipValidator();
        $parsed = parse_url($inValue);
        return $validator->validate($parsed['host']);
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
        if ($parsed['scheme'] !== 'http' || $parsed['scheme'] !== 'https') {
            return false;
        }
        return true;
    }
    private function httpOnly($inValue) {
        $parsed = parse_url($inValue);
        if (empty($parsed['scheme'])) {
            return false;
        }
        if ($parsed['scheme'] !== 'http') {
            return false;
        }
        return true;
    }
    private function httpsOnly($inValue) {
        $parsed = parse_url($inValue);
        if (empty($parsed['scheme'])) {
            return false;
        }
        if ($parsed['scheme'] !== 'https') {
            return false;
        }
        return true;
    }
}