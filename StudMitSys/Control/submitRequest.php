<?php
require_once '../../vendor/autoload.php';
require_once 'userFunctions.php';
if (groupcheck() !== 'Student') {
    header("Location: i7648171.appspot.com");
} else {
    require_once 'smartyFunctions.php';
    smartyHeader(); #smarty setup and header
}