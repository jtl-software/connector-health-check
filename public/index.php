<?php

use Jtl\Connector\HealthCheck\ConnectorHealthCheck;
use Noodlehaus\Config;

$rootDir = dirname(__DIR__);

require_once sprintf('%s/vendor/autoload.php', $rootDir);

$configFile = sprintf('%s/config/config.json', $rootDir);

if(!is_file($configFile)) {
    throw new \Exception('Config file missing');
}

$config = new Config($configFile);

$environment = $config->get('connector.environment');
$token = $config->get('connector.token');
$url = $config->get('connector.url');

if(in_array(null, [$environment, $token, $url], true)) {
    throw new \Exception('environment, token or url in config missing');
}

(new ConnectorHealthCheck($environment, $token, $url))->checkAndSendResult();
