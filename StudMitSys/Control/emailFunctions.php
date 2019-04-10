<?php
require_once '../../vendor/autoload.php';
use google\appengine\api\mail\Message;

function sendEmail($recipient, $subject, $emailBody) {
    try {
        $message = new Message();
        $message->setSender('NoReply@i7648171.appspotmail.com');
        $message->addTo($recipient);
        $message->setSubject($subject);
        $message->setHtmlBody($emailBody);
        $message->send();
    } catch (InvalidArgumentException $e) {
        return 'There was an error ' . $e;
    }
}