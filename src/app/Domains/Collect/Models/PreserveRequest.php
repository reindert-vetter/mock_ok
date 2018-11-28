<?php
declare(strict_types=1);


namespace App\Domains\Collect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * App\Domains\Collect\Models\PreserveRequest
 *
 * @property string                          $method
 * @property string                          $uri
 * @property array                           $query
 * @property string                          $body
 * @property array                           $headers
 * @property int                             $id
 * @property int|null                        $preserve_response_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domains\Collect\Models\PreserveResponse|null $preserveResponse
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest wherePreserveResponseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereQuery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domains\Collect\Models\PreserveRequest whereUri($value)
 * @mixin \Eloquent
 */
class PreserveRequest extends Model
{
    protected $fillable = [
        'method',
        'uri',
        'query',
        'body',
        'headers',
        'hash',
    ];

    protected $casts = [
        'headers' => 'array',
        'query'   => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        if (! empty($attributes)) {
            $this->headers = $this->normalizeHeaders(collect($attributes['headers']));
            $this->uri     = PreserveRequest::removeTwinsHost($attributes['uri']);
            unset($attributes['headers'], $attributes['uri']);
        }
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function preserveResponse(): BelongsTo
    {
        return $this->belongsTo(PreserveResponse::class);
    }

    /**
     * @param $subject
     * @return string
     */
    public static function removeTwinsHost($subject): string
    {
        $subject = str_replace_first('.localhost/dev', '', $subject);
        return str_replace_first('.localhost', '', $subject);
    }

    /**
     * @param Collection $headers
     * @return array
     */
    private function normalizeHeaders(Collection $headers): array
    {
        // Normalize multidimensional array
        $headers->transform(function ($item) {
            return $item[0];
        });

        if ("" === $headers['content-length']) {
            unset($headers['content-length']);
        }

        // Remove twins in host header
        $headers->put('host', PreserveRequest::removeTwinsHost($headers->get('host')));

        return $headers->toArray();
    }
}