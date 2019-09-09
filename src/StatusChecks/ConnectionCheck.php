<?php

namespace W2w\Lib\Apie\StatusChecks;

use GuzzleHttp\Client;
use Throwable;
use W2w\Lib\Apie\ApiResources\Status;

/**
 * Does a connection check with a Guzzle client.
 */
class ConnectionCheck implements StatusCheckInterface
{
    private $client;

    private $statusName;

    private $statusPath;

    private $parseResponse;

    private $debug;

    public function __construct(Client $client, string $statusName, string $statusPath = 'status', bool $parseResponse = true, bool $debug = false)
    {
        $this->client = $client;
        $this->statusName = $statusName;
        $this->statusPath = $statusPath;
        $this->parseResponse = $parseResponse;
        $this->debug = $debug;
    }

    public function getStatus(): Status
    {
        try {
            $response = $this->client->get($this->statusPath, ['headers' => ['Accept' => 'application/json']]);
            if ($response->getStatusCode() < 300) {
                $body = (string) $response->getBody();

                return new Status($this->statusName, $this->determineStatusResult($body), null, ['statuses' => $this->debug ? json_decode($body, true) : null]);
            }

            return new Status($this->statusName, 'Response error: ' . $response->getBody());
        } catch (Throwable $throwable) {
            return new Status($this->statusName, 'Error connecting: ' . $throwable->getMessage());
        }
    }

    private function determineStatusResult(string $body): string
    {
        if (!$this->parseResponse) {
            return 'OK';
        }
        $statuses = json_decode($body, true);
        if (!is_array($statuses)) {
            return 'Unexpected response';
        }
        foreach ($statuses as $key => $status) {
            if (!isset($status['id'])) {
                return 'Missing id for status #' . $key;
            }
            if (isset($status['no_errors']) && gettype($status['no_errors']) === 'boolean') {
                if ($status['no_errors'] === false) {
                    return 'Status error for check ' . $status['id'];
                }
            }
            if (!isset($status['status'])) {
                return 'Status can not be determined for ' . $status['id'];
            }
            if ($status['status'] !== 'OK') {
                return 'Status error for check ' . $status['id'];
            }
        }

        return 'OK';
    }
}
