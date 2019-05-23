

use Illuminate\Http\Request;

return [

    'when' => function (Request $request): bool {
        return
            $request->isMethod('{{ $method }}') &&
            preg_match('#^{!! $url !!}$#', $request->fullUrl())@if($method !== 'GET') &&
            preg_match('#^{!! $requestBody !!}$#', (string) $request->getContent())@endif;
    },

    'response' => [
        'status'  => {{ $status }},
        'headers' => [
@foreach ($headers as $key => $header)
            '{{ $key }}' => '{{ $header }}',
@endforeach
        ],
        'body'    =>  LANG_IDE'{!! $responseBody !!}',
    ],

];
