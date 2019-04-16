<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Console\Helpers\Json;
use App\Domains\Collect\Helpers\RequestHelper;
use App\Domains\Collect\Helpers\ResponseHelper;
use App\Domains\Collect\Providers\RequestProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response as ConsumerResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Reindert Vetter
 */
class CollectorController
{
    /**
     * @param  Request         $request
     * @param  RequestProvider $requestProvider
     * @return \Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function handle(Request $request, RequestProvider $requestProvider): Response
    {
        $request = RequestHelper::normalizeRequest($request);

        if ($result = $this->tryExample($request)) {
            return $result;
        }

        $clientResponse = $requestProvider->handle(
            $request->method(),
            str_replace('http://', 'https://', $request->url()),
            $request->query->all(),
            $request->getContent(),
            $request->headers->all()
        );

        $this->saveExample($request, $clientResponse);

        $result = new ConsumerResponse(
            (string) $clientResponse->getBody(),
            $clientResponse->getStatusCode(),
            $clientResponse->getHeaders()
        );

        return $result;
    }

    /**
     * @param  \Illuminate\Http\Request            $consumerRequest
     * @param  \Psr\Http\Message\ResponseInterface $clientResponse
     * @throws \Throwable
     */
    private function saveExample(Request $consumerRequest, ResponseInterface $clientResponse): void
    {
        $fileName = Str::slug(
            trim(str_replace(['.', '/', '?', '=', '&', 'https', 'http'], '-', $consumerRequest->fullUrl()), '-')
        );
        $body     = (string) $clientResponse->getBody();
        $langIde  = Json::isJson($body) ? 'JSON' : 'XML';

        $url = $this->getRegexUrl($consumerRequest);

        $with = [
            "method"  => $consumerRequest->getMethod(),
            "url"     => $url,
            "status"  => $clientResponse->getStatusCode(),
            "body"    => $body,
            //            "body"    => Json::prettyPrint($body),
            "headers" => ResponseHelper::normalizeHeaders($clientResponse->getHeaders(), strlen($body)),
        ];

        $exampleInc = "<?php\n\n" . view('body-template')
                ->with($with)
                ->render();
        $exampleInc = str_replace('LANG_IDE', "/** @lang $langIde */", $exampleInc);

        if (file_exists(base_path() . "/examples/$fileName.inc")) {
            throw new \Exception("Can't create example $fileName.inc already exist");
        }

        file_put_contents(base_path() . "/examples/$fileName.inc", $exampleInc);
    }

    /**
     * @param  \Illuminate\Http\Request $consumerRequest
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    private function tryExample(Request $consumerRequest): ?Response
    {
        $examples = $this->getExamples();

        $matchExamples = $examples->filter(
            function ($value) use ($consumerRequest) {
                return call_user_func($value['when'], $consumerRequest);
            }
        );

        if ($matchExamples->isEmpty()) {
            return null;
        }

        if ($matchExamples->count() > 1) {
            $pathExamples = str_replace('/var/www/examples/', '', $matchExamples->pluck('path')->implode(", \n"));
            throw new Exception("Multiple examples have a match: \n" . $pathExamples);
        }

        $example = $matchExamples->first();

        $response = new Response(
            $example['response']['body'],
            $example['response']['status'],
            ResponseHelper::normalizeHeaders($example['response']['headers'], strlen($example['response']['body']))
        );

        return $response->setContent($example['response']['body']);
    }

    /**
     * @param  string $dir
     * @param  array  $results
     * @return Collection
     */
    private function getExamples(string $dir = null, array &$results = []): Collection
    {
        if (null === $dir) {
            $dir = base_path() . '/examples/';
        }

        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if (! is_dir($path)) {
                if (false !== strpos($path, '.inc')) {
                    $example         = require($path);
                    $example['path'] = $path;
                    $results[]       = $example;
                }
            } elseif ($value != "." && $value != "..") {
                $this->getExamples($path, $results);
            }
        }

        return collect($results);
    }

    /**
     * @param  Request $consumerRequest
     * @return string
     */
    private function getRegexUrl(Request $consumerRequest): string
    {
        $url = $consumerRequest->fullUrl();

        $regexUrl = preg_quote(html_entity_decode($url), '#');
        return str_replace(['https\:', 'http\:'], 'https?\:', $regexUrl);
    }
}
