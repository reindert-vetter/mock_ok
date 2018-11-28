<?php
declare(strict_types=1);

namespace App\Domains\Collect\Providers;


use App\Domains\Collect\Models\PreserveRequest;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

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
        $client = new Client();
        $options = [
            'headers' => $request->headers,
            'query' => $request->query,
            'timeout' => 3,
        ];

        $result = $client->request($request->method, $request->uri, $options);

        return $result;
    }
}