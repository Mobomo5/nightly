<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:36 PM
 */
class addClassBlock implements block {

    private $content;
    private $title;

    private $className;
    private $classNumber;
    private $classSubject;

    public function __construct() {
        $perm = permissionEngine::getInstance()->getPermission('userCanAddClasses');
        if (!$perm->canDo()) {
            return false;
        }
        $this->title = 'Add a class';
        $this->content = 'This is where you add a class.';

        if (empty($_POST['addClassState'])) {
            $this->stepOne();
            return;
        }

        switch ($_POST['addClassState']) {
            case 1:
                $this->stepTwo();
                break;
            case 2:
                $this->stepThree();
                break;
            default:
                $this->stepOne();
                break;
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
        $this->content = "
        <form method='post' id='addClass'>
            <label>Class Name:</label><input type='text' form='addClass' required='yes' name='className'/><br>
            <input form='addClass' type='hidden' name='addClassState' value='1'>
            <label>Class Number:</label><input type='text' form='addClass' name='classNumber' required='yes'><br>
            <label>Class Subject:</label><input type='text' form='addClass' name='classSubject'><br>
            <input type='submit' form='addClass'><input type='reset' form='addClass'></form>";
    }

    private function stepTwo() {
        // get the details for the class
        // choose department from list or add new to list.

        // are we adding to the list of departments?
        if (!empty($_POST['departmentToAdd'])) {
            $this->addDepartment($_POST['departmentToAdd']);
        }

        $departments = $this->getDepartmentsOptionHTML();
        $this->content = '<p>Choose a department for ' . $this->className . ' from the list:</p>
                            <form id="addClass" method="post">
                            <label>Department:</label>' . $departments . '
                            <input type="submit" form="addClass"><input type="reset" form="addClass">
                            <input type="hidden" form="addClass" name="className" value="' . $_POST['className'] . '">
                            <input type="hidden" form="addClass" name="classSubject" value="' . $_POST['classSubject'] . '">
                            <input type="hidden" form="addClass" name="classNumber" value="' . $_POST['classNumber'] . '">
                            <input type="hidden" name="addClassState" value="2" form="addClass">
                            </form>
                            <p>Or add a new department:</p>
                            <form id="addDepartment" method="post">
                                <input type="text" form="addDepartment" required="yes">
                                <input type="submit" form="addDepartment">
                                <input type="reset" form="addDepartment">
                                <input type="hidden" form="addDepartment" name="addClassState" value="1">
                                <input type="hidden" form="addDepartment" name="className" value="' . $_POST['className'] . '">
                                <input type="hidden" form="addDepartment" name="classSubject" value="' . $_POST['classSubject'] . '">
                                <input type="hidden" form="addDepartment" name="classNumber" value="' . $_POST['classNumber'] . '">
                            </form> ';

    }

    private function stepThree() {
        // add the class to the database and display the first step again

        if (!$this->addToDB()) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, "I couldn't create that class for some reason."));

        } else {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::positive, "Class $this->className has been created successfully."));
        }

        $this->stepOne();
    }

    private function getDepartmentsOptionHTML() {

        $array = array("1" => "Math", "2" => "Science"); //@todo: function to get all departments from db

        $return = '<select name="departmentToAdd" form="addClass">';
        foreach ($array as $id => $name) {
            $return .= '<option value="' . $id . '">' . $name . '</option>';
        }

        $return .= '</select>';

        return $return;
    }

    private function addDepartment($departmentToAdd) {
        //@todo: add departments to the database

    }

    private function addToDB() {
        //@todo: figure out how to make this all work with nodeEngine.
        return true;
    }
}