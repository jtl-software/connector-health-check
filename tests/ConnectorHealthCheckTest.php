<?php

namespace Jtl\Connector\HealthCheck\Test;

use Jtl\Connector\Client\ConnectorClient;
use Jtl\Connector\Client\ResponseException;
use Jtl\Connector\HealthCheck\ConnectorHealthCheck;
use Jtl\HealthCheck\ResultDetail;
use PHPUnit\Framework\TestCase;

class ConnectorHealthCheckTest extends TestCase
{
    public function testPassesWhenAuthenticationSucceeds(): void
    {
        $client = $this->createMock(ConnectorClient::class);
        $client->expects($this->once())->method('authenticate');

        $result = (new ConnectorHealthCheck('production', 'token', 'https://connector.test', $client))->check();

        $this->assertTrue($result->hasPassed());
        $this->assertFalse($result->hasMessages());
    }

    /**
     * CO-3659: the result of the last sync must not affect the uptime check.
     */
    public function testDoesNotCheckFinish(): void
    {
        $client = $this->createMock(ConnectorClient::class);
        $client->expects($this->never())->method('finish');

        $result = (new ConnectorHealthCheck('production', 'token', 'https://connector.test', $client))->check();

        $this->assertTrue($result->hasPassed());
        $this->assertSame(['env', 'auth'], array_map(function (ResultDetail $detail) {
            return $detail->getSubject();
        }, $result->getDetails()));
    }

    public function testFailsWhenAuthenticationFails(): void
    {
        $client = $this->createMock(ConnectorClient::class);
        $client->method('authenticate')->willThrowException(new ResponseException('Could not authenticate'));

        $result = (new ConnectorHealthCheck('production', 'token', 'https://connector.test', $client))->check();

        $this->assertFalse($result->hasPassed());
        $this->assertSame([
            'passed'   => false,
            'details'  => ['env' => 'production', 'auth' => false],
            'messages' => [['message' => 'Could not authenticate', 'status' => 'error', 'subject' => 'auth']],
        ], $result->toArray());
    }
}
