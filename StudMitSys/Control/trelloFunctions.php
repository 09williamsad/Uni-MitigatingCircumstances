<?php
require_once '../../vendor/autoload.php';
$client = new Stevenmaguire\Services\Trello\Client(array(
    'callbackUrl' => 'http://your.domain/oauth-callback-url',
    'domain' => 'https://trello.com',
    'expiration' => '3days',
    'key' => '23eac14294dcaaa3ccd3401483ff7851',
    'name' => 'Test name 1',
    'scope' => 'read,write',
    'secret' => '7d55911779cddbad6f2383212b6e2ed919b2085d2e94551afa5a07cf84afe4c5',
    'token'  => 'e81b7f7fdebeb77583f1284436b4fd006ff4b3e4903f31826794da197c15e40c',
    'version' => '1',
    'proxy' => 'tcp://localhost:8125',
));

function addCard($title, $description, $typeID) {
    global $client;
    $attributes = array('name' => $title, 'desc' => $description, 'pos' => "bottom", 'idList' => $typeID);
    return json_decode(json_encode($client->addCard($attributes)), true)["id"];
}

function addCardComment ($cardID, $commentText) {
    global $client;
    return json_decode(json_encode($client->addCardActionComment($cardID, array('text' => $commentText))), true)["id"];
}

function editCardListID ($cardID, $listID) {
    global $client;
    $client->updateCardIdList($cardID, array('value' => $listID));
}

function closeCard ($cardID) {
    global $client;
    $client->updateCardIdList($cardID, array('value' => '5c1a3e11ff468b7b935106fa'));
}

function getListID($requestStatus) { #Get the list for a given list name / status type
    global $client;
    $boardsArray = json_decode(json_encode($client->getCurrentUserBoards()), true); //Get the board and convert to an array from stdClass
    $boardID = strval($boardsArray[0]["id"]);
    $listsArray = json_decode(json_encode($client->getBoardLists($boardID)), true);
    foreach($listsArray as $data) {
        if ($data["name"] == $requestStatus) {
            return $data["id"];
        }
    }
}