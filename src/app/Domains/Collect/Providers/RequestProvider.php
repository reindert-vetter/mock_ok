<?php
declare(strict_types=1);

namespace App\Domains\Collect\Providers;


use App\Domains\Collect\Models\PreserveRequest;
use GuzzleHttp\Client;

/**
 * @author Reindert Vetter
 */
class RequestProvider
{
    /**
     * @param PreserveRequest $request
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(PreserveRequest $request): \Psr\Http\Message\ResponseInterface
    {
        $client = new Client(["http_errors" => false]);
        $options = [
            'headers' => $request->headers,
            'query' => $request->query,
            'body' => $request->body,
            'timeout' => 3,
        ];

        $result = $client->request($request->method, $request->uri, $options);

        return $result;
    }
}