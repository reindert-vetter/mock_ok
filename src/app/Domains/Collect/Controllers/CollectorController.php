<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Helpers\RequestHelper;
use App\Domains\Collect\Providers\RequestProvider;
use App\Domains\Collect\Service\ExampleService;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\Response as ConsumerResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Reindert Vetter
 */
class CollectorController
{
    /**
     * @var ExampleService
     */
    private $exampleService;

    public function __construct(ExampleService $exampleService)
    {
        $this->exampleService = $exampleService;
    }

    /**
     * @param LaravelRequest         $request
     * @param ServerRequestInterface $psrRequest
     * @param RequestProvider        $requestProvider
     * @return Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function handle(
        LaravelRequest $request,
        ServerRequestInterface $psrRequest,
        RequestProvider $requestProvider
    ): Response {
        $request = RequestHelper::normalizeRequest($request);
        $this->logRequest($psrRequest);

        if ($result = $this->exampleService->tryExample($request)) {
            return $result;
        }

        $clientResponse = $requestProvider->handle(
            $request->method(),
            str_replace('http://', 'https://', $request->url()),
            $request->query->all(),
            $request->getContent(),
            $request->headers->all()
        );

        $this->exampleService->saveExample($request, $clientResponse);

        $result = new ConsumerResponse(
            (string) $clientResponse->getBody(),
            $clientResponse->getStatusCode(),
            $clientResponse->getHeaders()
        );

        return $result;
    }

    /**
     * @param ServerRequestInterface $psrRequest
     */
    private function logRequest(ServerRequestInterface $psrRequest): void
    {
        Log::debug("\n" . (new CurlFormatter(120))->format($psrRequest));
    }
}
