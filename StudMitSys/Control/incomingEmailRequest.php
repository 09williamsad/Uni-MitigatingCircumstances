<?php
require_once '../../vendor/autoload.php';
//Handles emails to newRequest@i7648171.appspotmail.com
$rawEmail = file_get_contents('php://input'); //Get received email
preg_match('~mailfrom=(.*?);~', $rawEmail, $output); //Get from address, is between mailfrom= and ;, could not find a simple way to do it as app engine uses mime data for emails but puts email nickname in the From area.
$fromAddress = $output[1]; //Assign from address

require_once 'emailFunctions.php';
require_once 'smartyFunctions.php';
require_once 'userFunctions.php';
$emailParser = new PlancakeEmailParser($rawEmail); //Parse email to be more usable, does not work for from address as gets nickname
$requestTitle = $emailParser->getSubject();

if (getEmailGroup($fromAddress) !== 'Student') { //Only students can make requests
    $emailBody = smartyEmailBody(array('fromAddress' => $fromAddress, 'requestTitle' => $requestTitle), '../templates/emailNotAStudent.tpl'); #Pass variables to smarty template and get email body response.
} else {
    $requestText = substr($emailParser->getPlainBody(), 0, strpos($emailParser->getPlainBody(), "--"));
    if (strlen(trim($requestText)) < 1 OR strlen(trim($requestTitle)) < 1) {
        $emailBody = smartyEmailBody(NULL, '../templates/emailNoTextOrTitle.tpl'); #get email body response.
    } else {
        $requestData = array('email' => $fromAddress, 'status' => 'Open', 'assignedTo' => 'Unassigned', 'timeCreated' => new DateTime(), 'title' => $requestTitle, 'description' => $requestText, 'reason' => 'None given: Made by email'); //assemble new request details.
        require_once '../Model/writeDatastore.php';
        insertNewEntity('Request', $requestData);
        require_once '../Model/readDatastore.php';
        $requestIDResult = getGQLData('Request', 'select __key__ from Request where email = "'. $fromAddress .'" order by timeCreated desc'); #Get ID of new data by query as no function for getting ID of after insert
        $requestID = $requestIDResult[0]->getKeyID();
        $emailBody = smartyEmailBody(array('id' => $requestID, 'requestTitle' => $requestTitle), '../templates/emailRequestSubmitted.tpl');
    }
}
sendEmail($fromAddress, $requestTitle, $emailBody);