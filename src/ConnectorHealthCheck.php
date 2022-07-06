<?php


namespace Jtl\Connector\HealthCheck;

use Jtl\Connector\Client\ConnectorClient;
use Jtl\HealthCheck\AbstractHealthCheck;
use Jtl\HealthCheck\Result;
use Jtl\HealthCheck\ResultDetail;
use Jtl\HealthCheck\ResultMessage;

class ConnectorHealthCheck extends AbstractHealthCheck
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var string
     */
    protected $connectorToken;

    /**
     * @var string
     */
    protected $connectorUrl;

    /**
     * ApplicationHealthCheck constructor.
     * @param string $environment
     * @param string $connectorToken
     * @param string $connectorUrl
     */
    public function __construct(string $environment, string $connectorToken, string $connectorUrl)
    {
        $this->environment = $environment;
        $this->connectorToken = $connectorToken;
        $this->connectorUrl = $connectorUrl;
        parent::__construct();
    }

    /**
     * @return Result
     * @throws \Exception
     */
    public function check(): Result
    {
        $client = new ConnectorClient($this->connectorToken, $this->connectorUrl);

        $details = [
            'env' => new ResultDetail('env', $this->environment),
            'auth' => new ResultDetail('auth', true),
            'finish' => new ResultDetail('finish', true)
        ];

        $messages = [];

        $passed = true;
        try {
            $client->authenticate();
        } catch (\Throwable $ex) {
            $passed = false;
            $details['auth']->setValue(false);
            $messages[] = new ResultMessage('error', 'auth', $ex->getMessage());
        }

        try {
            $client->finish();
        } catch (\Throwable $ex) {
            $passed = false;
            $details['finish']->setValue(false);
            $messages[] = new ResultMessage('error', 'finish', $ex->getMessage());
        }

        return new Result($passed, array_values($details), $messages);
    }
}
