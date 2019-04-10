<?php
require_once '../../vendor/autoload.php';
require_once '../Model/readDatastore.php';
require_once 'userFunctions.php';

if (! empty($_GET['status'])) {
    $status = $_GET['status'];
    if ($_GET['status'] == '') {
        $status = 'Open';
    }
} else {
    $status = 'Open'; #if status not specified then assume open
}

$requestQuery = 'select * from Request where status = "' . $status . '"';
if (groupCheck() == 'Tutor') {
    if (! empty($_GET['selectedTutor'])) {
        $tutorEmail = $_GET['selectedTutor'];
        if (strstr ($tutorEmail, '@') == false) {
            $tutorEmail = 'Unassigned';
        }
        $requestQuery .= ' and assignedTo = "'. $tutorEmail .'"  order by timeCreated desc';
    } else {
        $requestQuery .= ' order by timeCreated desc';
    }
    #Build the tutor select drop down
    $tutorEmailElement = '<p>Tutor</p><select id="selectedTutor"><option value="Unassigned">Unassigned</option>';
    $tutorList = getGQLData('Account', 'select __key__, Name from Account where role = "Tutor" AND active = true order by Name asc'); #Get list of active tutors
    foreach ($tutorList as $tutorData) { #Option for each active tutor
        $tutorEmailElement .= '<option value="'. $tutorData->getKeyName() .'">'. $tutorData->Name .'</option>';
    }
    $tutorEmailElement .= '</select>';
} else {
    $requestQuery .= ' and email = "' . $user->getEmail() . '" order by timeCreated desc'; #student can only see their own requests
    $tutorEmailElement = '';
}
$requestsRows = '';
foreach(getGQLData('Requests', $requestQuery) as $data) { #table of results for requests query
    $requestsRows .= '<div class="grid-item"><a href="/requestDetails?id='. $data->getKeyID() .'">'. $data->title .'</a></div>
    <div class="grid-item"><a href="/userDetails?id='. $data->email .'">'. getUserFullName($data->email) .'</a></div>
    <div class="grid-item">'. $data->reason .'</div>
    <div class="grid-item">'. $data->timeCreated->format("H:i d-m-Y") .'</div>
    <div class="grid-item">'. $data->status .'</div>
    <div class="grid-item"><a href="/userDetails?id='. $data->assignedTo .'">'. getUserFullName($data->assignedTo) .'</a></div>';
}

#Build header
require_once 'smartyFunctions.php';
smartyHeader(); #smarty setup and header