<?php
require_once(FOLDER_OBJECT_FILE);
require_once(FILE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);
class fileSystemEngine {
    private static $instance;
    private $foundFiles;
    private $foundFolders;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new fileSystemEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->foundFiles = array();
        $this->foundFolders = array();
    }
    public function getFile($inFileID) {
        if(! is_numeric($inFileID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('readFileSystem')) {
            return false;
        }
        if(isset($this->foundFiles[$inFileID])) {
            return $this->foundFiles[$inFileID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inFileID = $database->escapeString($inFileID);
        $results = $database->getData("*", "file", "fileID = {$inFileID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $dateUploaded = new DateTime($results[0]['uploaded']);
        $toReturn = new file($results[0]['fileID'], $dateUploaded, $results[0]['title'], $results[0]['mimeType'], $results[0]['size'], $results[0]['location'], $results[0]['nodeID'], $results[0]['uploader'], $results[0]['parentFolder']);
        $this->foundFiles[$inFileID] = $toReturn;
        return $toReturn;
    }
    public function getUploaderFiles($inUploaderID) {
        if(! is_numeric($inUploaderID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('readFileSystem')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inUploaderID = $database->escapeString($inUploaderID);
        $results = $database->getData("*", "file", "uploader={$inUploaderID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        $toReturn = array();
        foreach($results as $rawFileData) {
            if(isset($this->foundFiles[$rawFileData['fileID']])) {
                $toReturn[] = $this->foundFiles[$rawFileData['fileID']];
                continue;
            }
            $toAddDateUploaded = new DateTime($rawFileData['uploaded']);
            $toAdd = new file($rawFileData['fileID'], $toAddDateUploaded, $rawFileData['title'], $rawFileData['mimeType'], $rawFileData['size'], $rawFileData['location'], $rawFileData['nodeID'], $rawFileData['uploader'], $rawFileData['parentFolder']);
            $this->foundFiles[$rawFileData['fileID']] = $toAdd;
            $toReturn[] = $toAdd;
        }
        return $toReturn;
    }
    public function getFolder($inFolderID) {
        if(! is_numeric($inFolderID)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('readFileSystem')) {
            return false;
        }
        if(isset($this->foundFolders[$inFolderID])) {
            return $this->foundFolders[$inFolderID];
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $inFolderID = $database->escapeString($inFolderID);
        $results = $database->getData('*', 'folder', "folderID={$inFolderID}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $timeCreated = new DateTime($results[0]['created']);
        $childItems = array();
        $childFolders = $database->getData('folderID', 'folder', "parentFolder={$inFolderID}");
        if($childFolders == false) {
            return false;
        }
        foreach($childFolders as $childFolder) {
            $child = $this->getFolder($childFolder['folderID']);
            if($child == false) {
                continue;
            }
            $childItems[] = $child;
        }
        $childFiles = $database->getData('fileID', 'file', "folderID={$inFolderID}");
        if($childFiles == false) {
            return false;
        }
        foreach($childFiles as $childFile) {
            $child = $this->getFile($childFile['fileID']);
            if($child == false) {
                continue;
            }
            $childItems[] = $child;
        }
        $toReturn = new folder($results[0]['folderID'], $results[0]['title'], $timeCreated, $results[0]['ownerID'], $results[0]['parentFolder'], $childItems);
        $this->foundFolders[$toReturn->getID()] = $toReturn;
        return $toReturn;
    }
    public function addFile(file $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('uploadFile')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $validator = new validator("checkIfKnownMimeType");
        if(! $validator->validate($toAdd->getMimeType())) {
            return false;
        }
        if(! is_file($toAdd->getLocation())) {
            return false;
        }
        $dateUploaded = $database->escapeString($toAdd->getUploadedDate()->format('Y-m-d H:i:s'));
        $title = $database->escapeString(strip_tags($toAdd->getTitle()));
        $mimeType = $database->escapeString($toAdd->getMimeType());
        $size = $database->escapeString($toAdd->getSize());
        $location = $database->escapeString($toAdd->getLocation());
        $nodeID = $database->escapeString($toAdd->getNodeID());
        $uploader = $database->escapeString($toAdd->getUploaderID());
        $folder = $database->escapeString($toAdd->getFolderID());
        $result = $database->insertData('file', 'uploaded, title, mimeType, size, location, nodeID, uploader, folderID', "'{$dateUploaded}', '{$title}', '{$mimeType}', {$size}, '{$location}', {$nodeID}, {$uploader}, {$folder}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function addFolder(folder $toAdd) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('createFolder')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $title = $database->escapeString(strip_tags($toAdd->getTitle()));
        $dateCreated = $database->escapeString($toAdd->getDateCreated()->format('Y-m-d H:i:s'));
        $owner = $database->escapeString($toAdd->getOwnerID());
        $parent = $database->escapeString($toAdd->getParentFolderID());
        $result = $database->insertData('folder', 'title, created, ownerID, parentFolder', "'{$title}', '{$dateCreated}', {$owner}, {$parent}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function setFile(file $toSave) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('uploadFile')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $validator = new validator("checkIfKnownMimeType");
        if(! $validator->validate($toSave->getMimeType())) {
            return false;
        }
        if(! is_file($toSave->getLocation())) {
            return false;
        }
        $id = $database->escapeString($toSave->getID());
        $dateUploaded = $database->escapeString($toSave->getUploadedDate()->format('Y-m-d H:i:s'));
        $title = $database->escapeString(strip_tags($toSave->getTitle()));
        $mimeType = $database->escapeString($toSave->getMimeType());
        $size = $database->escapeString($toSave->getSize());
        $location = $database->escapeString($toSave->getLocation());
        $nodeID = $database->escapeString($toSave->getNodeID());
        $uploader = $database->escapeString($toSave->getUploaderID());
        $folder = $database->escapeString($toSave->getFolderID());
        $result = $database->updateTable('file', "uploaded='{$dateUploaded}', title='{$title}', mimeType='{$mimeType}', size={$size}, location='{$location}', nodeID={$nodeID}, uploader={$uploader}, folderID={$folder}", "fileID='{$id}'");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function setFolder(folder $toSave) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('createFolder')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toSave->getID());
        $title = $database->escapeString(strip_tags($toSave->getTitle()));
        $dateCreated = $database->escapeString($toSave->getDateCreated()->format('Y-m-d H:i:s'));
        $owner = $database->escapeString($toSave->getOwnerID());
        $parent = $database->escapeString($toSave->getParentFolderID());
        $result = $database->updateTable('folder', "title='{$title}', created='{$dateCreated}', ownerID={$owner}, parentFolder={$parent}", "folderID={$id}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function deleteFile(file $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('deleteFile')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toDelete->getID());
        $result = $database->removeData('fileSystemShare', "referenceID={$id} AND referenceType='file'");
        if($result == false) {
            return false;
        }
        $result = $database->removeData('file', "fileID={$id}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function deleteFolder(folder $toDelete) {
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('deleteFolder')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toDelete->getID());
        $childFiles = $toDelete->getChildFiles();
        foreach($childFiles as $childFile) {
            $deleted = $this->deleteFile($childFile);
            if($deleted == false) {
                return false;
            }
        }
        unset($childFiles);
        $childFolders = $toDelete->getChildFolders();
        foreach($childFolders as $childFolder) {
            $deleted = $this->deleteFolder($childFolder);
            if($deleted == false) {
                return false;
            }
        }
        unset($childFolders);
        $result = $database->removeData('fileSystemShare', "referenceID={$id} AND referenceType='folder'");
        if($result == false) {
            return false;
        }
        $result = $database->removeData('folder', "folderID={$id}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function shareFile(file $toShare, $userIDToShareTo, $shared = true) {
        if(! is_numeric($userIDToShareTo)) {
            return false;
        }
        if(! is_bool($shared)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('shareFile')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($shared == true) {
            $insertShare = 1;
        } else {
            $insertShare = 0;
        }
        $fileID = $database->escapeString($toShare->getID());
        $userIDToShareTo = $database->escapeString($userIDToShareTo);
        $insertShare = $database->escapeString($insertShare);
        $alreadyIn = $database->getData('shared', 'fileSystemShare', "referenceID={$fileID} AND referenceType='file' AND userID={$userIDToShareTo}");
        if($alreadyIn != null) {
            return $this->updateFileShare($fileID, $userIDToShareTo, $insertShare, $alreadyIn[0]['shared']);
        }
        $result = $database->insertData('fileSystemShare', 'referenceID, referenceType, shared, userID', "{$fileID}, 'file', {$insertShare}, {$userIDToShareTo}");
        if($result == false) {
            return false;
        }
        return true;
    }
    private function updateFileShare($fileID, $userID, $shared, $currentShared) {
        if((int)$currentShared == (int)$shared) {
            return true;
        }
        if(! is_numeric($fileID)) {
            return false;
        }
        if(! is_numeric($userID)) {
            return false;
        }
        if(($shared != 0) and ($shared != 1)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('shareFile')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->updateTable('fileSystemShare', "shared={$shared}", "referenceID={$fileID} AND referenceType='file' AND userID={$userID}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function shareFolder(folder $toShare, $userIDToShareTo, $shared = true) {
        if(! is_numeric($userIDToShareTo)) {
            return false;
        }
        if(! is_bool($shared)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('shareFolder')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        if($shared == true) {
            $insertShare = 1;
        } else {
            $insertShare = 0;
        }
        $folderID = $database->escapeString($toShare->getID());
        $userIDToShareTo = $database->escapeString($userIDToShareTo);
        $insertShare = $database->escapeString($insertShare);
        $alreadyIn = $database->getData('shared', 'fileSystemShare', "referenceID={$folderID} AND referenceType='folder' AND userID={$userIDToShareTo}");
        if($alreadyIn != null) {
            return $this->updateFolderShare($folderID, $userIDToShareTo, $insertShare, $alreadyIn[0]['shared']);
        }
        $result = $database->insertData('fileSystemShare', 'referenceID, referenceType, shared, userID', "{$folderID}, 'folder', {$insertShare}, {$userIDToShareTo}");
        if($result == false) {
            return false;
        }
        return true;
    }
    private function updateFolderShare($folderID, $userID, $shared, $currentShared) {
        if((int)$currentShared == (int)$shared) {
            return true;
        }
        if(! is_numeric($folderID)) {
            return false;
        }
        if(! is_numeric($userID)) {
            return false;
        }
        if(($shared != 0) and ($shared != 1)) {
            return false;
        }
        $permissionEngine = permissionEngine::getInstance();
        if(! $permissionEngine->currentUserCanDo('shareFolder')) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $result = $database->updateTable('fileSystemShare', "shared={$shared}", "referenceID={$folderID} AND referenceType='folder' AND userID={$userID}");
        if($result == false) {
            return false;
        }
        return true;
    }
    public function isFileSharedTo(file $toCheck, $userIDToCheck) {
        if(! is_numeric($userIDToCheck)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toCheck->getID());
        $results = $database->getData('shared', 'fileSystemShare', "referenceID={$id} AND referenceType='file' AND userID={$userIDToCheck}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $shared = (int) $results[0]['shared'];
        if($shared == 0) {
            return false;
        }
        return true;
    }
    public function isFolderSharedTo(folder $toCheck, $userIDToCheck) {
        if(! is_numeric($userIDToCheck)) {
            return false;
        }
        $database = database::getInstance();
        if(! $database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($toCheck->getID());
        $results = $database->getData('shared', 'fileSystemShare', "referenceID={$id} AND referenceType='folder' AND userID={$userIDToCheck}");
        if($results == false) {
            return false;
        }
        if($results == null) {
            return false;
        }
        if(count($results) > 1) {
            return false;
        }
        $shared = (int) $results[0]['shared'];
        if($shared == 0) {
            return false;
        }
        return true;
    }
}