<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 5:49 PM
 */
class fileSystemEngine {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new fileSystemEngine();
        }
        return self::$instance;
    }

    public function getFileByID($inID) {
        if (!is_numeric($inID)) {
            return false;
        }
        $db = database::getInstance();
        $results = $db->getData('*', 'file', 'fileID = \'' . $inID . '\'');
        if (!$results) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }

        return new file($results[0]['fileID'], $results[0]['uploaded'], $results[0]['title'], $results[0]['mimeType'], $results[0]['size'], $results[0]['location'], $results[0]['nodeiD'], $results[0]['uploader'], $results[0]['folderID']);
    }

    public function getFilesByUploaderID($id) {

        if (!is_numeric($id)) {
            return false;
        }
        if (!permissionEngine::getInstance()->getPermission('userCanGetFiles')->canDo()) {
            return false;
        }
        $db = database::getInstance();
        $results = $db->getData('*', 'file', 'uploaderID = \'' . $id . '\'');
        $files = array();
        foreach ($results as $row) {
            $files[] = new file($row['fileID'], $row['uploaded'], $row['title'], $row['mimeType'], $row['size'], $row['location'], $row['nodeID'], $row['uploader'], $row['folderID']);
        }
        return $files;

    }

    public function getFolder($folderID) {
        if (!is_numeric($folderID)) {
            return false;
        }

        if (strlen($folderID) > 11) {
            return false;
        }

        $db = database::getInstance();
        $results = $db->getData('*', 'folder', 'folderID = \'' . $folderID . '\'');

        if (count($results) > 1) {
            return false;
        }

        $folder = new folder($results[0]['folderID'], $results[0]['title'], $results[0]['created'], $results[0]['ownerID'], $results[0]['parentFolder'], '');

    }

    public function setFile() {

    }

    public function setFolder() {

    }

    public function uploadFile(file $inFile) {

    }

    public function createFolder() {

    }

    public function deleteFile() {

    }

    public function deleteFolder() {

    }

    public function shareFile() {

    }

    public function shareFolder() {

    }
} 