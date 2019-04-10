<?php
require_once '../../vendor/autoload.php';
require_once '../Model/readDatastore.php';
use google\appengine\api\users\UserService;
$user = UserService::getCurrentUser();

$userGroup = groupCheck();
if ($userGroup !== 'Student' AND $userGroup !== 'Tutor') {
    header("Location: /noAccount");
}

function groupCheck() { #Get group of logged in user
    $userGroup = getKeyData('Account', getUserEmail(), 'role');
    return $userGroup;
}

function getEmailGroup($email) {
    return getKeyData('Account', $email, 'role');
}

function getUserEmail() { #Get user email
    global $user;
    return $user->getEmail();
}

function getUserNickName() { #Get nickname, usually part of email before @ symbol
    global $user;
    return $user->getNickname();
}

function getUserFullName($email) { #Get name from email in Requests kind/table
    if (! empty($email)) {
        $name = getKeyData('Account', $email, 'Name');
    } else {
        $name = '';
    }
    return $name;
}

function getLogoutURL() { #Generate logout URL
    return UserService::createLogoutUrl('/');
}

function requestOwnerOrTutor ($id) {
    if (groupCheck() == 'Tutor' OR getKeyData('Request', $id, 'email') == getUserEmail()) {
        return true;
    } else {
        return false;
    }
}

function requestOwnerOrAssigned ($id) { #Check if logged in user is the creator or assigned to a request
    switch (groupCheck()) {
        case 'Student': $emailOrAssigned = 'email'; break;
        case 'Tutor': $emailOrAssigned = 'assignedTo'; break;
    }
    if (getKeyData('Request', $id, $emailOrAssigned) == getUserEmail()) {return true;}
    else {
        return false;
    }
}