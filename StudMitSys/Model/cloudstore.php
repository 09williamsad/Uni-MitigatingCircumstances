<?php
require_once '../../vendor/autoload.php';
use google\appengine\api\cloud_storage\CloudStorageTools;

function writeFile($bucketFolder, $type, $file) {
    $options = ['gs' => ['Content-Type' => $type]];
    $context = stream_context_create($options);
    file_put_contents($bucketFolder, $file, 0, $context);
}

function getFileLink ($location) {
    return CloudStorageTools::getImageServingUrl($location, ['secure_url' => true]);
}