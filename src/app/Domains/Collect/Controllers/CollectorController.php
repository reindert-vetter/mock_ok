<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Helpers\RequestHelper;
use App\Domains\Collect\Providers\RequestProvider;
use App\Domains\Collect\Service\MockService;
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
     * @var MockService
     */
    private $mockService;

    public function __construct(MockService $mockService)
    {
        $this->mockService = $mockService;
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

        if ($result = $this->mockService->tryMock($request)) {
            return $result;
        }

        $clientResponse = $requestProvider->handle(
            $request->method(),
            str_replace('http://', 'https://', $request->url()),
            $request->query->all(),
            $request->getContent(),
            $request->headers->all()
        );

        $this->mockService->saveMock($request, $clientResponse);

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
