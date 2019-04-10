<?php
require_once '../../vendor/autoload.php';
use Google\Cloud\Datastore\DatastoreClient;
use google\appengine\api\app_identity\AppIdentityService;
use GDS\Store;
$datastore = new DatastoreClient(['projectId' => AppIdentityService::getApplicationId()]);

function insertChildEntity ($kind, $parentKind, $parentKey, $data) { #Adds new child entity, takes data array
    global $datastore;
    $key = $datastore->key($kind);
    $key->ancestor($parentKind, $parentKey);
    $entity = $datastore->entity($key, $data);
    $datastore->insert($entity);
}

function insertNewEntity ($kind, $data) { #Adds new entity
    global $datastore;
    $key = $datastore->key($kind);
    $entity = $datastore->entity($key, $data);
    $datastore->insert($entity);
}

function insertNewDefinedKeyEntity ($kind, $data, $keyValue) { #Adds new entity with a custom key, not a random integer.
    global $datastore;
    $key = $datastore->key($kind, $keyValue);
    $entity = $datastore->entity($key, $data);
    $datastore->insert($entity);
}

function updateSingleValue($kind, $keyValue, $property, $newValue) {#Update existing entity property or add new property to existing entity
    global $datastore;
    $transaction = $datastore->transaction();
    $key = $datastore->key($kind, $keyValue);
    $task = $transaction->lookup($key);
    $task[$property] = $newValue;
    $transaction->update($task);
    $transaction->commit();
}

function upsertMultipleValues($kind, $keyValue, $dataArray) {#Update existing entity with multiple properties/values, will create new properties if does not exist
    global $datastore;
    $transaction = $datastore->transaction();
    $key = $datastore->key($kind, $keyValue);
    $task = $transaction->lookup($key);
    foreach ($dataArray as $key => $data) {
        $task[$key] = $data;
    }
    $transaction->upsert($task);
    $transaction->commit();
}

function updateExistingValues($kind, $keyValue, $newData) {#Update existing property values of an entity.
    global $datastore;
    $key = $datastore->key($kind, $keyValue);
    $entity = $datastore->entity($key, $newData);
    $datastore->upsert($entity);
}