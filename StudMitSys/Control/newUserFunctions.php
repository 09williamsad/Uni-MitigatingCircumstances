<?php
require_once '../../vendor/autoload.php';
require_once 'userFunctions.php';

if (isset($_POST["userEmail"]) AND groupCheck() == 'Tutor') {
    if (strlen(trim($_POST["userEmail"])) < 1) {
        echo json_encode('Email field is empty.');
        exit();
    } elseif (strlen(trim($_POST["userName"])) < 1) {
        echo json_encode('Name field is empty.');
        exit();
    } elseif (strlen(trim($_POST["userRoleSelect"])) < 1) {
        echo json_encode('Role field is empty.');
        exit();
    } else {
        $newUserEmail = $_POST["userEmail"];
        require_once '../Model/writeDatastore.php';
        $requestData = array('Name' => $_POST["userName"], 'role' => $_POST["userRoleSelect"], 'active' => true, 'timeCreated' => new DateTime());
        insertNewDefinedKeyEntity('Account', $requestData, $newUserEmail);
        #Email the user that their account has been setup
        require_once 'emailFunctions.php';
        require_once 'smartyFunctions.php';
        $emailBody = smartyEmailBody(array('newUserEmail' => $newUserEmail, 'newUserName' => $_POST["userName"], 'newUserType' => $_POST["userRoleSelect"]), '../templates/emailNewUser.tpl');
        sendEmail($newUserEmail, 'New Request: '. $_POST["titleText"], $emailBody);
        echo json_encode('id=' . $newUserEmail);
    }
} else {
    echo json_encode('Failed response' );
}