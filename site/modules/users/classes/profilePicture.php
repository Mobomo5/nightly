<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 11/08/2015
 * Time: 8:10 PM
 */
class profilePicture {
    public static function uploadProfilePicture(Link $default) {
        if(empty($_FILES)) {
            return $default;
        }
        $tempFile = $_FILES['profilePictureUpload'];
        if(! file_exists($tempFile['tmp_name'])) {
            return $default;
        }
        if(! is_uploaded_file($tempFile['tmp_name'])) {
            return $default;
        }
        if (!isset($tempFile['error']) || is_array($tempFile['error'])) {
            return $default;
        }
        $maxFileUploadSize = (int)VariableEngine::getInstance()->getVariable("maxFileUploadSize")->getValue();
        if ($tempFile['size'] > $maxFileUploadSize) {
            return $default;
        }
        $profilePicturesLocation = EDUCASK_ROOT . '/public_html/images/profilePictures/';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $extension = array_search($finfo->file($tempFile['tmp_name']),array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif',),true);
        if($extension === false) {
            return $default;
        }
        do {
            $guid = uniqid() . uniqid("-");
            $fileLocation = $profilePicturesLocation . $guid . '.' . $extension;
        } while (file_exists($fileLocation));
        if(! move_uploaded_file($tempFile['tmp_name'], $fileLocation)) {
            return $default;
        }
        return new Link("images/profilePictures/{$guid}.{$extension}", true);
    }
}