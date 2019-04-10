<?php
require_once '../../vendor/autoload.php';
use Google\Cloud\Datastore\DatastoreClient;
use google\appengine\api\app_identity\AppIdentityService;
use GDS\Store;
$datastore = new DatastoreClient(['projectId' => AppIdentityService::getApplicationId()]);

function getKeyData($kind, $keyValue, $propertyID) { #Get a single value, takes the Kind, key/ID value and property name
    global $datastore;
    $key = $datastore->key($kind, $keyValue);
    $task = $datastore->lookup($key);
    return $task[$propertyID];
}

function getKindData($kind, $keyValue) {
    global $datastore;
    $key = $datastore->key($kind, $keyValue);
    $task = $datastore->lookup($key);
    return $task;
}

function getGQLData($kind, $queryString) {
    $data = new GDS\Store($kind);
    $queryData = $data->fetchAll($queryString);
    return $queryData;
}