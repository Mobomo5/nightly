<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:46 PM
 */
class addStudentBlock implements block {

    private $content;
    private $title;

    public function __construct() {
        if (!permissionEngine::getInstance()->getPermission('userCanAddStudents')->canDo()) {
            return false;
        }
        $this->title = 'Add a student';

        if (empty($_POST['addStudentState'])) {
            $this->stepOne();
            return true;
        }

        switch ($_POST['addStudentState']) {
            case 1:
                $this->stepTwo();
                break;
            case 2:
                $this->stepThree();
                break;
            case 3:
                $this->processCSV();
        }
        return true;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
    }

    public function getType() {
        return get_class($this);
    }

    private function stepOne() {
        $this->content = '<p>Would you like to add a single student or a comma-separated list of students?</p>

                            <form id="singleStudent" method="post"><input type="submit" form="singleStudent" value="Single"><input type="hidden" form="singleStudent" name="howMany" value="single"><input type="hidden"form="singleStudent" name="addStudentState" value="1"></form>
                            <form  id="listStudent" method="post"><input type="submit" form="listStudent" value="List"><input type="hidden" form="listStudent" name="howMany" value="list"><input type="hidden" form="listStudent" name="addStudentState" value="1"></form> ';
    }

    private function stepTwo() {

        if (empty($_POST['howMany'])) {
            $this->stepOne();
            return false;
        }

        switch ($_POST['howMany']) {
            case "single":
                $this->content = $this->getSingleForm();
                break;
            case "list":
                $this->content = $this->getListForm();
                break;
            default:
                $this->stepOne();
                break;

        }
        return true;
    }

    private function stepThree() {
    }

    private function getListForm() {
        $return = '<p>Select a comma-separated list file from your computer to upload to the server.</p>
                    <p>The new account will be the students first initial followed by their last name and the day of the month they were born. Their password will be their student ID number.</p>
                    <form id="listForm" method="post" enctype="multipart/form-data">
                        <input type="file" name="csvFile" form="listForm" accept=".csv" required="yes"><br>
                        <input type="hidden" form="listForm" name="addStudentState" value="3">
                        <input form="listForm" type="submit"><input type="reset" form="listForm">
                        </form>';
        return $return;

    }

    private function getSingleForm() {
        $return = '<p>Enter the student\'s credentials.</p><p>The new account will be the students first initial followed by their last name and the day of the month they were born. Their password will be their student ID number.</p>
                    <form id="singleForm" method="post">
                        <label for="firstName">First Name:</label><input type="text" form="singleForm" name="firstName"><br>
                        <label for="lastName">Last Name:</label><input type="text" name="lastName" form="singleForm"/><br>
                        <label for="email">Email:</label><input type="email" form="singleForm" name="email"/><br>
                        <label for="studentID">Student ID:</label><input type="text" name="studentID" form="singleForm"/>
                        <input type="hidden" name="addStudentState" form="singleForm" value="2">
                        <input type="submit" form="singleForm"></form>';

        return $return;

    }

    //@todo: THere is a problem creating > 300 users at a time. The crypt() function times out.

    /**
     *
     * Accepts csv file in the format "firstName, lastName, Email, StudentID, Birthday"
     * @return bool
     */
    private function processCSV() {
        if ($_FILES["csvFile"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }
        $filename = $_FILES['csvFile']['tmp_name'];
        $ext = strtolower(end(explode('.', $_FILES['csvFile']['name'])));
        if (strcmp($ext, 'csv')) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, "The file must be a .csv file."));
            $this->stepOne();
            return false;
        }

        $db = database::getInstance();
        $results = $db->getData('roleID', 'role', "LOWER(roleName) = 'student'");
        if (!$results) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, 'Ensure the role "Student" has been created.'));
            $this->stepOne();
            return false;
        }

        $roleID = $results[0]['roleID'];
        $count = 0;
        if (($handle = fopen($filename, 'r')) !== false) {

            while (($row = fgetcsv($handle)) !== false) {
                // get credentials from the csv
                $firstName = $db->escapeString($row[0]);
                $lastName = $db->escapeString($row[1]);
                $email = $db->escapeString($row[2]);
                $studentID = $db->escapeString($row[3]);
                $birthday = date('Y-m-d', strtotime($row[4]));
                $day = date('d', strtotime($row[4]));
                $username = strtolower(substr($firstName, 0, 1) . $lastName . $day);

                if (!$this->addUser($roleID, $studentID, $username, $firstName, $lastName, $email, $birthday)) {
                    continue;
                }

                $count++;

            }
            fclose($handle);
        }
        noticeEngine::getInstance()->addNotice(new notice(noticeType::positive, "Created $count Students!"));
        $this->stepOne();
        return true;
    }

    private function addUser($roleID, $studentID, $username, $firstName, $lastName, $email, $birthday) {

        $user = new user('1', $roleID, $studentID, $username, $firstName, $lastName, $email, $birthday);

        // set the password
        if (!userEngine::getInstance()->addUser($user, $studentID)) { //@todo: handle duplicates and errors in insertion.
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, "The student $username couldn't be created for some reason."));
            return false;
        }
        return true;
    }
}