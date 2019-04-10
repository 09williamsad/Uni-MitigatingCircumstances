<?php
require_once '../../vendor/autoload.php';
use google\appengine\api\users\UserService;
echo '<a class="button" href="'. UserService::createLogoutUrl('/') .'">Log Out</a>';