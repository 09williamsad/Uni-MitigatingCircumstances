<?php
require_once '../../vendor/autoload.php';
require_once 'userFunctions.php';
require_once 'smartyFunctions.php';
require_once 'emailFunctions.php';

if (isset($_POST["ID"])) {
    $id = $_POST["ID"];
    if (isset($_POST["comment"])) { #New follow up comment
        if (requestOwnerOrTutor($id) == True) {
            if (strlen(trim($_POST["comment"])) < 1) {
                echo json_encode('Response box is empty.');
                exit();
            } else {
                $userEmail = getUserEmail();
                require_once 'trelloFunctions.php';
                $trelloCardID = getKeyData('Request', $id, 'trelloID');
                $followupTrelloID = addCardComment($trelloCardID, $userEmail . ': ' . $_POST["comment"]);
                if (!empty($_FILES['imageFile']['type'])) {
                    $imageFile = $_FILES['imageFile'];
                    if (stripos($imageFile['type'], 'png') == false AND stripos($imageFile['type'], 'bmp') == false AND stripos($imageFile['type'], 'jpeg') == false) { #Check if not a valid image
                        echo json_encode('Selected file is not an png, bmp or jpeg.');
                        exit();
                    } else {
                        $name = $id . ' ' . $imageFile['name'];
                        $file_tmp = $imageFile['tmp_name'];
                        $location = 'gs://i7648171.appspot.com/images/responseImages/' . $name;
                        move_uploaded_file($file_tmp, $location);

                        require_once '../Model/cloudstore.php';
                        $_url = getFileLink($location);
                        $followupData = array('email' => $userEmail, 'followupText' => $_POST["comment"], 'timeCreated' => new DateTime(), 'imageLink' => $_url, 'followupTrelloID' => $followupTrelloID);
                    }
                } else {
                    $followupData = array('email' => $userEmail, 'followupText' => $_POST["comment"], 'timeCreated' => new DateTime(), 'followupTrelloID' => $followupTrelloID);
                }
                if (groupCheck() == 'Student') { #If student makes the followup then email tutor, if tutor then email student
                    $recipientEmail = getKeyData('Request', $id, 'assignedTo');
                    $madeBy = 'student';
                } else {
                    $recipientEmail = getKeyData('Request', $id, 'email');
                    $madeBy = 'tutor';
                }
                $emailData = array('recipient' => $recipientEmail, 'madeBy' => $madeBy, 'followupText' => $_POST["comment"], 'id' => $id);
                $emailBody = smartyEmailBody($emailData, '../templates/emailRequestFollowup.tpl'); #Pass variables to smarty template and get email body response.
                sendEmail($recipientEmail, 'New followup comment on request ' . $id, $emailBody);
                require_once '../Model/writeDatastore.php';
                insertChildEntity('Followup', 'Request', $id, $followupData);
                echo json_encode('submitted');
                exit();
            }
        } else {
            echo json_encode('Not a tutor or the request owner');
        }

    } elseif (isset($_POST["tutorSelect"])) { #Change tutor assigned to request
        $newTutorEmail = $_POST["tutorSelect"];
        if (($newTutorEmail == 'Unassigned' OR getEmailGroup($newTutorEmail) == 'Tutor') AND groupCheck() == 'Tutor') {
            $oldTutorEmail = getKeyData('Request', $id, 'assignedTo');
            $emailData = array('oldTutorEmail' => $oldTutorEmail, 'newTutorEmail' => $newTutorEmail, 'id' => $id);
            $emailBody = smartyEmailBody($emailData, '../templates/emailTutorChange.tpl'); #Pass variables to smarty template and get email body response.
            require_once 'trelloFunctions.php';
            if ($newTutorEmail == 'Unassigned') {
                $listID = getListID('Unassigned');
                $recipients = $oldTutorEmail;
            } else {
                $listID = getListID('Assigned');
                $recipients = $newTutorEmail . '; ' . $oldTutorEmail;
            }
            sendEmail($recipients, 'Assigned tutor change for request ' . $id, $emailBody);
            $trelloCardID = getKeyData('Request', $id, 'trelloID');
            editCardListID($trelloCardID, $listID);
            require_once '../Model/writeDatastore.php';
            updateSingleValue('Request', $id, 'assignedTo', $newTutorEmail);
            echo json_encode('submitted');
        } else {
            echo json_encode('Invalid tutor email or not logged in as a tutor');
        }

    } elseif (isset($_POST["closeText"])) { #Close request
        if (requestOwnerOrAssigned($id) == true) {
            $closeText = $_POST["closeText"];
            $group = groupCheck();
            if ($group == 'Student') { #If student is closing the request then they cannot choose the reason
                $closeSelect = 'Closed by student';
            } else {
                $closeSelect = $_POST["closeSelect"];
            }
            if (strlen(trim($closeSelect)) < 1 OR strlen(trim($closeText)) < 1) { #If reason text box is empty
                echo json_encode('Close text box or reason drop down is empty.');
                exit();
            } else {
                if ($group == 'Student') { #If student closes the request then email tutor, if tutor then email student
                    $recipientEmail = getKeyData('Request', $id, 'assignedTo');
                    $closedBy = 'student';
                } else {
                    $recipientEmail = getKeyData('Request', $id, 'email');
                    $closedBy = 'tutor';
                }
                $emailData = array('recipient' => $recipientEmail, 'closedBy' => $closedBy, 'id' => $id, 'status' => $closeSelect, 'closeReason' => $closeText);
                $emailBody = smartyEmailBody($emailData, '../templates/emailRequestClose.tpl'); #Pass variables to smarty template and get email body response.
                require_once 'trelloFunctions.php';
                $trelloCardID = getKeyData('Request', $id, 'trelloID');
                closeCard($trelloCardID);
                sendEmail($recipientEmail, 'Request '. $id .' has been closed', $emailBody);
                require_once '../Model/writeDatastore.php';
                $closeData = array('status' => $closeSelect, 'closeReason' => $closeText, 'timeClosed' => new DateTime());
                upsertMultipleValues('Request', $id, $closeData);
                echo json_encode('submitted');
            }
        } else {
            echo json_encode('Not the owner or assigned tutor.');
        }
    } else {
        echo json_encode('Failed response' );
        exit();
    }
} else {
    echo json_encode('No ID');
}