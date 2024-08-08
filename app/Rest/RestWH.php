<?php

namespace App\Rest;

use GuzzleHttp\Client;

class RestWH
{
    private int $total;

    private Client $client;

    private array $error = [];

    private array $info;

    public function __construct(int $timeOut = 20)
    {
        $this->client = new Client([
            'base_uri' => require '/home/worker/x_config/wh.php',
            'timeout' => $timeOut,
            'http_errors' => false,
            'verify' => false
        ]);
    }

    public function error(): array
    {
        return $this->error;
    }

    public function getBig(string $method, array $params): array
    {
        $this->info = ['method' => $method, 'params' => $params];

        $start = 0;
        $res = [];

        while (true) {
            $params['start'] = $start;
            $start += 50;
            $result = $this->call($method, $params);
            $res = array_merge($res, $result ?? []);
            if ($start >= $this->total || $this->error)
                break;
        }

        return $res;
    }

    public function call(string $method, array $params): mixed
    {
        $this->info ??= ['method' => $method, 'params' => $params];
        $this->error = [];
        $response = $this->client->post($method, ['query' => $params]);
        $result = json_decode($response->getBody(), true);
        $this->total ??= $result['total'] ?? 0;

        if ($response->getStatusCode() != 200) {
            throw new \Exception("rest24 error: " . print_r($result, 1));
        }
        return $result['result'] ?? [];
    }
}