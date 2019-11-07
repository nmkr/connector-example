<?php
/**
 * @copyright 2010-2015 JTL-Software GmbH
 * @package Jtl\Connector\Example
 */
require_once dirname(__DIR__). "/bootstrap.php";

use Jtl\Connector\Core\Application\Application;
use Jtl\Connector\Example\Authentication\TokenValidator;
use Jtl\Connector\Example\Connector;
use Jtl\Connector\Example\Mapper\PrimaryKeyMapper;

$application = null;

try {
    $logDir = CONNECTOR_DIR . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        mkdir($logDir);
        chmod($logDir, 0777);
    }

    $username = 'root';
    $password = 'jtlgmbh';
    $dbName = 'example';

    $pdo = new \PDO(sprintf('mysql:host=localhost;dbname=%s', $dbName), $username, $password);

    // Connector instance
    $connector = new Connector(new PrimaryKeyMapper($pdo), new TokenValidator());
    $application = new Application($connector);
    $application->run();
} catch (\Exception $e) {
    if (is_object($application)) {
        $handler = $application->getErrorHandler()->getExceptionHandler();
        $handler($e);
    }
}
