<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Models\PreserveRequest;
use App\Domains\Collect\Models\PreserveResult;
use App\Domains\Collect\Providers\RequestProvider;
use Illuminate\Http\Request as ConsumerRequest;
use Illuminate\Http\Response as ConsumerResponse;

/**
 * @author Reindert Vetter
 */
class CollectorController
{
    /**
     * @param ConsumerRequest $consumerRequest
     * @param RequestProvider $requestProvider
     * @return ConsumerResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(ConsumerRequest $consumerRequest, RequestProvider $requestProvider)
    {
        PreserveRequest::all();
        $request = new PreserveRequest([
            'method'  => $consumerRequest->method(),
            'uri'     => $consumerRequest->getUri(),
            'query'   => $consumerRequest->query->all(),
            'body'    => $consumerRequest->getContent(),
            'headers' => $consumerRequest->headers->all(),
        ]);
        $clientResponse = $requestProvider->handle($request);

        $response = new PreserveResult([
            'body'    => (string) $clientResponse->getBody(),
            'status'  => $clientResponse->getStatusCode(),
            'headers' => $clientResponse->getHeaders(),
            'request' => $request,
        ]);

        $response->save();

        return new ConsumerResponse(
            $response->body,
            $response->status,
            $response->headers
        );
    }
}