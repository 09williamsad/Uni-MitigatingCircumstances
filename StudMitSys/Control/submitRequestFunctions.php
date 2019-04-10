<?php
require_once '../../vendor/autoload.php';
require_once 'userFunctions.php';

if (isset($_POST["titleText"]) AND groupCheck() == 'Student') {
    if (strlen(trim($_POST["titleText"])) < 1) {
        echo json_encode('Title is empty.');
        exit();
    } elseif (strlen(trim($_POST["descriptionText"])) < 1) {
        echo json_encode('Description is empty.');
        exit();
    } else {
        $goodImage = false;
        if (!empty($_FILES['imageFile']['type'])) {
            $imageFile = $_FILES['imageFile'];
            if (stripos($imageFile['type'], 'png') == false AND stripos($imageFile['type'], 'bmp') == false AND stripos($imageFile['type'], 'jpeg') == false) { #Check if not a valid image
                echo json_encode('Selected file is not an png, bmp or jpeg.');
                exit();
            } else {
                $goodImage = true;
            }
        }
        $studentEmail = getUserEmail();
        require_once '../Model/writeDatastore.php';
        require_once '../Model/readDatastore.php';
        require_once 'trelloFunctions.php';
        $unassignedListID = getListID('Unassigned');
        $trelloCardID = addCard($_POST["titleText"] . ' by ' . $studentEmail, $_POST["descriptionText"], $unassignedListID);
        $requestData = array('email' => $studentEmail, 'status' => 'Open', 'assignedTo' => 'Unassigned', 'timeCreated' => new DateTime(), 'title' => $_POST["titleText"], 'description' => $_POST["descriptionText"], 'reason' => $_POST["reasonSelect"], 'trelloID' => $trelloCardID);
        insertNewEntity('Request', $requestData);
        $requestIDResult = getGQLData('Request', 'select __key__ from Request where email = "'. $studentEmail .'" order by timeCreated desc'); #Get ID of new data by query as there does not seem to be a function for php datastore to get ID of inserted data
        $requestID = $requestIDResult[0]->getKeyID();

        if ($goodImage == true) { #If valid image then update request with image, adding image requires the request ID to be made
            $name = $requestID . ' ' . $imageFile['name'];
            $file_tmp = $imageFile['tmp_name'];
            $location = 'gs://i7648171.appspot.com/images/requestImages/' . $name;
            move_uploaded_file($file_tmp, $location);
            require_once '../Model/cloudstore.php';
            $_url = getFileLink($location);
            updateSingleValue('Request', $requestID, 'imageLink', $_url);
        }

        $tutorList = getGQLData('Account', 'select __key__ from Account where role = "Tutor" AND active = true'); #Get active tutors
        $tutorEmails = '';
        foreach ($tutorList as $tutorData) { #Get emails for active tutors
            $tutorEmails .= $tutorData->getKeyName() .';';
        }
        require_once 'emailFunctions.php';
        require_once 'smartyFunctions.php';
        $emailBody = smartyEmailBody(array('fromAddress' => $studentEmail, 'id' => $requestID), '../templates/emailNewRequest.tpl');
        sendEmail($tutorEmails, 'New Request: '. $_POST["titleText"], $emailBody);
        echo json_encode('requestDetails?id=' . $requestID);
    }
} else {
    echo json_encode('Failed response' );
}