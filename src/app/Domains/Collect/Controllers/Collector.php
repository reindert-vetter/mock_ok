<?php
declare(strict_types=1);

namespace App\Domains\Collect\Controllers;

use App\Domains\Collect\Service\Proxy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @author Reindert Vetter <reindert@myparcel.nl>
 */
class Collector
{
    /**
     * @var Proxy
     */
    private $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function get(Request $request)
    {

//        $response = Cache::get('test_date', function () use ($request) {
            $response = $this->proxy->getReponse($request);
            Cache::put('test_date', $response, 2);
            return $response;
//        });

        return $response;
    }
}