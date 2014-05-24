<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 5:49 PM
 */

require_once(FOLDER_OBJECT_FILE);
require_once(FILE_OBJECT_FILE);

class fileSystemEngine {

    private static $instance;

    /**
     * @return fileSystemEngine
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new fileSystemEngine();
        }
        return self::$instance;
    }

    /**
     * @param $inID
     *
     * @return bool|file
     */
    public function getFileByID($inID) {
        if (!permissionEngine::getInstance()->getPermission('userCanAccessFileSystem')->canDo()) {
            return false;
        }

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

        return new file($results[0]['fileID'], $results[0]['uploaded'], $results[0]['title'], $results[0]['mimeType'], $results[0]['size'], $results[0]['location'], $results[0]['nodeID'], $results[0]['uploader'], $results[0]['folderID']);
    }

    /**
     * @param $id
     *
     * @return array|bool
     */
    public function getFilesByUploaderID($id) {
        if (!permissionEngine::getInstance()->getPermission('userCanAccessFileSystem')->canDo()) {
            return false;
        }

        if (!is_numeric($id)) {
            return false;
        }
        if (!permissionEngine::getInstance()->getPermission('userCanGetFiles')->canDo()) {
            return false;
        }
        $db = database::getInstance();
        $results = $db->getData('*', 'file', 'uploaderID = \'' . $id . '\'');
        $files = array();
        if ($results) {
            foreach ($results as $row) {
                $files[] = new file($row['fileID'], $row['uploaded'], $row['title'], $row['mimeType'], $row['size'], $row['location'], $row['nodeID'], $row['uploader'], $row['folderID']);
            }
        }
        return $files;

    }

    /**
     * @param $folderID
     *
     * @return bool|folder
     */
    public function getFolder($folderID) {
        if (!permissionEngine::getInstance()->getPermission('userCanAccessFileSystem')->canDo()) {
            return false;
        }

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
        $folder = new folder($results[0]['folderID'], $results[0]['title'], $results[0]['created'], $results[0]['ownerID'], $results[0]['parentFolder'], $this->getfolderChildren($folderID));

        return $folder;
    }

    /**
     * @param $folderID
     *
     * @return array
     */
    private function getFolderChildren($folderID) {
        $db = database::getInstance();
        $results = $db->getData('*', 'folder', 'parentFolder = \'' . $folderID . '\'');
        if (!$results) {
            return;
        }
        if (empty($results)) {
            return false;
        }

        $subFolders = array();
        foreach ($results as $row) {
            $subFolders[] = new folder($row['folderID'], $row['title'], $row['created'], $row['ownerID'], $row['parentFolder'], $this->getfolderChildren($row['folderID']));
        }
        return $subFolders;

    }

    /**
     *
     */
    public function setFile(file $inFile) {
        if (!permissionEngine::getInstance()->getPermission('userCanAlterFiles')->canDo()) {
            return false;
        }

        $db = database::getInstance();

        $title = $db->escapeString($inFile->getTitle());
        $mimeType = $db->escapeString($inFile->getMimeType());
        $size = $db->escapeString($inFile->getSize());
        $location = $db->escapeString($inFile->getLocation());
        $nodeID = $db->escapeString($inFile->getNodeID());
        $folderID = $db->escapeString($inFile->getFolderID());
        $fileID = $db->escapeString($inFile->getId());

        $results = $db->updateTable(
            'file',
            'title = \'' . $title . '\', mimeType = \'' . $mimeType . '\', size = \'' . $size .
            '\', location = \'' . $location . '\', nodeID = \'' . $nodeID . '\', folderID = \'' . $folderID . '\'',
            'fileID = \'' . $fileID . '\'');

        if (!$results) {
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function setFolder() {
        if (!permissionEngine::getInstance()->getPermission('userCanAlterFolders')->canDo()) {
            return false;
        }

    }

    /**
     * @param file $inFile
     */
    public function uploadFile(file $inFile) {
        if (!permissionEngine::getInstance()->getPermission('userCanUploadFiles')->canDo()) {
            return false;
        }

    }

    /**
     *  returns false on fail and the ID of the newly created folder on success
     */
    public function createFolder(folder $inFolder, $parentFolderID = 1) {
        if (!permissionEngine::getInstance()->getPermission('userCanAddFolders')->canDo()) {
            return false;
        }
        if (!is_numeric($parentFolderID)) {
            return false;
        }

        $db = database::getInstance();

        $title = $db->escapeString($inFolder->getTitle());
        $ownerID = $db->escapeString(currentUser::getUserSession()->getUserID());

        $results = $db->insertData('folder', 'title, ownerID, parentFolder', '\'' . $title . '\', \'' . $ownerID . '\', \'' . $parentFolderID . '\'');

        if (!$results) {
            return false;
        }

        $results = $db->getData('folderID', 'folder', 'title = \'' . $title . '\' AND ownerID = \'' . $ownerID . '\' AND parentFolder = \'' . $parentFolderID . '\'');
        return $results[0]['folderID'];
    }

    /**
     *
     */
    public function deleteFile(file $inFile) {
//        if (!permissionEngine::getInstance()->getPermission('userCanDeleteFiles')->canDo()){
//            return false;
//        }

        $db = database::getInstance();

        $id = $db->escapeString($inFile->getId());
        $title = $db->escapeString($inFile->getTitle());
        $uploader = $db->escapeString($inFile->getUploader());

        $results = $db->removeData('file', 'fileID = \'' . $id . '\' AND title = \'' . $title . '\' AND uploader = \'' . $uploader . '\'');
        if (!$results) {
            return false;
        }
        return true;

    }

    /**
     *
     */
    public function deleteFolder($folderID, $deleteSubDirectories = false) {

        $perm = permissionEngine::getInstance()->getPermission('userCanDeleteFolders');

        if (!$perm->canDo()) {
            return false;
        }
        if (!is_numeric($folderID)) {
            return false;
        }
        // don't delete master folder or non-folders
        if ($folderID < 1) {
            return false;
        }

        $db = database::getInstance();

        // if it's empty, get rid of it

        if ($this->folderIsEmpty($this->getFolder($folderID))) {
            return $db->removeData('folder', 'folderID = \'' . $folderID . '\'');
        }

        // if it's not empty and you don't want to delete subs, bail.
        if (!$this->folderIsEmpty($this->getFolder($folderID)) AND !$deleteSubDirectories) {
            return false;
        }

        // get the subs, if any
        $subDirectories = $db->getData('folderID', 'folder', 'parentFolder = \'' . $folderID . '\'');
        if ($subDirectories) {
            foreach ($subDirectories as $sub) {
                $this->deleteFolder($sub['folderID'], $deleteSubDirectories);
            }
        }

        // delete all files in the folder
        $results = $db->getData('fileID', 'file', 'folderID = \'' . $folderID . '\'');
        if ($results) {
            foreach ($results as $file) {
                $this->deleteFile($this->getFileByID($file['fileID']));
            }
        }

        $results = $db->removeData('folder', 'folderID = \'' . $folderID . '\'');
        if (!$results) {
            return false;
        }
        return true;

    }

    private function folderIsEmpty(folder $inFolder) {
        if (!$inFolder) {
            return false;
        }
        $db = database::getInstance();
        $subFolders = $db->getData('*', 'folder', 'parentFolder = \'' . $inFolder->getId() . '\'');
        $files = $db->getData('*', 'file', 'folderID = \'' . $inFolder->getId() . '\'');

        if ($subFolders OR $files) {
            return false;
        }
        return true;

    }

    /**
     *
     */
    public function shareFile() {

    }

    /**
     *
     */
    public function shareFolder() {

    }

    public function moveFolder(folder $inFolder, folder $toFolder) {
        if (!permissionEngine::getInstance()->getPermission('userCanMoveFolders')->canDo()) {
            return false;
        }
        // can't move the base filesystem
        if ($inFolder->getId() == 0) {
            return false;
        }
        $final = new folder($inFolder->getId(), $inFolder->getTitle(), $inFolder->getCreated(), $inFolder->getOwnerID(), $toFolder->getId(), $inFolder->getChildFilesAndFolders());
        return $this->setFolder($final);
    }

    public function moveFile(file $inFile, folder $toFolder) {
        if (!permissionEngine::getInstance()->getPermission('userCanMoveFiles')->canDo()) {
            return false;
        }
        $final = new file($inFile->getId(), $inFile->getUploaded(), $inFile->getTitle(), $inFile->getMimeType(),
            $inFile->getSize(), $inFile->getLocation(), $inFile->getNodeID(), $inFile->getUploader(), $toFolder->getId());
        return $this->setFile($final);
    }
} 