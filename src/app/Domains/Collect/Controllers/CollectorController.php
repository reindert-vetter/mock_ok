<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Helpers\RequestHelper;
use App\Domains\Collect\Models\PreserveRequest;
use App\Domains\Collect\Models\PreserveResponse;
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
        $request = PreserveRequest::where([
            'method' => $consumerRequest->method(),
            'uri'    => RequestHelper::removeTwinsHost($consumerRequest->getUri()),
        ])->first();
//        'headers->accept' => $consumerRequest->header('accept', ''),

        if (null !== $request) {
            return new ConsumerResponse(
                $request->preserveResponse->body,
                $request->preserveResponse->status,
                $request->preserveResponse->headers
            );
        }

        $request = new PreserveRequest([
            'method'  => $consumerRequest->method(),
            'uri'     => $consumerRequest->getUri(),
            'query'   => $consumerRequest->query->all(),
            'body'    => $consumerRequest->getContent(),
            'headers' => $consumerRequest->headers->all(),
        ]);

        $clientResponse = $requestProvider->handle($request);

        $reserveResponse = new PreserveResponse([
            'body'    => (string)$clientResponse->getBody()->getContents(),
            'status'  => $clientResponse->getStatusCode(),
            'headers' => $clientResponse->getHeaders(),
        ]);
        $reserveResponse->save();
        $request->preserveResponse()->associate($reserveResponse)->save();

        return new ConsumerResponse(
            $reserveResponse->body,
            $reserveResponse->status,
            $reserveResponse->headers
        );
    }
}