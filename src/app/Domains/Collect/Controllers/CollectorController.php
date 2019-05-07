<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Helpers\RequestHelper;
use App\Domains\Collect\Providers\RequestProvider;
use App\Domains\Collect\Service\ExampleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as ConsumerResponse;
use Illuminate\Http\Response;

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
     * @param Request         $request
     * @param RequestProvider $requestProvider
     * @return \Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function handle(Request $request, RequestProvider $requestProvider): Response
    {
        $request = RequestHelper::normalizeRequest($request);

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
}
