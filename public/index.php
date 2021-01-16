<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/21/18
 * Time: 8:46 PM
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/docusign/esign-client/autoload.php';
require_once __DIR__ . '/../ds_config.php';

use Example\Services\RouterService;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['app_url'] = $GLOBALS['DS_CONFIG']['app_url'] . '/';

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


$router = new RouterService();

$router->router();
