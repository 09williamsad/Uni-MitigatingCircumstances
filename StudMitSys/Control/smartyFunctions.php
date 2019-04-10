<?php
require_once '../../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;
$storage = new StorageClient(); //Storage client for smarty to store cache data in
$storage->registerStreamWrapper();
$smarty = new Smarty();
$smarty->compile_dir= "gs://i7648171.appspot.com/smartybucket/compile";
$smarty->cache_dir="gs://i7648171.appspot.com/smartybucket/cache";
$smarty->addPluginsDir('../plugins');
$smarty->setTemplateDir('../templates');
require_once '../Control/userFunctions.php';

function smartyHeader() { //Smarty establish with header
    global $smarty;
    $smarty->display('header.tpl');
}

function smartyEmailBody($variables, $emailTemplate) { //Smarty establish with header
    global $smarty;
    foreach ($variables as $key => $data) {
        $smarty->assign($key, $data);
    }
    return $smarty->fetch($emailTemplate);
}