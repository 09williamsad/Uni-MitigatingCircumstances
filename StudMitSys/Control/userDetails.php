<?php
require_once '../../vendor/autoload.php';
require_once 'userFunctions.php';
require_once '../Model/readDatastore.php';

if (empty($_GET['id'])) { #if the user is trying to get to the page without an ID then redirect to homepage
    header("Location: i7648171.appspot.com");
    exit();
} else {
    $id = $_GET['id'];
    if (groupCheck() == 'Tutor' OR $id == getUserEmail()) { #If not a tutor or the profile owner then redirect back to home page.
        $userData = getKindData('Account', $id);
        if ($userData['role'] == 'Tutor') {
            $requestsHeader = ' assigned requests';
            $queryCondition = 'assignedTo = "' . $id . '"';
        } else {
            $requestsHeader = ' requests made';
            $queryCondition = 'email = "' . $id . '"';
        }
        if ($userData['active'] == true) {
            $active = 'Yes';
        } else {
            $active = 'No';
        }
        $userRequests = getGQLData('Request', 'select * from Request where ' . $queryCondition . ' order by timeCreated desc');
        $requestsHeader = count($userRequests) . $requestsHeader;
        $userRequestsRows = '';
        foreach ($userRequests as $data) { #Get data for any follow up comments
            if (!empty($data)) {
                $userRequestsRows .= '<tr><td>' . $data->timeCreated->format("H:i d-m-Y") . '</td>
                                    <td><a href="/requestDetails?id=' . $data->getKeyID() . '">' . $data->title . '</a></td>
                                    <td>' . $data->reason . '</td>
                                    <td>' . $data->status . '</td></tr>';
            }
        }
        require_once 'smartyFunctions.php';
        smartyHeader(); #Setup smarty with header
    } else {
        header("Location: i7648171.appspot.com");
    }
}